<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('public')) {
    header('Location: ../login.php');
    exit();
}

$id_demande = $_GET['id'] ?? 0;

// Récupérer la demande du citoyen
$stmt = $pdo->prepare("
    SELECT d.*, c.numero_citoyen, u_a.nom as agent_nom, u_a.prenom as agent_prenom
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    LEFT JOIN users u_a ON d.agent_id = u_a.id
    WHERE d.id = ? AND c.user_id = ?
");
$stmt->execute([$id_demande, $_SESSION['user_id']]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: mes_demandes.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la demande - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-timeline {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline-item {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            height: 100%;
            width: 2px;
            background: #667eea;
        }
        
        .timeline-item:last-child::before {
            display: none;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: absolute;
            left: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
                <div class="p-3 text-white">
                    <h4><i class="fas fa-user me-2"></i>Mon Espace</h4>
                    <hr>
                </div>
                <nav>
                    <a href="dashboard.php" class="text-white d-block p-3" style="text-decoration: none;"><i class="fas fa-home me-2"></i>Accueil</a>
                    <a href="nouvelle_demande.php" class="text-white d-block p-3" style="text-decoration: none;"><i class="fas fa-plus-circle me-2"></i>Nouvelle demande</a>
                    <a href="mes_demandes.php" class="text-white d-block p-3" style="text-decoration: none;"><i class="fas fa-file-alt me-2"></i>Mes demandes</a>
                    <a href="messagerie.php" class="text-white d-block p-3" style="text-decoration: none;"><i class="fas fa-envelope me-2"></i>Messagerie</a>
                    <a href="../logout.php" class="text-white d-block p-3" style="text-decoration: none;"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Détail de la demande</h3>
                    <a href="mes_demandes.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Demande N° <?= htmlspecialchars($demande['numero_demande']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Type de demande :</strong> <?= htmlspecialchars($demande['type_demande']) ?></p>
                                <p><strong>Date de la demande :</strong> <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></p>
                                <p><strong>Statut :</strong> 
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
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Agent traitant :</strong> 
                                    <?= $demande['agent_nom'] ? htmlspecialchars($demande['agent_prenom'] . ' ' . $demande['agent_nom']) : 'Non assigné' ?>
                                </p>
                                <?php if ($demande['date_traitement']): ?>
                                <p><strong>Date de traitement :</strong> <?= date('d/m/Y H:i', strtotime($demande['date_traitement'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($demande['commentaire']): ?>
                        <div class="mt-4">
                            <h6>Votre message :</h6>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($demande['commentaire'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($demande['commentaire_reponse']): ?>
                        <div class="mt-4">
                            <h6>Réponse de la mairie :</h6>
                            <div class="p-3 bg-success text-white rounded">
                                <?= nl2br(htmlspecialchars($demande['commentaire_reponse'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($demande['fichier_joint']): ?>
                        <div class="mt-4">
                            <h6>Pièce jointe :</h6>
                            <a href="../uploads/<?= htmlspecialchars($demande['fichier_joint']) ?>" class="btn btn-info" target="_blank">
                                <i class="fas fa-download me-2"></i>Télécharger la pièce jointe
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <h6>Suivi de la demande :</h6>
                            <div class="status-timeline">
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="ms-5">
                                        <strong>Demande soumise</strong>
                                        <p class="text-muted"><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($demande['agent_id']): ?>
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="ms-5">
                                        <strong>Prise en charge</strong>
                                        <p class="text-muted">Par <?= htmlspecialchars($demande['agent_prenom'] . ' ' . $demande['agent_nom']) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($demande['statut'] == 'traite' || $demande['statut'] == 'rejete'): ?>
                                <div class="timeline-item">
                                    <div class="timeline-icon">
                                        <i class="fas fa-flag-checkered"></i>
                                    </div>
                                    <div class="ms-5">
                                        <strong>Demande traitée</strong>
                                        <p class="text-muted"><?= date('d/m/Y H:i', strtotime($demande['date_traitement'])) ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($demande['statut'] != 'traite' && $demande['statut'] != 'rejete'): ?>
                        <div class="mt-4 text-center">
                            <a href="messagerie.php?action=nouveau&sujet=Question sur demande <?= $demande['numero_demande'] ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Contacter le service
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>