<?php 
include('database/config.php'); 

// Récupération du Logo Dynamique
$site_logo = "logo-default.png";
if (isset($conn)) {
    $config_res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
    if ($config_res && $row = $config_res->fetch_assoc()) {
        $site_logo = $row['logo_path'];
    }
}
?>
<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Nos Formations | Enfance et Paix</title> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b5998;
            --secondary: #4a69bd;
            --accent: #10b981;
            --bg: #f8fafc;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); margin: 0; color: #1e293b; }

        /* Navigation (Identique à l'accueil) */
        header {
            background: white; padding: 15px 8%; display: flex;
            align-items: center; justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo-box { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-box img { height: 45px; }
        .logo-box span { font-weight: 800; color: var(--primary); font-size: 1.1rem; }

        nav ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; align-items: center; }
        nav ul li a { text-decoration: none; color: #64748b; font-weight: 600; transition: 0.3s; font-size: 0.95rem; }
        nav ul li a:hover, nav ul li a.active { color: var(--primary); }
        .btn-cta { background: var(--primary); color: white !important; padding: 10px 20px; border-radius: 50px; }

        /* Contenu Formations */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white; padding: 60px 8%; text-align: center;
        }

        .container { max-width: 900px; margin: -50px auto 50px; padding: 0 20px; }
        
        .form-card {
            background: white; padding: 40px; border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #475569; }
        
        input, select, textarea {
            width: 100%; padding: 14px; border: 1px solid #e2e8f0;
            border-radius: 12px; font-family: inherit; font-size: 1rem;
            box-sizing: border-box; transition: 0.3s;
        }
        
        input:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(59, 89, 152, 0.1); }

        .btn-submit {
            width: 100%; background: var(--primary); color: white; border: none;
            padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;
            font-size: 1rem; transition: 0.3s; margin-top: 10px;
        }
        .btn-submit:hover { background: var(--secondary); transform: translateY(-2px); }

        footer { text-align: center; padding: 40px; color: #94a3b8; font-size: 0.9rem; }
    </style>
</head> 
<body> 

<header>
    <a href="index.php" class="logo-box">
        <img src="assets/img/<?= $site_logo ?>" alt="Logo">
        <span>ENFANCE & PAIX</span>
    </a>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="formation.php" class="active">Formations</a></li>
            <li><a href="demande-stage.php">Demande de Stage</a></li>
            <li><a href="login.php" class="btn-cta">Se connecter</a></li>
        </ul>
    </nav>
</header>

<section class="page-header">
    <h1 style="margin:0; font-size: 2.5rem;">Nos Formations</h1>
    <p style="opacity: 0.9; margin-top: 10px;">Donnez un nouvel élan à votre carrière avec nos experts.</p>
</section>

<div class="container">
    <div class="form-card">
        <h2 style="margin-top: 0; color: var(--primary); margin-bottom: 30px;">Formulaire d'inscription</h2>
        <form action="traitement_formation.php" method="post"> 
            <div class="form-group">
                <label>Nom complet</label> 
                <input type="text" name="nom" placeholder="Ex: Moussa Diop" required> 
            </div>

            <div class="form-group">
                <label>Adresse Email</label> 
                <input type="email" name="email" placeholder="exemple@domaine.com" required> 
            </div>

            <div class="form-group">
                <label>Choisir une formation</label> 
                <select name="formation"> 
                    <option value="education">Éducation et Petite Enfance</option> 
                    <option value="developpement">Développement Personnel</option> 
                    <option value="gestion">Gestion de Projet Social</option> 
                </select> 
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-check-circle"></i> Confirmer mon inscription
            </button> 
        </form> 
    </div>
</div>

<footer>
    <p>© 2026 Enfance et Paix - Tous droits réservés</p>
</footer>

</body> 
</html>