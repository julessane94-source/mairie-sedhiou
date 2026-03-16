<?php
require_once '../config.php';

require_once '../security/logger.php';
$_logger = new Logger($pdo);
if (!isLoggedIn() || !hasRole('agent')) {
    redirect('../login.php');
}

// Générer CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$agentId    = (int) $_SESSION['user_id'];
$id_demande = (int) ($_GET['id'] ?? 0);

if ($id_demande <= 0) {
    redirect('dashboard.php');
}

// Récupérer la demande
$stmt = $pdo->prepare("
    SELECT d.*, c.numero_citoyen, c.id AS citoyen_table_id,
           u.nom, u.prenom, u.email, u.telephone
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    redirect('dashboard.php');
}

// Statuts autorisés pour le traitement
$statuts_valides = ['traite', 'rejete'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Requête invalide.";
        redirect('traiter_demande.php?id=' . $id_demande);
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'prendre') {
        // Seulement si la demande est en attente
        if ($demande['statut'] !== 'en_attente') {
            $_SESSION['error'] = "Cette demande n'est plus disponible.";
            redirect('traiter_demande.php?id=' . $id_demande);
        }

        $stmt = $pdo->prepare("UPDATE demandes SET agent_id = ?, statut = 'en_cours' WHERE id = ? AND statut = 'en_attente'");
        $stmt->execute([$agentId, $id_demande]);

        // Notifier le citoyen
        $stmtUser = $pdo->prepare("SELECT user_id FROM citoyens WHERE id = ?");
        $stmtUser->execute([$demande['citoyen_id']]);
        $dest = $stmtUser->fetchColumn();

        if ($dest) {
            $sujet   = "Votre demande " . $demande['numero_demande'] . " est en cours de traitement";
            $contenu = "Bonjour,\n\nVotre demande a été prise en charge par un agent. Nous vous tiendrons informé de son évolution.\n\nCordialement,\nLa Mairie";
            $stmtMsg = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) VALUES (?, ?, ?, ?)");
            $stmtMsg->execute([$agentId, $dest, $sujet, $contenu]);
        }

        $_logger->audit("DEMANDE_PRISE_EN_CHARGE", "Agent #{$agentId} — demande #{$id_demande}");
        $_SESSION["success"] = "Demande prise en charge avec succès.";
        redirect('voir_demande.php?id=' . $id_demande);

    } elseif ($action === 'traiter') {
        // Vérifier que l'agent est bien assigné à cette demande
        if ((int)$demande['agent_id'] !== $agentId) {
            $_SESSION['error'] = "Vous n'êtes pas autorisé à traiter cette demande.";
            redirect('traiter_demande.php?id=' . $id_demande);
        }

        $statut  = $_POST['statut'] ?? '';
        $reponse = trim($_POST['reponse'] ?? '');

        // Valider le statut
        if (!in_array($statut, $statuts_valides, true)) {
            $_SESSION['error'] = "Statut invalide.";
            redirect('traiter_demande.php?id=' . $id_demande);
        }

        if (empty($reponse)) {
            $_SESSION['error'] = "Une réponse est obligatoire.";
            redirect('traiter_demande.php?id=' . $id_demande);
        }

        $stmt = $pdo->prepare("UPDATE demandes SET statut = ?, commentaire_reponse = ?, date_traitement = NOW() WHERE id = ? AND agent_id = ?");
        $stmt->execute([$statut, $reponse, $id_demande, $agentId]);

        // Notifier le citoyen
        $stmtUser = $pdo->prepare("SELECT user_id FROM citoyens WHERE id = ?");
        $stmtUser->execute([$demande['citoyen_id']]);
        $dest = $stmtUser->fetchColumn();

        if ($dest) {
            $sujet   = "Votre demande " . $demande['numero_demande'] . " a été traitée";
            $contenu = "Bonjour,\n\nVotre demande a été " . ($statut === 'traite' ? 'acceptée' : 'refusée') . ".\n\nRéponse : " . $reponse . "\n\nCordialement,\nLa Mairie";
            $stmtMsg = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) VALUES (?, ?, ?, ?)");
            $stmtMsg->execute([$agentId, $dest, $sujet, $contenu]);
        }

        $_logger->audit("DEMANDE_TRAITEE", "Agent #{$agentId} — demande #{$id_demande} — statut: {$statut}");
        $_SESSION["success"] = "Demande traitée avec succès.";
        redirect('voir_demande.php?id=' . $id_demande);
    }
}

$est_assigne = ((int)$demande['agent_id'] === $agentId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traiter la demande - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background:#f8f9fa; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
        .demande-container { max-width:900px; margin:30px auto; }
        .demande-card { background:white; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,.08); margin-bottom:20px; overflow:hidden; }
        .demande-card .card-header { background:linear-gradient(135deg,#667eea,#764ba2); color:white; padding:20px; }
        .info-label { font-weight:bold; color:#667eea; }
    </style>
</head>
<body>
<div class="container demande-container">
    <div class="mb-3">
        <a href="demandes_attente.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux demandes
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Informations de la demande -->
    <div class="demande-card">
        <div class="card-header">
            <h4 class="mb-0">Demande N° <?= htmlspecialchars($demande['numero_demande'], ENT_QUOTES, 'UTF-8') ?></h4>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <p><span class="info-label">Type :</span> <?= htmlspecialchars(DEMANDE_TYPES[$demande['type_demande']] ?? $demande['type_demande'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><span class="info-label">Citoyen :</span> <?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><span class="info-label">Email :</span> <?= htmlspecialchars($demande['email'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="col-md-6">
                    <p><span class="info-label">Date :</span> <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></p>
                    <p><span class="info-label">Statut :</span>
                        <span class="badge bg-<?= STATUT_BADGES[$demande['statut']] ?? 'secondary' ?>">
                            <?= htmlspecialchars(DEMANDE_STATUTS[$demande['statut']] ?? $demande['statut'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                    <p><span class="info-label">N° Citoyen :</span> <?= htmlspecialchars($demande['numero_citoyen'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <?php if ($demande['commentaire']): ?>
            <hr>
            <p><strong>Commentaire du citoyen :</strong></p>
            <p><?= nl2br(htmlspecialchars($demande['commentaire'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>

            <?php if ($demande['fichier_joint']): ?>
            <hr>
            <a href="../uploads/documents/<?= htmlspecialchars($demande['fichier_joint'], ENT_QUOTES, 'UTF-8') ?>"
               class="btn btn-info" target="_blank" rel="noopener noreferrer">
                <i class="fas fa-download me-2"></i>Télécharger la pièce jointe
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <?php if ($demande['statut'] === 'en_attente'): ?>
    <div class="demande-card">
        <div class="card-header"><h5 class="mb-0">Prendre en charge</h5></div>
        <div class="card-body p-4">
            <p>En cliquant sur ce bouton, vous vous assignez cette demande et la passez « En cours ».</p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="prendre">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-hand-pointer me-2"></i>Prendre en charge
                </button>
            </form>
        </div>
    </div>

    <?php elseif ($demande['statut'] === 'en_cours' && $est_assigne): ?>
    <div class="demande-card">
        <div class="card-header bg-success text-white"><h5 class="mb-0">Traiter la demande</h5></div>
        <div class="card-body p-4">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="traiter">

                <div class="mb-3">
                    <label class="form-label fw-bold">Décision <span class="text-danger">*</span></label>
                    <select class="form-select" name="statut" required>
                        <option value="traite">✅ Acceptée</option>
                        <option value="rejete">❌ Refusée</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Réponse au citoyen <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reponse" rows="5" required
                        placeholder="Cette réponse sera transmise au citoyen par messagerie..."></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle me-2"></i>Valider le traitement
                </button>
            </form>
        </div>
    </div>

    <?php elseif ($demande['statut'] === 'en_cours' && !$est_assigne): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Cette demande est actuellement traitée par un autre agent.
    </div>

    <?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Cette demande a déjà été traitée le <?= date('d/m/Y H:i', strtotime($demande['date_traitement'] ?? 'now')) ?>.
    </div>
    <?php if ($demande['commentaire_reponse']): ?>
    <div class="demande-card">
        <div class="card-body p-4">
            <p><strong>Réponse apportée :</strong></p>
            <p><?= nl2br(htmlspecialchars($demande['commentaire_reponse'], ENT_QUOTES, 'UTF-8')) ?></p>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
