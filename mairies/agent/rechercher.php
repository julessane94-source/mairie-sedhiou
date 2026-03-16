<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    header('Location: ../login.php');
    exit();
}

$resultats = [];
$recherche_effectuee = false;

if (isset($_GET['rechercher'])) {
    $terme = $_GET['terme'] ?? '';
    $type = $_GET['type'] ?? 'tout';
    
    $sql = "
        SELECT d.*, 
               c.numero_citoyen, 
               u.nom as citoyen_nom, 
               u.prenom as citoyen_prenom,
               u.email as citoyen_email
        FROM demandes d
        JOIN citoyens c ON d.citoyen_id = c.id
        JOIN users u ON c.user_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($terme)) {
        if ($type == 'numero_demande') {
            $sql .= " AND d.numero_demande LIKE ?";
            $params[] = "%$terme%";
        } elseif ($type == 'numero_citoyen') {
            $sql .= " AND c.numero_citoyen LIKE ?";
            $params[] = "%$terme%";
        } elseif ($type == 'nom') {
            $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ?)";
            $params[] = "%$terme%";
            $params[] = "%$terme%";
        } else {
            $sql .= " AND (d.numero_demande LIKE ? OR c.numero_citoyen LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)";
            $params[] = "%$terme%";
            $params[] = "%$terme%";
            $params[] = "%$terme%";
            $params[] = "%$terme%";
        }
    }
    
    $sql .= " ORDER BY d.date_demande DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultats = $stmt->fetchAll();
    $recherche_effectuee = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche - Agent</title>
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
        
        .search-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3">
                    <h4><i class="fas fa-user-tie me-2"></i>Agent</h4>
                    <hr>
                </div>
                <nav>
                    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                    <a href="demandes_attente.php"><i class="fas fa-clock"></i>Demandes en attente</a>
                    <a href="mes_demandes.php"><i class="fas fa-file-alt"></i>Mes demandes</a>
                    <a href="rechercher.php"><i class="fas fa-search"></i>Rechercher</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i>Messagerie</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h3 class="mb-4">Recherche de demandes</h3>
                
                <div class="search-box">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="type" class="form-label">Type de recherche</label>
                            <select class="form-select" id="type" name="type">
                                <option value="tout">Tous les critères</option>
                                <option value="numero_demande">Numéro de demande</option>
                                <option value="numero_citoyen">Numéro citoyen</option>
                                <option value="nom">Nom du citoyen</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="terme" class="form-label">Terme de recherche</label>
                            <input type="text" class="form-control" id="terme" name="terme" 
                                   value="<?= htmlspecialchars($_GET['terme'] ?? '') ?>" required>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="rechercher" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Rechercher
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php if ($recherche_effectuee): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5>Résultats de la recherche (<?= count($resultats) ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($resultats) > 0): ?>
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
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($resultats as $demande): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($demande['numero_demande']) ?></td>
                                                <td><?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) ?></td>
                                                <td><?= htmlspecialchars($demande['numero_citoyen']) ?></td>
                                                <td><?= htmlspecialchars($demande['type_demande']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($demande['date_demande'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $demande['statut'] == 'en_attente' ? 'warning' : ($demande['statut'] == 'en_cours' ? 'info' : 'success') ?>">
                                                        <?= $demande['statut'] ?>
                                                    </span>
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
                                <p class="text-center text-muted my-3">Aucun résultat trouvé</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>