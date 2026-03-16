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
    <title>Demande de Stage | Enfance et Paix</title> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b5998;
            --secondary: #4a69bd;
            --bg: #f8fafc;
            --accent: #10b981;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); margin: 0; color: #1e293b; }

        /* Navigation Style Accueil */
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
        .btn-cta { background: var(--primary); color: white !important; padding: 10px 25px; border-radius: 50px; }

        /* Hero Section Intermédiaire */
        .page-banner {
            background: linear-gradient(rgba(59, 89, 152, 0.9), rgba(59, 89, 152, 0.9)), url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=2000') center/cover;
            color: white; padding: 60px 8%; text-align: center;
        }

        /* Formulaire Modernisé */
        .container { max-width: 800px; margin: -40px auto 60px; padding: 0 20px; }
        .stage-card {
            background: white; padding: 40px; border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: 1 / -1; }

        label { display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 0.9rem; }
        input, textarea {
            width: 100%; padding: 14px; border: 1px solid #e2e8f0;
            border-radius: 12px; font-family: inherit; font-size: 1rem;
            box-sizing: border-box; transition: 0.3s; background: #fdfdfd;
        }
        input:focus, textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(59, 89, 152, 0.1); }

        .btn-send {
            width: 100%; background: var(--accent); color: white; border: none;
            padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;
            font-size: 1rem; transition: 0.3s; margin-top: 10px;
        }
        .btn-send:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3); }

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
            <li><a href="formation.php">Formations</a></li>
            <li><a href="demande-stage.php" class="active">Demande de Stage</a></li>
            <li><a href="login.php" class="btn-cta">Se connecter</a></li>
        </ul>
    </nav>
</header>

<section class="page-banner">
    <h1 style="margin:0; font-size: 2.2rem;">Rejoignez notre équipe</h1>
    <p style="opacity: 0.9; margin-top: 10px;">Proposez votre candidature pour un stage enrichissant au sein de notre ONG.</p>
</section>

<div class="container">
    <div class="stage-card">
        <form action="traitement_stage.php" method="post"> 
            <div class="form-grid">
                <div>
                    <label>Nom</label> 
                    <input type="text" name="nom" placeholder="Votre nom" required> 
                </div>
                <div>
                    <label>Prénom</label> 
                    <input type="text" name="prenom" placeholder="Votre prénom" required> 
                </div>
                <div class="full-width">
                    <label>Adresse Email</label> 
                    <input type="email" name="email" placeholder="nom@exemple.com" required> 
                </div>
                <div class="full-width">
                    <label>Votre motivation / Message</label> 
                    <textarea name="message" rows="5" placeholder="Décrivez votre parcours et pourquoi vous souhaitez nous rejoindre..." required></textarea> 
                </div>
            </div>

            <button type="submit" class="btn-send">
                <i class="fas fa-paper-plane"></i> Envoyer ma candidature
            </button> 
        </form> 
    </div>
</div>

<footer>
    <p>© 2026 Enfance et Paix - Tous droits réservés</p>
</footer>

</body> 
</html>