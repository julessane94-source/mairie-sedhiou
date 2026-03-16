<?php 
session_start(); 
include('../database/config.php'); 

// Vérification de sécurité pour l'administrateur
if(!isset($_SESSION['role']) || $_SESSION['role']!='admin'){ 
    header("Location: ../login.php"); 
    exit(); 
} 

// --- Récupération du Logo Dynamique ---
$site_logo = "logo-default.png";
$config_res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
if ($config_res && $row = $config_res->fetch_assoc()) {
    $site_logo = $row['logo_path'];
}

// --- Logique d'Ajout ---
if(isset($_POST['ajouter'])){ 
    $nom=$_POST['nom']; 
    $prenom=$_POST['prenom']; 
    $numero=$_POST['numero']; 
    $email=$_POST['email']; 
    $fonction=$_POST['fonction']; 
    $mot_de_passe=password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT); 
    
    $stmt=$conn->prepare("INSERT INTO agents(nom,prenom,numero,email,mot_de_passe,fonction,statut) VALUES(?,?,?,?,?,?,?)"); 
    $statut="actif"; 
    $stmt->bind_param("sssssss",$nom,$prenom,$numero,$email,$mot_de_passe,$fonction,$statut); 
    $stmt->execute(); 
} 

// --- Logique de Suppression ---
if(isset($_GET['supprimer'])){ 
    $id=$_GET['supprimer']; 
    $conn->query("DELETE FROM agents WHERE id_agent=$id"); 
} 

$result=$conn->query("SELECT * FROM agents ORDER BY id_agent DESC"); 
?> 

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin Dashboard | Enfance et Paix</title> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #3b5998;
            --secondary: #4a69bd;
            --accent: #f59e0b;
            --bg: #f8fafc;
            --sidebar-bg: #1e293b;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg); 
            margin: 0; display: flex; 
        }

        /* --- SIDEBAR NAV --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            color: white;
            padding: 30px 20px;
            box-sizing: border-box;
        }
        .sidebar-logo { text-align: center; margin-bottom: 40px; }
        .sidebar-logo img { height: 60px; border-radius: 12px; background: white; padding: 5px; }
        
        .nav-menu { list-style: none; padding: 0; }
        .nav-item { margin-bottom: 12px; }
        .nav-link { 
            display: flex; align-items: center; gap: 12px; padding: 12px 15px; 
            color: #94a3b8; text-decoration: none; border-radius: 10px; transition: 0.3s; 
        }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; }
        
        /* BOUTON PARAMÈTRES MIS EN AVANT */
        .nav-link.settings { color: var(--accent); border: 1px dashed var(--accent); margin-top: 30px; }
        .nav-link.settings:hover { background: var(--accent); color: white; }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 40px;
            box-sizing: border-box;
        }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .card { 
            background: white; border-radius: 16px; padding: 25px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px; 
        }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        input { padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; }
        
        .btn-add { background: var(--primary); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; }

        /* --- TABLEAU --- */
        .table-container { background: white; border-radius: 16px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; text-align: left; padding: 15px; color: #64748b; font-size: 0.8rem; text-transform: uppercase; border-bottom: 1px solid #edf2f7; }
        td { padding: 15px; border-bottom: 1px solid #edf2f7; font-size: 0.95rem; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; background: #dcfce7; color: #166534; font-weight: 700; }
        .btn-delete { color: #ef4444; text-decoration: none; font-size: 1.1rem; }
    </style>
</head> 
<body> 

<div class="sidebar">
    <div class="sidebar-logo">
        <img src="../assets/img/<?= $site_logo ?>" alt="Logo">
        <h2 style="font-size: 1.1rem; margin-top: 15px;">Admin Panel</h2>
    </div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="gestion-agents.php" class="nav-link active"><i class="fas fa-users"></i> Agents</a>
        </li>
        <li class="nav-item">
            <a href="../index.php" class="nav-link"><i class="fas fa-eye"></i> Voir le Site</a>
        </li>
        
        <li class="nav-item">
            <a href="parametres.php" class="nav-link settings">
                <i class="fas fa-cog"></i> Paramètres Logo
            </a>
        </li>

        <li class="nav-item" style="margin-top: 40px;">
            <a href="../logout.php" class="nav-link" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="header-top">
        <h1 style="margin:0; color: #1e293b;">Gestion des Agents</h1>
        <span style="color: #64748b;">Dernière mise à jour : 2026</span>
    </div>

    <div class="card">
        <h3 style="margin-top:0; color: var(--primary);"><i class="fas fa-user-plus"></i> Nouveau Collaborateur</h3>
        <form method="post" class="form-grid"> 
            <input type="text" name="nom" placeholder="Nom" required> 
            <input type="text" name="prenom" placeholder="Prénom" required> 
            <input type="text" name="numero" placeholder="Téléphone" required> 
            <input type="email" name="email" placeholder="Email" required> 
            <input type="text" name="fonction" placeholder="Fonction" required> 
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required> 
            <button type="submit" name="ajouter" class="btn-add">Inscrire l'agent</button> 
        </form> 
    </div>

    <div class="table-container card">
        <table> 
            <thead>
                <tr>
                    <th>Identité</th>
                    <th>Email</th>
                    <th>Fonction</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row=$result->fetch_assoc()){ ?> 
                <tr> 
                    <td><strong><?= strtoupper($row['nom']) ?></strong> <?= $row['prenom'] ?></td> 
                    <td><?= $row['email'] ?></td> 
                    <td><?= $row['fonction'] ?></td> 
                    <td><span class="badge"><?= $row['statut'] ?></span></td> 
                    <td style="text-align: right;">
                        <a href="?supprimer=<?= $row['id_agent'] ?>" class="btn-delete" onclick="return confirm('Supprimer cet agent ?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td> 
                </tr> 
                <?php } ?> 
            </tbody>
        </table> 
    </div>
</div>

</body> 
</html>