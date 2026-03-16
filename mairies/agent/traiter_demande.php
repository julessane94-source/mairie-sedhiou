<?php include('../navbar.php'); ?>
<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    header('Location: ../login.php');
    exit();
}

$id_demande = $_GET['id'] ?? 0;

// Récupérer la demande
$stmt = $pdo->prepare("
    SELECT d.*, c.numero_citoyen, u.nom, u.prenom, u.email, u.telephone
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: dashboard.php');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $commentaire = $_POST['commentaire'] ?? '';
    
    if ($action === 'prendre') {
        // Assigner la demande à l'agent
        $stmt = $pdo->prepare("UPDATE demandes SET agent_id = ?, statut = 'en_cours' WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $id_demande]);
        
        // Ajouter un message dans la messagerie
        $stmt = $pdo->prepare("
            INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) 
            VALUES (?, ?, ?, ?)
        ");
        $sujet = "Votre demande " . $demande['numero_demande'] . " est en cours de traitement";
        $contenu = "Bonjour,\n\nVotre demande a été prise en charge par un agent. Nous vous tiendrons informé de son évolution.\n\nCordialement,\nLa Mairie";
        
        $citoyen_user = $pdo->prepare("SELECT user_id FROM citoyens WHERE id = ?");
        $citoyen_user->execute([$demande['citoyen_id']]);
        $destinataire = $citoyen_user->fetchColumn();
        
        $stmt->execute([$_SESSION['user_id'], $destinataire, $sujet, $contenu]);
        
        $_SESSION['success'] = "Demande prise en charge avec succès";
        header('Location: voir_demande.php?id=' . $id_demande);
        exit();
        
    } elseif ($action === 'traiter') {
        $statut = $_POST['statut'] ?? 'traite';
        $reponse = $_POST['reponse'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE demandes SET statut = ?, commentaire_reponse = ?, date_traitement = NOW() WHERE id = ?");
        $stmt->execute([$statut, $reponse, $id_demande]);
        
        // Envoyer un message au citoyen
        $stmt = $pdo->prepare("
            INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) 
            VALUES (?, ?, ?, ?)
        ");
        $sujet = "Votre demande " . $demande['numero_demande'] . " a été traitée";
        $contenu = "Bonjour,\n\nVotre demande a été traitée.\n\nRéponse : " . $reponse . "\n\nCordialement,\nLa Mairie";
        
        $citoyen_user = $pdo->prepare("SELECT user_id FROM citoyens WHERE id = ?");
        $citoyen_user->execute([$demande['citoyen_id']]);
        $destinataire = $citoyen_user->fetchColumn();
        
        $stmt->execute([$_SESSION['user_id'], $destinataire, $sujet, $contenu]);
        
        $_SESSION['success'] = "Demande traitée avec succès";
        header('Location: voir_demande.php?id=' . $id_demande);
        exit();
    }
}

// Vérifier si la demande est déjà assignée
$est_assigne = ($demande['agent_id'] == $_SESSION['user_id']);
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
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .demande-container {
            max-width: 900px;
            margin: 30px auto;
        }
        
        .demande-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .status-badge {
            font-size: 1rem;
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    
    <div class="demande-container">
        <div class="demande-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Traitement de la demande</h2>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <!-- Informations demande -->
            <div class="info-section">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>N° Demande :</strong> <?= htmlspecialchars($demande['numero_demande']) ?></p>
                        <p><strong>Type :</strong> <?= htmlspecialchars($demande['type_demande']) ?></p>
                        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Statut :</strong> 
                            <span class="badge bg-<?= $demande['statut'] == 'en_attente' ? 'warning' : ($demande['statut'] == 'en_cours' ? 'info' : 'success') ?> status-badge">
                                <?= $demande['statut'] ?>
                            </span>
                        </p>
                        <p><strong>Citoyen :</strong> <?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) ?></p>
                        <p><strong>N° Citoyen :</strong> <?= htmlspecialchars($demande['numero_citoyen']) ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Informations citoyen -->
            <div class="info-section">
                <h5 class="mb-3">Contact citoyen</h5>
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Email :</strong> <?= htmlspecialchars($demande['email']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($demande['telephone']) ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Commentaire du citoyen -->
            <?php if ($demande['commentaire']): ?>
            <div class="info-section">
                <h5 class="mb-3">Commentaire du citoyen</h5>
                <p><?= nl2br(htmlspecialchars($demande['commentaire'])) ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Fichier joint -->
            <?php if ($demande['fichier_joint']): ?>
            <div class="info-section">
                <h5 class="mb-3">Pièce jointe</h5>
                <a href="../uploads/<?= htmlspecialchars($demande['fichier_joint']) ?>" class="btn btn-info" target="_blank">
                    <i class="fas fa-download me-2"></i>Télécharger la pièce jointe
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Actions -->
            <?php if (!$demande['agent_id']): ?>
                <!-- Demande non assignée -->
                <form method="POST">
                    <input type="hidden" name="action" value="prendre">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-hand-pointer me-2"></i>Prendre en charge cette demande
                    </button>
                </form>
                
            <?php elseif ($est_assigne && $demande['statut'] == 'en_cours'): ?>
                <!-- Demande assignée à cet agent et en cours -->
                <div class="info-section">
                    <h5 class="mb-3">Traiter la demande</h5>
                    <form method="POST">
                        <input type="hidden" name="action" value="traiter">
                        
                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut final</label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="traite">Traité - Demande acceptée</option>
                                <option value="rejete">Rejeté - Demande refusée</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reponse" class="form-label">Réponse au citoyen</label>
                            <textarea class="form-control" id="reponse" name="reponse" rows="5" required></textarea>
                            <small class="text-muted">Cette réponse sera envoyée au citoyen par messagerie</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i>Valider le traitement
                        </button>
                    </form>
                </div>
                
            <?php elseif ($demande['statut'] == 'traite' || $demande['statut'] == 'rejete'): ?>
                <!-- Demande déjà traitée -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Cette demande a déjà été traitée le <?= date('d/m/Y H:i', strtotime($demande['date_traitement'])) ?>
                </div>
                
                <?php if ($demande['commentaire_reponse']): ?>
                <div class="info-section">
                    <h5 class="mb-3">Réponse apportée</h5>
                    <p><?= nl2br(htmlspecialchars($demande['commentaire_reponse'])) ?></p>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Demande assignée à un autre agent -->
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Cette demande est actuellement traitée par un autre agent.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>