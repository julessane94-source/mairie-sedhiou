<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Statistiques générales
$stats = [
    'total_demandes' => $pdo->query("SELECT COUNT(*) FROM demandes")->fetchColumn(),
    'total_citoyens' => $pdo->query("SELECT COUNT(*) FROM citoyens")->fetchColumn(),
    'total_agents' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='agent'")->fetchColumn(),
    
    'demandes_par_type' => $pdo->query("
        SELECT type_demande, COUNT(*) as count 
        FROM demandes 
        GROUP BY type_demande
    ")->fetchAll(),
    
    'demandes_par_mois' => $pdo->query("
        SELECT DATE_FORMAT(date_demande, '%Y-%m') as mois, COUNT(*) as count
        FROM demandes
        GROUP BY DATE_FORMAT(date_demande, '%Y-%m')
        ORDER BY mois DESC
        LIMIT 12
    ")->fetchAll(),
    
    'temps_traitement_moyen' => $pdo->query("
        SELECT AVG(TIMESTAMPDIFF(HOUR, date_demande, date_traitement)) as moyenne
        FROM demandes
        WHERE date_traitement IS NOT NULL
    ")->fetchColumn(),
    
    'top_agents' => $pdo->query("
        SELECT u.nom, u.prenom, COUNT(d.id) as total
        FROM users u
        LEFT JOIN demandes d ON u.id = d.agent_id
        WHERE u.role = 'agent'
        GROUP BY u.id
        ORDER BY total DESC
        LIMIT 5
    ")->fetchAll()
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .main-content {
            padding: 20px;
            background: #f8f9fa;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
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
                    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                    <a href="agents.php"><i class="fas fa-users"></i>Gestion Agents</a>
                    <a href="demandes.php"><i class="fas fa-file-alt"></i>Demandes</a>
                    <a href="informations.php"><i class="fas fa-info-circle"></i>Infos Mairie</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i>Messagerie</a>
                    <a href="statistiques.php"><i class="fas fa-chart-bar"></i>Statistiques</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <h3 class="mb-4">Statistiques de la mairie</h3>
                
                <!-- KPIs -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>Total demandes</h5>
                            <h2><?= $stats['total_demandes'] ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>Citoyens inscrits</h5>
                            <h2><?= $stats['total_citoyens'] ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>Agents</h5>
                            <h2><?= $stats['total_agents'] ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h5>Traitement moyen</h5>
                            <h2><?= round($stats['temps_traitement_moyen']) ?>h</h2>
                        </div>
                    </div>
                </div>
                
                <!-- Graphiques -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5>Demandes par type</h5>
                            <div class="chart-container">
                                <canvas id="chartTypes"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5>Évolution mensuelle</h5>
                            <div class="chart-container">
                                <canvas id="chartMonthly"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top agents -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5>Top 5 agents</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Agent</th>
                                        <th>Demandes traitées</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($stats['top_agents'] as $agent): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) ?></td>
                                        <td><?= $agent['total'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Graphique des types de demandes
    const ctxTypes = document.getElementById('chartTypes').getContext('2d');
    new Chart(ctxTypes, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($stats['demandes_par_type'], 'type_demande')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($stats['demandes_par_type'], 'count')) ?>,
                backgroundColor: ['#667eea', '#764ba2', '#ff6b6b', '#4ecdc4', '#45b7d1']
            }]
        }
    });
    
    // Graphique mensuel
    const ctxMonthly = document.getElementById('chartMonthly').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($stats['demandes_par_mois'], 'mois')) ?>,
            datasets: [{
                label: 'Nombre de demandes',
                data: <?= json_encode(array_column($stats['demandes_par_mois'], 'count')) ?>,
                borderColor: '#667eea',
                tension: 0.1
            }]
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>