<?php 
session_start();
// 1. Connexion à la base de données
include('../database/config.php'); 

// 2. Sécurité : Vérification admin
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit();
}

// 3. Récupération des paramètres (Filtre + Tri)
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date_desc';

// 4. Construction de la requête SQL
$query = "SELECT d.*, f.titre as formation_nom 
          FROM demandes_stage d 
          LEFT JOIN formations f ON d.formation_id = f.id";

// Application du filtre de type
if ($type_filter === 'stage') {
    $query .= " WHERE d.formation_id IS NULL";
} elseif ($type_filter === 'formation') {
    $query .= " WHERE d.formation_id IS NOT NULL";
}

// Application du Tri
switch ($sort_by) {
    case 'name_asc': $query .= " ORDER BY d.nom_complet ASC"; break;
    case 'name_desc': $query .= " ORDER BY d.nom_complet DESC"; break;
    case 'date_asc': $query .= " ORDER BY d.date_demande ASC"; break;
    default: $query .= " ORDER BY d.date_demande DESC"; 
}

$result = $conn->query($query);

// Récupération du logo
$site_logo = "logo-default.png";
$config_res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
if ($config_res && $row = $config_res->fetch_assoc()) { $site_logo = $row['logo_path']; }
?> 

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Demandes | Enfance et Paix</title> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #3b5998; --sidebar-bg: #1e293b; --bg: #f1f5f9; --success: #10b981; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }

        /* Sidebar */
        .sidebar { width: 280px; background: var(--sidebar-bg); height: 100vh; position: fixed; color: white; padding: 30px 20px; box-sizing: border-box; }
        .sidebar-logo img { height: 50px; background: white; padding: 5px; border-radius: 8px; margin-bottom: 20px; }
        .nav-link { display: block; padding: 12px; color: #94a3b8; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }

        /* Main Content */
        .main-content { margin-left: 280px; width: calc(100% - 280px); padding: 40px; }
        
        /* Toolbar */
        .toolbar { background: white; padding: 20px; border-radius: 15px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .filter-group { display: flex; gap: 10px; }
        .btn-filter { text-decoration: none; padding: 10px 18px; border-radius: 8px; background: #f1f5f9; color: #64748b; font-weight: 600; font-size: 0.85rem; border: 1px solid #e2e8f0; }
        .btn-filter.active { background: var(--primary); color: white; border-color: var(--primary); }
        
        .action-group { display: flex; gap: 15px; align-items: center; }
        .btn-export { background: var(--success); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 0.85rem; transition: 0.3s; border: none; cursor: pointer; }
        .btn-export:hover { background: #059669; transform: translateY(-1px); }

        select { padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; font-weight: 600; cursor: pointer; outline: none; }

        /* Table Style */
        .table-container { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 18px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; }
        
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .badge-stage { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-formation { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        
        small { color: #94a3b8; font-weight: 500; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../assets/img/<?= htmlspecialchars($site_logo) ?>" alt="Logo">
    </div>
    <nav>
        <a href="dashboard.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="voir-demandes.php" class="nav-link active"><i class="fas fa-envelope-open-text"></i> Suivi Demandes</a>
        <a href="gestion-formations.php" class="nav-link"><i class="fas fa-graduation-cap"></i> Formations</a>
        <a href="../logout.php" class="nav-link" style="color: #fca5a5; margin-top: 40px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </nav>
</div>

<div class="main-content">
    <div style="margin-bottom: 30px;">
        <h1 style="margin:0; color: #1e293b;">Liste des Candidatures</h1>
        <p style="color: #64748b;">Gérez les inscriptions aux formations et les demandes de stage libre.</p>
    </div>

    <div class="toolbar">
        <div class="filter-group">
            <a href="?type=all&sort=<?= $sort_by ?>" class="btn-filter <?= $type_filter == 'all' ? 'active' : '' ?>">Tout voir</a>
            <a href="?type=stage&sort=<?= $sort_by ?>" class="btn-filter <?= $type_filter == 'stage' ? 'active' : '' ?>">Stages</a>
            <a href="?type=formation&sort=<?= $sort_by ?>" class="btn-filter <?= $type_filter == 'formation' ? 'active' : '' ?>">Formations</a>
        </div>

        <div class="action-group">
            <a href="export.php?type=<?= $type_filter ?>&sort=<?= $sort_by ?>" class="btn-export">
                <i class="fas fa-file-excel"></i> Exporter Excel
            </a>

            <select onchange="location = this.value;">
                <option value="?type=<?= $type_filter ?>&sort=date_desc" <?= $sort_by == 'date_desc' ? 'selected' : '' ?>>Plus récents</option>
                <option value="?type=<?= $type_filter ?>&sort=date_asc" <?= $sort_by == 'date_asc' ? 'selected' : '' ?>>Plus anciens</option>
                <option value="?type=<?= $type_filter ?>&sort=name_asc" <?= $sort_by == 'name_asc' ? 'selected' : '' ?>>Nom (A-Z)</option>
                <option value="?type=<?= $type_filter ?>&sort=name_desc" <?= $sort_by == 'name_desc' ? 'selected' : '' ?>>Nom (Z-A)</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Candidat / Contact</th>
                    <th>Type</th>
                    <th>Formation Assignée</th>
                    <th>Date de réception</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($row['nom_complet']) ?></div>
                            <small><i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?></small>
                        </td>
                        <td>
                            <?php if($row['formation_id']): ?>
                                <span class="badge badge-formation">FORMATION</span>
                            <?php else: ?>
                                <span class="badge badge-stage">STAGE LIBRE</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $row['formation_id'] ? "<strong>".htmlspecialchars($row['formation_nom'])."</strong>" : "<em>Candidature RH</em>" ?>
                        </td>
                        <td>
                            <i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($row['date_demande'])) ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center; color: #94a3b8; padding: 50px;">Aucune donnée trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body> 
</html>