<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('public')) {
    header('Location: ../login.php');
    exit();
}

// Récupérer les infos du citoyen
$stmt = $pdo->prepare("
    SELECT c.*, u.nom, u.prenom, u.email, u.telephone 
    FROM citoyens c 
    JOIN users u ON c.user_id = u.id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$citoyen = $stmt->fetch();

// Récupérer les demandes du citoyen
$stmt = $pdo->prepare("
    SELECT d.*, u.nom as agent_nom, u.prenom as agent_prenom 
    FROM demandes d 
    LEFT JOIN users u ON d.agent_id = u.id 
    WHERE d.citoyen_id = ? 
    ORDER BY d.date_demande DESC
");
$stmt->execute([$citoyen['id']]);
$demandes = $stmt->fetchAll();

// Compter les messages non lus
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM messages 
    WHERE destinataire_id = ? AND lu = FALSE
");
$stmt->execute([$_SESSION['user_id']]);
$messages_non_lus = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace Citoyen - Mairie</title>
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
            transition: background 0.3s;
        }
        
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 40px;
            color: #667eea;
        }
        
        .header {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .info-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 1.1rem;
        }
        
        .service-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .service-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
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
                    <div class="text-center mb-3">
                        <i class="fas fa-id-card fa-3x mb-2"></i>
                        <p class="mb-0">N° Citoyen</p>
                        <strong><?= htmlspecialchars($citoyen['numero_citoyen']) ?></strong>
                    </div>
                </div>
                <nav>
                    <a href="dashboard.php"><i class="fas fa-home"></i>Accueil</a>
                    <a href="nouvelle_demande.php"><i class="fas fa-plus-circle"></i>Nouvelle demande</a>
                    <a href="mes_demandes.php"><i class="fas fa-file-alt"></i>Mes demandes</a>
                    <a href="messagerie.php"><i class="fas fa-envelope"></i>Messagerie 
                        <?php if ($messages_non_lus > 0): ?>
                            <span class="badge bg-danger"><?= $messages_non_lus ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="profil.php"><i class="fas fa-user-cog"></i>Mon profil</a>
                    <a href="informations.php"><i class="fas fa-info-circle"></i>Infos mairie</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="header d-flex justify-content-between align-items-center">
                    <h3>Bonjour, <?= htmlspecialchars($citoyen['prenom'] . ' ' . $citoyen['nom']) ?></h3>
                    <div>
                        <span class="info-badge">
                            <i class="fas fa-id-card me-2"></i><?= htmlspecialchars($citoyen['numero_citoyen']) ?>
                        </span>
                    </div>
                </div>
                
                <!-- Services rapides -->
                <h4 class="mb-4">Services en ligne</h4>
                <div class="row g-4">
                    <div class="col-md-4">
                        <a href="nouvelle_demande.php?type=extrait_naissance" style="text-decoration: none;">
                            <div class="service-card">
                                <i class="fas fa-baby service-icon"></i>
                                <h5>Extrait de naissance</h5>
                                <p class="text-muted">Obtenez votre acte de naissance</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <a href="nouvelle_demande.php?type=mariage" style="text-decoration: none;">
                            <div class="service-card">
                                <i class="fas fa-heart service-icon"></i>
                                <h5>Certificat de mariage</h5>
                                <p class="text-muted">Demandez votre certificat</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <a href="nouvelle_demande.php?type=deces" style="text-decoration: none;">
                            <div class="service-card">
                                <i class="fas fa-cross service-icon"></i>
                                <h5>Certificat de décès</h5>
                                <p class="text-muted">Acte de décès</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <a href="nouvelle_demande.php?type=residence" style="text-decoration: none;">
                            <div class="service-card">
                                <i class="fas fa-home service-icon"></i>
                                <h5>Certificat de résidence</h5>
                                <p class="text-muted">Attestation de domicile</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <a href="nouvelle_demande.php?type=declaration_naissance" style="text-decoration: none;">
                            <div class="service-card">
                                <i class="fas fa-calendar-plus service-icon"></i>
                                <h5>Déclarer une naissance</h5>
                                <p class="text-muted">Nouveau-né</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <a href="messagerie.php" style="text-decoration: none;">
                            <div class="service-card">
                                <i class="fas fa-question-circle service-icon"></i>
                                <h5>Contactez-nous</h5>
                                <p class="text-muted">Posez vos questions</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Demandes récentes -->
                <div class="row mt-5">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5>Mes demandes récentes</h5>
                                <a href="mes_demandes.php" class="btn btn-sm btn-primary">Voir tout</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($demandes) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>N° Demande</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Statut</th>
                                                <th>Agent traitant</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach(array_slice($demandes, 0, 5) as $demande): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($demande['numero_demande']) ?></td>
                                                <td><?= htmlspecialchars($demande['type_demande']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($demande['date_demande'])) ?></td>
                                                <td>
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
                                                </td>
                                                <td>
                                                    <?= $demande['agent_nom'] ? htmlspecialchars($demande['agent_prenom'] . ' ' . $demande['agent_nom']) : 'Non assigné' ?>
                                                </td>
                                                <td>
                                                    <a href="voir_demande.php?id=<?= $demande['id'] ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <p class="text-center text-muted my-3">Vous n'avez pas encore de demande</p>
                                <div class="text-center">
                                    <a href="nouvelle_demande.php" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-2"></i>Faire une demande
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>