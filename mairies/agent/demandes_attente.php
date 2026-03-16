<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    header('Location: ../login.php');
    exit();
}

// Récupérer toutes les demandes en attente
$stmt = $pdo->query("
    SELECT d.*, 
           c.numero_citoyen, 
           u.nom as citoyen_nom, 
           u.prenom as citoyen_prenom,
           u.email as citoyen_email,
           u.telephone as citoyen_telephone
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE d.statut = 'en_attente'
    ORDER BY d.date_demande ASC
");
$demandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes en attente - Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .demande-card {
            transition: transform 0.3s;
            border-left: 4px solid #ffc107;
        }
        
        .demande-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
                <h3 class="mb-4">Demandes en attente</h3>
                
                <?php if (count($demandes) > 0): ?>
                    <div class="row">
                        <?php foreach($demandes as $demande): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card demande-card">
                                <div class="card-header bg-warning text-white d-flex justify-content-between">
                                    <strong>N° <?= htmlspecialchars($demande['numero_demande']) ?></strong>
                                    <small><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></small>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($demande['type_demande']) ?></h6>
                                    <p class="card-text">
                                        <strong>Citoyen :</strong> <?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) ?><br>
                                        <strong>N° Citoyen :</strong> <?= htmlspecialchars($demande['numero_citoyen']) ?><br>
                                        <strong>Contact :</strong> <?= htmlspecialchars($demande['citoyen_email']) ?> / <?= htmlspecialchars($demande['citoyen_telephone']) ?>
                                    </p>
                                    <?php if ($demande['commentaire']): ?>
                                    <p class="text-muted"><small><?= substr(htmlspecialchars($demande['commentaire']), 0, 100) ?>...</small></p>
                                    <?php endif; ?>
                                    <a href="traiter_demande.php?id=<?= $demande['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-hand-pointer me-2"></i>Prendre en charge
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5>Aucune demande en attente</h5>
                        <p>Toutes les demandes ont été traitées</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>