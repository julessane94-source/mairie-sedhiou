<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('public')) {
    redirect('../login.php');
}

$userId = (int) $_SESSION['user_id'];

// Récupérer le citoyen
$stmt = $pdo->prepare("SELECT id FROM citoyens WHERE user_id = ?");
$stmt->execute([$userId]);
$citoyen = $stmt->fetch();

// Si l'utilisateur n'a pas de fiche citoyen, rediriger
if (!$citoyen) {
    $_SESSION['error'] = "Votre profil citoyen est incomplet. Veuillez contacter la mairie.";
    redirect('dashboard.php');
}

// Récupérer toutes les demandes du citoyen
$stmt = $pdo->prepare("
    SELECT d.*, u.nom AS agent_nom, u.prenom AS agent_prenom
    FROM demandes d
    LEFT JOIN users u ON d.agent_id = u.id
    WHERE d.citoyen_id = ?
    ORDER BY d.date_demande DESC
");
$stmt->execute([$citoyen['id']]);
$demandes = $stmt->fetchAll();

$badge_class = [
    'en_attente' => 'warning',
    'en_cours'   => 'info',
    'traite'     => 'success',
    'rejete'     => 'danger',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes demandes - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height:100vh; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background .3s; }
        .sidebar a:hover { background:rgba(255,255,255,.1); }
        .demande-card { transition:transform .3s; margin-bottom:20px; }
        .demande-card:hover { transform:translateY(-5px); box-shadow:0 5px 20px rgba(0,0,0,.1); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0 sidebar">
            <div class="p-3"><h4><i class="fas fa-user me-2"></i>Mon Espace</h4><hr></div>
            <nav>
                <a href="dashboard.php"><i class="fas fa-home"></i>Accueil</a>
                <a href="nouvelle_demande.php"><i class="fas fa-plus-circle"></i>Nouvelle demande</a>
                <a href="mes_demandes.php" class="active"><i class="fas fa-file-alt"></i>Mes demandes</a>
                <a href="messagerie.php"><i class="fas fa-envelope"></i>Messagerie</a>
                <a href="profil.php"><i class="fas fa-user-cog"></i>Mon profil</a>
                <a href="../logout.php?token=<?= urlencode($_SESSION['csrf_token'] ?? '') ?>"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
            </nav>
        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Mes demandes</h3>
                <a href="nouvelle_demande.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Nouvelle demande
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if (!empty($demandes)): ?>
            <div class="row">
                <?php foreach ($demandes as $demande): ?>
                <div class="col-md-6">
                    <div class="card demande-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>N° <?= htmlspecialchars($demande['numero_demande'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="badge bg-<?= $badge_class[$demande['statut']] ?? 'secondary' ?>">
                                <?= htmlspecialchars(DEMANDE_STATUTS[$demande['statut']] ?? $demande['statut'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p><strong>Type :</strong> <?= htmlspecialchars(DEMANDE_TYPES[$demande['type_demande']] ?? $demande['type_demande'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></p>
                            <?php if ($demande['agent_nom']): ?>
                                <p><strong>Agent :</strong> <?= htmlspecialchars($demande['agent_prenom'] . ' ' . $demande['agent_nom'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <?php if ($demande['commentaire_reponse']): ?>
                                <hr>
                                <p><strong>Réponse :</strong></p>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($demande['commentaire_reponse'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h5>Aucune demande pour le moment</h5>
                <a href="nouvelle_demande.php" class="btn btn-primary mt-3">Faire une demande</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
