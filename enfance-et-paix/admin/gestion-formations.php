<?php 
session_start();

// 1. CONNEXION À LA BASE DE DONNÉES (Indispensable avant tout)
include('../database/config.php'); 

// 2. SÉCURITÉ : Vérification du rôle administrateur
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit();
}

// 3. INITIALISATION DES VARIABLES (Évite les erreurs "null" ou "undefined")
$site_logo = "logo-default.png";
$nb_agents = 0;
$nb_demandes = 0;

// 4. RÉCUPÉRATION DES DONNÉES (Seulement si $conn est valide)
if (isset($conn) && $conn) {
    // Logo
    $config_res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
    if ($config_res && $row = $config_res->fetch_assoc()) { 
        $site_logo = $row['logo_path']; 
    }

    // Statistiques Agents
    $res_agents = $conn->query("SELECT COUNT(*) as total FROM agents");
    if ($res_agents) { $nb_agents = $res_agents->fetch_assoc()['total']; }

    // Statistiques Demandes
    $res_demandes = $conn->query("SELECT COUNT(*) as total FROM demandes_stage");
    if ($res_demandes) { $nb_demandes = $res_demandes->fetch_assoc()['total']; }
}
?> 

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord | Enfance et Paix</title> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #3b5998; --sidebar-bg: #1e293b; --bg: #f1f5f9; --accent: #f59e0b; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }

        /* Sidebar */
        .sidebar { width: 280px; background: var(--sidebar-bg); height: 100vh; position: fixed; color: white; padding: 30px 20px; box-sizing: border-box; }
        .sidebar-logo { text-align: center; margin-bottom: 40px; }
        .sidebar-logo img { height: 60px; max-width: 100%; border-radius: 12px; background: white; padding: 5px; object-fit: contain; }
        .nav-menu { list-style: none; padding: 0; }
        .nav-link { display: flex; align-items: center; gap: 15px; padding: 15px; color: #94a3b8; text-decoration: none; border-radius: 12px; transition: 0.3s; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.settings { color: var(--accent); border: 1px dashed var(--accent); margin-top: 20px; }

        /* Contenu */
        .main-content { margin-left: 280px; width: calc(100% - 280px); padding: 40px; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }

        /* Cartes Statistiques */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; border-left: 5px solid var(--primary); box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .stat-card small { color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.7rem; }
        .stat-card .value { font-size: 1.8rem; font-weight: 800; color: var(--primary); margin-top: 5px; }

        /* Grille Gestion */
        .container-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; }
        .card { background: white; padding: 30px; text-align: center; border-radius: 20px; border: 1px solid #edf2f7; text-decoration: none; color: inherit; transition: 0.3s; display: block; }
        .card:hover { transform: translateY(-10px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border-color: var(--primary); }
        .card i { font-size: 2.5rem; color: var(--primary); margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../assets/img/<?= htmlspecialchars($site_logo) ?>" alt="Logo">
        <p style="font-size: 0.8rem; margin-top: 10px; font-weight: 700;">ESPACE ADMIN</p>
    </div>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="gestion-agent.php" class="nav-link"><i class="fas fa-user-shield"></i> Agents</a>
        <a href="gestion-formations.php" class="nav-link"><i class="fas fa-graduation-cap"></i> Formations</a>
        <a href="voir-demandes.php" class="nav-link"><i class="fas fa-envelope-open-text"></i> Demandes</a>
        <a href="parametres.php" class="nav-link settings"><i class="fas fa-cog"></i> Paramètres Site</a>
        <a href="../logout.php" class="nav-link" style="margin-top: 30px; color: #fca5a5;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </nav>
</div>

<div class="main-content">
    <header class="header-top">
        <div>
            <h1 style="margin:0;">Tableau de bord</h1>
            <p style="color: #64748b; margin-top: 5px;">Bienvenue dans votre interface de gestion.</p>
        </div>
        <a href="../index.php" target="_blank" style="text-decoration:none; color:var(--primary); font-weight:700;"><i class="fas fa-external-link-alt"></i> Voir le site public</a>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <small>Agents Actifs</small>
            <div class="value"><?= (int)$nb_agents ?></div>
        </div>
        <div class="stat-card" style="border-left-color: #10b981;">
            <small>Demandes de Stage</small>
            <div class="value"><?= (int)$nb_demandes ?></div>
        </div>
        <div class="stat-card" style="border-left-color: var(--accent);">
            <small>Aujourd'hui</small>
            <div class="value" style="font-size: 1.1rem; color: #1e293b;"><?= date('d/m/Y') ?></div>
        </div>
    </div>

    <div class="container-grid">
        <a href="gestion-agent.php" class="card">
            <i class="fas fa-users-gear"></i>
            <h3>Agents</h3>
            <p>Gérer les comptes agents</p>
        </a>
        <a href="gestion-pages.php" class="card">
            <i class="fas fa-edit"></i>
            <h3>Pages</h3>
            <p>Modifier le contenu</p>
        </a>
        <a href="gestion-formations.php" class="card">
            <i class="fas fa-book-reader"></i>
            <h3>Formations</h3>
            <p>Gérer les programmes</p>
        </a>
        <a href="voir-demandes.php" class="card">
            <i class="fas fa-user-graduate"></i>
            <h3>Demandes</h3>
            <p>Suivi candidatures</p>
        </a>
        <a href="parametres.php" class="card" style="border-color: var(--accent);">
            <i class="fas fa-sliders" style="color: var(--accent);"></i>
            <h3>Réglages</h3>
            <p>Logo & Infos Site</p>
        </a>
    </div>
</div>

</body> 
</html>