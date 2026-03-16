<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('public')) {
    header('Location: ../login.php');
    exit();
}

// Récupérer le citoyen
$stmt = $pdo->prepare("SELECT id FROM citoyens WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$citoyen = $stmt->fetch();

// Récupérer toutes les demandes
$stmt = $pdo->prepare("
    SELECT d.*, u.nom as agent_nom, u.prenom as agent_prenom
    FROM demandes d
    LEFT JOIN users u ON d.agent_id = u.id
    WHERE d.citoyen_id = ?
    ORDER BY d.date_demande DESC
");
$stmt->execute([$citoyen['id']]);
$demandes = $stmt->fetchAll();
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
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
        }
        
        .demande-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .demande-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3">
                    <h4><i class="fas fa-user me-2"></i>Mon Espace</h4>
                    <hr>
                </div>
                <nav>
                    <a href="dashboard.php"><i class="fas fa-home"></i>Accueil</a>
                    <a href="nouvelle_demande.php"><i class="fas fa-plus-circle"></i>Nouvelle demande</a>
                    <a href="mes_demandes.php"><i class="fas fa-file-alt"></i>Mes demandes</a>
                    <a href="messagerie.php"><i class="fas fa-envelope"></i>Messagerie</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Mes demandes</h3>
                    <a href="nouvelle_demande.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Nouvelle demande
                    </a>
                </div>
                
                <?php if (count($demandes) > 0): ?>
                    <div class="row">
                        <?php foreach($demandes as $demande): ?>
                        <div class="col-md-6">
                            <div class="card demande-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>N° <?= htmlspecialchars($demande['numero_demande']) ?></strong>
                                    <?php
                                    $badge_class = [
                                        'en_attente' => 'warning',
                                        'en_cours' => 'info',
                                        'traite' => 'success',
                                        'rejete' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $badge_class[$demande['statut']] ?>">
                                        <?= $demande['statut'] ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($demande['type_demande']) ?></h6>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            Demandé le <?= date('d/m/Y', strtotime($demande['date_demande'])) ?>
                                        </small>
                                    </p>
                                    <p class="card-text">
                                        <?= $demande['commentaire'] ? substr(htmlspecialchars($demande['commentaire']), 0, 100) . '...' : 'Aucun commentaire' ?>
                                    </p>
                                    <a href="voir_demande.php?id=<?= $demande['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-2"></i>Voir détails
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                        <h5>Aucune demande pour le moment</h5>
                        <p>Commencez par faire votre première demande en ligne</p>
                        <a href="nouvelle_demande.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Nouvelle demande
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>