<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Statistiques
$stats = [
    'citoyens' => $pdo->query("SELECT COUNT(*) FROM citoyens")->fetchColumn(),
    'demandes' => $pdo->query("SELECT COUNT(*) FROM demandes")->fetchColumn(),
    'agents' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='agent'")->fetchColumn(),
    'messages_non_lus' => $pdo->query("SELECT COUNT(*) FROM messages WHERE destinataire_id = {$_SESSION['user_id']} AND lu = FALSE")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mairie</title>
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3">
                    <h4><i class="fas fa-city me-2"></i>Admin</h4>
                    <hr>
                </div>
                <nav>
                    <a href="dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a>
                    <a href="agents.php"><i class="fas fa-users"></i>Gestion Agents</a>
                    <a href="demandes.php"><i class="fas fa-file-alt"></i>Toutes les demandes</a>
                    <a href="informations.php"><i class="fas fa-info-circle"></i>Infos Mairie</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i>Messagerie</a>
                    <a href="statistiques.php"><i class="fas fa-chart-bar"></i>Statistiques</a>
                    <a href="parametres.php"><i class="fas fa-cog"></i>Paramètres</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="header d-flex justify-content-between align-items-center">
                    <h3>Tableau de bord Administrateur</h3>
                    <div>
                        <span class="me-3"><i class="fas fa-user me-2"></i><?= $_SESSION['user_nom'] ?></span>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Citoyens</h6>
                                    <h2><?= $stats['citoyens'] ?></h2>
                                </div>
                                <i class="fas fa-user-friends stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Demandes</h6>
                                    <h2><?= $stats['demandes'] ?></h2>
                                </div>
                                <i class="fas fa-file-alt stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Agents</h6>
                                    <h2><?= $stats['agents'] ?></h2>
                                </div>
                                <i class="fas fa-user-tie stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Messages non lus</h6>
                                    <h2><?= $stats['messages_non_lus'] ?></h2>
                                </div>
                                <i class="fas fa-envelope stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Demandes récentes -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Demandes récentes</h5>
                            </div>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>N° Demande</th>
                                            <th>Type</th>
                                            <th>Citoyen</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $pdo->query("
                                            SELECT d.*, c.numero_citoyen, u.nom, u.prenom 
                                            FROM demandes d
                                            JOIN citoyens c ON d.citoyen_id = c.id
                                            JOIN users u ON c.user_id = u.id
                                            ORDER BY d.date_demande DESC 
                                            LIMIT 10
                                        ");
                                        while($demande = $stmt->fetch()) {
                                            $statut_class = [
                                                'en_attente' => 'warning',
                                                'en_cours' => 'info',
                                                'traite' => 'success',
                                                'rejete' => 'danger'
                                            ];
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($demande['numero_demande']) . '</td>';
                                            echo '<td>' . htmlspecialchars($demande['type_demande']) . '</td>';
                                            echo '<td>' . htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) . '</td>';
                                            echo '<td>' . date('d/m/Y H:i', strtotime($demande['date_demande'])) . '</td>';
                                            echo '<td><span class="badge bg-' . $statut_class[$demande['statut']] . '">' . $demande['statut'] . '</span></td>';
                                            echo '<td>';
                                            echo '<a href="voir_demande.php?id=' . $demande['id'] . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions rapides -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Actions rapides</h5>
                            </div>
                            <div class="card-body">
                                <a href="agents.php?action=ajouter" class="btn btn-primary me-2">
                                    <i class="fas fa-user-plus me-2"></i>Nouvel agent
                                </a>
                                <a href="informations.php?action=ajouter" class="btn btn-success me-2">
                                    <i class="fas fa-plus-circle me-2"></i>Publier info
                                </a>
                                <a href="export.php?type=demandes" class="btn btn-info me-2">
                                    <i class="fas fa-download me-2"></i>Exporter (Excel)
                                </a>
                                <a href="export.php?type=demandes&format=pdf" class="btn btn-danger">
                                    <i class="fas fa-file-pdf me-2"></i>Exporter (PDF)
                                </a>
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