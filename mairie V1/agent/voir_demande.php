<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    header('Location: ../login.php');
    exit();
}

$id_demande = $_GET['id'] ?? 0;

// Récupérer la demande (accessible si assignée à l'agent ou en attente)
$stmt = $pdo->prepare("
    SELECT d.*, 
           c.numero_citoyen, 
           c.adresse,
           c.date_naissance,
           c.lieu_naissance,
           u.nom as citoyen_nom, 
           u.prenom as citoyen_prenom,
           u.email as citoyen_email,
           u.telephone as citoyen_telephone
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE d.id = ? AND (d.agent_id = ? OR d.statut = 'en_attente')
");
$stmt->execute([$id_demande, $_SESSION['user_id']]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: demandes_attente.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la demande - Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Détail de la demande</h3>
                    <div>
                        <a href="<?= $demande['statut'] == 'en_attente' ? 'demandes_attente.php' : 'mes_demandes.php' ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                        <?php if ($demande['statut'] == 'en_attente'): ?>
                        <a href="traiter_demande.php?id=<?= $id_demande ?>" class="btn btn-primary">
                            <i class="fas fa-hand-pointer me-2"></i>Prendre en charge
                        </a>
                        <?php elseif ($demande['statut'] == 'en_cours'): ?>
                        <a href="traiter_demande.php?id=<?= $id_demande ?>" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Traiter la demande
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Informations demande -->
                <div class="info-section">
                    <h5 class="mb-3">Informations de la demande</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>N° Demande :</strong> <?= htmlspecialchars($demande['numero_demande']) ?></p>
                            <p><strong>Type :</strong> <?= htmlspecialchars($demande['type_demande']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></p>
                            <p><strong>Statut :</strong> 
                                <span class="badge bg-<?= $demande['statut'] == 'en_attente' ? 'warning' : ($demande['statut'] == 'en_cours' ? 'info' : 'success') ?>">
                                    <?= $demande['statut'] ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Informations citoyen -->
                <div class="info-section">
                    <h5 class="mb-3">Informations du citoyen</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Nom complet :</strong> <?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) ?></p>
                            <p><strong>N° Citoyen :</strong> <?= htmlspecialchars($demande['numero_citoyen']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Email :</strong> <?= htmlspecialchars($demande['citoyen_email']) ?></p>
                            <p><strong>Téléphone :</strong> <?= htmlspecialchars($demande['citoyen_telephone']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Date naissance :</strong> <?= htmlspecialchars($demande['date_naissance']) ?></p>
                            <p><strong>Lieu naissance :</strong> <?= htmlspecialchars($demande['lieu_naissance']) ?></p>
                        </div>
                    </div>
                    <p><strong>Adresse :</strong> <?= htmlspecialchars($demande['adresse']) ?></p>
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
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>