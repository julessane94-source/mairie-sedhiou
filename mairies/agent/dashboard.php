<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    header('Location: ../login.php');
    exit();
}

// Statistiques pour l'agent
// Demandes en attente (toutes)
$stmt_attente = $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut = 'en_attente'");
$demandes_attente_total = $stmt_attente->fetchColumn();

// Demandes assignées à l'agent
$stmt_mes_demandes = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
        SUM(CASE WHEN statut = 'traite' THEN 1 ELSE 0 END) as traite,
        SUM(CASE WHEN statut = 'rejete' THEN 1 ELSE 0 END) as rejete
    FROM demandes 
    WHERE agent_id = ?
");
$stmt_mes_demandes->execute([$_SESSION['user_id']]);
$stats = $stmt_mes_demandes->fetch();

// Messages non lus
$stmt_messages = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
$stmt_messages->execute([$_SESSION['user_id']]);
$messages_non_lus = $stmt_messages->fetchColumn();

// Récupérer les demandes en attente (non assignées)
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
    LIMIT 5
");
$demandes_attente = $stmt->fetchAll();

// Récupérer les demandes assignées à l'agent
$stmt = $pdo->prepare("
    SELECT d.*, 
           c.numero_citoyen, 
           u.nom as citoyen_nom, 
           u.prenom as citoyen_prenom
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE d.agent_id = ?
    ORDER BY d.date_demande DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$mes_demandes = $stmt->fetchAll();

// Récupérer les derniers messages
$stmt = $pdo->prepare("
    SELECT m.*, 
           u_exp.nom as exp_nom, u_exp.prenom as exp_prenom
    FROM messages m
    JOIN users u_exp ON m.expediteur_id = u_exp.id
    WHERE m.destinataire_id = ?
    ORDER BY m.date_envoi DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$derniers_messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Agent - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            display: block;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }
        
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            padding: 30px;
            background: #f4f6f9;
            min-height: 100vh;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .stat-icon {
            font-size: 3rem;
            color: #667eea;
            opacity: 0.2;
            position: absolute;
            bottom: 10px;
            right: 10px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .demande-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            transition: transform 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .demande-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .demande-card.attente {
            border-left-color: #ffc107;
        }
        
        .demande-card.en-cours {
            border-left-color: #17a2b8;
        }
        
        .demande-card.traite {
            border-left-color: #28a745;
        }
        
        .badge-custom {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .btn-action {
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: scale(1.05);
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .quick-action {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            border: 1px solid #e9ecef;
        }
        
        .quick-action:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        
        .quick-action i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .message-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            transition: background 0.3s;
        }
        
        .message-item:hover {
            background: #f8f9fa;
        }
        
        .message-item.unread {
            background: #e3f2fd;
        }
        
        .message-item.unread:hover {
            background: #bbdefb;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-4 text-center">
                    <i class="fas fa-user-tie fa-3x mb-3"></i>
                    <h5 class="mb-1"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Agent') ?></h5>
                    <p class="mb-0 small opacity-75">Agent municipal</p>
                    <hr class="my-3">
                </div>
                <nav>
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-home"></i>Dashboard
                    </a>
                    <a href="demandes_attente.php">
                        <i class="fas fa-clock"></i>Demandes en attente
                        <?php if ($demandes_attente_total > 0): ?>
                            <span class="badge bg-warning float-end"><?= $demandes_attente_total ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="mes_demandes.php">
                        <i class="fas fa-file-alt"></i>Mes demandes
                    </a>
                    <a href="rechercher.php">
                        <i class="fas fa-search"></i>Rechercher
                    </a>
                    <a href="messages.php">
                        <i class="fas fa-envelope"></i>Messagerie
                        <?php if ($messages_non_lus > 0): ?>
                            <span class="badge bg-danger float-end"><?= $messages_non_lus ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="statistiques.php">
                        <i class="fas fa-chart-bar"></i>Mes statistiques
                    </a>
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>Déconnexion
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Bannière de bienvenue -->
                <div class="welcome-banner d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Bonjour, <?= htmlspecialchars($_SESSION['user_nom']) ?> !</h2>
                        <p class="mb-0 opacity-75">Voici un résumé de votre activité du jour</p>
                    </div>
                    <div class="text-end">
                        <p class="mb-0 small"><?= date('l d F Y') ?></p>
                        <p class="mb-0 small"><?= date('H:i') ?></p>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $stats['en_cours'] ?? 0 ?></div>
                            <div class="stat-label">Demandes en cours</div>
                            <i class="fas fa-spinner stat-icon"></i>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $stats['traite'] ?? 0 ?></div>
                            <div class="stat-label">Demandes traitées</div>
                            <i class="fas fa-check-circle stat-icon"></i>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $demandes_attente_total ?></div>
                            <div class="stat-label">En attente</div>
                            <i class="fas fa-clock stat-icon"></i>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $messages_non_lus ?></div>
                            <div class="stat-label">Messages non lus</div>
                            <i class="fas fa-envelope stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <h4 class="section-title">Actions rapides</h4>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <a href="demandes_attente.php" style="text-decoration: none;">
                            <div class="quick-action">
                                <i class="fas fa-clock"></i>
                                <h6>Voir les demandes</h6>
                                <small class="text-muted"><?= $demandes_attente_total ?> en attente</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="rechercher.php" style="text-decoration: none;">
                            <div class="quick-action">
                                <i class="fas fa-search"></i>
                                <h6>Rechercher</h6>
                                <small class="text-muted">Trouver une demande</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="messages.php?action=nouveau" style="text-decoration: none;">
                            <div class="quick-action">
                                <i class="fas fa-envelope"></i>
                                <h6>Nouveau message</h6>
                                <small class="text-muted">Contacter</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="statistiques.php" style="text-decoration: none;">
                            <div class="quick-action">
                                <i class="fas fa-chart-bar"></i>
                                <h6>Mes stats</h6>
                                <small class="text-muted">Voir performances</small>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Demandes en attente -->
                    <div class="col-md-6">
                        <div class="header-card">
                            <h5 class="mb-3">
                                <i class="fas fa-clock text-warning me-2"></i>
                                Demandes en attente
                                <?php if ($demandes_attente_total > 0): ?>
                                    <span class="badge bg-warning float-end"><?= $demandes_attente_total ?> totales</span>
                                <?php endif; ?>
                            </h5>
                            
                            <?php if (count($demandes_attente) > 0): ?>
                                <?php foreach($demandes_attente as $demande): ?>
                                    <div class="demande-card attente">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($demande['numero_demande']) ?></h6>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></small>
                                        </div>
                                        <p class="mb-2">
                                            <strong>Type:</strong> <?= htmlspecialchars($demande['type_demande']) ?><br>
                                            <strong>Citoyen:</strong> <?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) ?>
                                        </p>
                                        <p class="text-muted small mb-3">
                                            <?= $demande['commentaire'] ? substr(htmlspecialchars($demande['commentaire']), 0, 100) . '...' : 'Aucun commentaire' ?>
                                        </p>
                                        <div class="text-end">
                                            <a href="traiter_demande.php?id=<?= $demande['id'] ?>" class="btn btn-sm btn-primary btn-action">
                                                <i class="fas fa-hand-pointer me-1"></i>Prendre en charge
                                            </a>
                                            <a href="voir_demande.php?id=<?= $demande['id'] ?>" class="btn btn-sm btn-info btn-action">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if ($demandes_attente_total > 5): ?>
                                    <div class="text-center mt-3">
                                        <a href="demandes_attente.php" class="btn btn-outline-primary btn-sm">
                                            Voir toutes les demandes en attente
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <p class="text-muted">Aucune demande en attente</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Mes demandes récentes -->
                    <div class="col-md-6">
                        <div class="header-card">
                            <h5 class="mb-3">
                                <i class="fas fa-file-alt text-primary me-2"></i>
                                Mes demandes récentes
                            </h5>
                            
                            <?php if (count($mes_demandes) > 0): ?>
                                <?php foreach($mes_demandes as $demande): ?>
                                    <div class="demande-card <?= $demande['statut'] == 'en_cours' ? 'en-cours' : ($demande['statut'] == 'traite' ? 'traite' : '') ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($demande['numero_demande']) ?></h6>
                                            <?php
                                            $badge_class = [
                                                'en_cours' => 'info',
                                                'traite' => 'success',
                                                'rejete' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $badge_class[$demande['statut']] ?> badge-custom">
                                                <?= $demande['statut'] ?>
                                            </span>
                                        </div>
                                        <p class="mb-2">
                                            <strong>Type:</strong> <?= htmlspecialchars($demande['type_demande']) ?><br>
                                            <strong>Citoyen:</strong> <?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) ?>
                                        </p>
                                        <div class="text-end">
                                            <a href="voir_demande.php?id=<?= $demande['id'] ?>" class="btn btn-sm btn-info btn-action">
                                                <i class="fas fa-eye me-1"></i>Détails
                                            </a>
                                            <?php if ($demande['statut'] == 'en_cours'): ?>
                                                <a href="traiter_demande.php?id=<?= $demande['id'] ?>" class="btn btn-sm btn-success btn-action">
                                                    <i class="fas fa-check me-1"></i>Traiter
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="mes_demandes.php" class="btn btn-outline-primary btn-sm">
                                        Voir toutes mes demandes
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Vous n'avez pas encore de demandes assignées</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Derniers messages -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="header-card">
                            <h5 class="mb-3">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                Derniers messages
                                <?php if ($messages_non_lus > 0): ?>
                                    <span class="badge bg-danger float-end"><?= $messages_non_lus ?> non lus</span>
                                <?php endif; ?>
                            </h5>
                            
                            <?php if (count($derniers_messages) > 0): ?>
                                <?php foreach($derniers_messages as $msg): ?>
                                    <div class="message-item <?= !$msg['lu'] ? 'unread' : '' ?> d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($msg['exp_prenom'] . ' ' . $msg['exp_nom']) ?></strong>
                                            <span class="mx-2">•</span>
                                            <span><?= htmlspecialchars($msg['sujet']) ?></span>
                                        </div>
                                        <div>
                                            <small class="text-muted me-3"><?= date('d/m/Y', strtotime($msg['date_envoi'])) ?></small>
                                            <a href="lire_message.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="messages.php" class="btn btn-outline-primary btn-sm">
                                        Voir tous les messages
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted my-3">Aucun message</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
    /* Styles supplémentaires pour le dashboard */
    .sidebar nav a.active {
        background: rgba(255,255,255,0.2);
        border-left-color: white;
    }
    
    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }
    
    .btn-action {
        padding: 5px 15px;
        font-size: 0.85rem;
    }
    
    .demande-card {
        position: relative;
    }
    
    .demande-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 20px;
        right: 20px;
        height: 1px;
        background: #e9ecef;
    }
    
    .demande-card:last-child::after {
        display: none;
    }
    </style>
</body>
</html>