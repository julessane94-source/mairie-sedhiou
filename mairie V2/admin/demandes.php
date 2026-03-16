<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Filtres
$type_filter = $_GET['type'] ?? '';
$statut_filter = $_GET['statut'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';

// Construction de la requête
$sql = "
    SELECT d.*, 
           c.numero_citoyen, 
           u.nom as citoyen_nom, 
           u.prenom as citoyen_prenom,
           u.email as citoyen_email,
           u.telephone as citoyen_telephone,
           ag.nom as agent_nom,
           ag.prenom as agent_prenom
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    LEFT JOIN users ag ON d.agent_id = ag.id
    WHERE 1=1
";

$params = [];

if (!empty($type_filter)) {
    $sql .= " AND d.type_demande = ?";
    $params[] = $type_filter;
}

if (!empty($statut_filter)) {
    $sql .= " AND d.statut = ?";
    $params[] = $statut_filter;
}

if (!empty($date_debut)) {
    $sql .= " AND DATE(d.date_demande) >= ?";
    $params[] = $date_debut;
}

if (!empty($date_fin)) {
    $sql .= " AND DATE(d.date_demande) <= ?";
    $params[] = $date_fin;
}

$sql .= " ORDER BY d.date_demande DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll();

// Statistiques pour les filtres
$types = $pdo->query("SELECT DISTINCT type_demande FROM demandes")->fetchAll(PDO::FETCH_COLUMN);
$statuts = ['en_attente', 'en_cours', 'traite', 'rejete'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des demandes - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 24px;
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
                <h3 class="mb-4">Gestion des demandes</h3>
                
                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM demandes")->fetchColumn() ?></div>
                            <div class="text-muted">Total demandes</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut='en_attente'")->fetchColumn() ?></div>
                            <div class="text-muted">En attente</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut='en_cours'")->fetchColumn() ?></div>
                            <div class="text-muted">En cours</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value"><?= $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut='traite'")->fetchColumn() ?></div>
                            <div class="text-muted">Traitées</div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtres -->
                <div class="filter-card">
                    <h5 class="mb-3">Filtres</h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="type" class="form-label">Type de demande</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Tous</option>
                                <?php foreach($types as $type): ?>
                                <option value="<?= $type ?>" <?= $type_filter == $type ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $type)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select" id="statut" name="statut">
                                <option value="">Tous</option>
                                <?php foreach($statuts as $statut): ?>
                                <option value="<?= $statut ?>" <?= $statut_filter == $statut ? 'selected' : '' ?>>
                                    <?= ucfirst($statut) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_debut" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?= $date_debut ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_fin" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?= $date_fin ?>">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Filtrer
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Liste des demandes -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Liste des demandes (<?= count($demandes) ?>)</h5>
                        <div>
                            <a href="export.php?type=demandes" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-2"></i>Excel
                            </a>
                            <a href="export.php?type=demandes&format=pdf" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf me-2"></i>PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($demandes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N° Demande</th>
                                        <th>Citoyen</th>
                                        <th>N° Citoyen</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th>Agent</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($demandes as $demande): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($demande['numero_demande']) ?></td>
                                        <td><?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) ?></td>
                                        <td><?= htmlspecialchars($demande['numero_citoyen']) ?></td>
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
                                            <a href="assigner_agent.php?id=<?= $demande['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-user-tag"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <p class="text-center text-muted my-3">Aucune demande trouvée</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>