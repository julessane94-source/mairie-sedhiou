<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    header('Location: ../login.php');
    exit();
}

// Statistiques personnelles
$stats = [
    'total_traitees' => $pdo->prepare("SELECT COUNT(*) FROM demandes WHERE agent_id = ?")->execute([$_SESSION['user_id']]) ? $pdo->fetchColumn() : 0,
    'en_cours' => $pdo->prepare("SELECT COUNT(*) FROM demandes WHERE agent_id = ? AND statut = 'en_cours'")->execute([$_SESSION['user_id']]) ? $pdo->fetchColumn() : 0,
    'terminees' => $pdo->prepare("SELECT COUNT(*) FROM demandes WHERE agent_id = ? AND statut = 'traite'")->execute([$_SESSION['user_id']]) ? $pdo->fetchColumn() : 0,
    'rejetees' => $pdo->prepare("SELECT COUNT(*) FROM demandes WHERE agent_id = ? AND statut = 'rejete'")->execute([$_SESSION['user_id']]) ? $pdo->fetchColumn() : 0,
    
    'par_type' => $pdo->prepare("
        SELECT type_demande, COUNT(*) as count 
        FROM demandes 
        WHERE agent_id = ?
        GROUP BY type_demande
    ")->execute([$_SESSION['user_id']]) ? $pdo->fetchAll() : [],
    
    'par_mois' => $pdo->prepare("
        SELECT DATE_FORMAT(date_traitement, '%Y-%m') as mois, COUNT(*) as count
        FROM demandes
        WHERE agent_id = ? AND date_traitement IS NOT NULL
        GROUP BY DATE_FORMAT(date_traitement, '%Y-%m')
        ORDER BY mois DESC
        LIMIT 6
    ")->execute([$_SESSION['user_id']]) ? $pdo->fetchAll() : [],
    
    'temps_moyen' => $pdo->prepare("
        SELECT AVG(TIMESTAMPDIFF(HOUR, date_demande, date_traitement)) as moyenne
        FROM demandes
        WHERE agent_id = ? AND date_traitement IS NOT NULL
    ")->execute([$_SESSION['user_id']]) ? $pdo->fetchColumn() : 0
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes statistiques - Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
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
                <h3 class="mb-4">Mes statistiques</h3>
                
                <!-- KPIs -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $stats['total_traitees'] ?></div>
                            <div class="text-muted">Total traitées</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $stats['en_cours'] ?></div>
                            <div class="text-muted">En cours</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $stats['terminees'] ?></div>
                            <div class="text-muted">Terminées</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= round($stats['temps_moyen']) ?>h</div>
                            <div class="text-muted">Temps moyen</div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphiques -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Demandes par type</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTypes"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Évolution mensuelle</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartMonthly"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Indicateurs de performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Taux de satisfaction</h6>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-success" style="width: 95%">95%</div>
                                </div>
                                
                                <h6>Rapidité de traitement</h6>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-info" style="width: 85%">85%</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Qualité des réponses</h6>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-warning" style="width: 90%">90%</div>
                                </div>
                                
                                <h6>Respect des délais</h6>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-primary" style="width: 88%">88%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Graphique des types
    const ctxTypes = document.getElementById('chartTypes').getContext('2d');
    new Chart(ctxTypes, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($stats['par_type'], 'type_demande')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($stats['par_type'], 'count')) ?>,
                backgroundColor: ['#667eea', '#764ba2', '#ff6b6b', '#4ecdc4']
            }]
        }
    });
    
    // Graphique mensuel
    const ctxMonthly = document.getElementById('chartMonthly').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($stats['par_mois'], 'mois')) ?>,
            datasets: [{
                label: 'Demandes traitées',
                data: <?= json_encode(array_column($stats['par_mois'], 'count')) ?>,
                borderColor: '#667eea',
                tension: 0.1
            }]
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>