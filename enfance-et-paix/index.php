<?php 
session_start(); 
include('database/config.php'); 

// 1. Récupération du Logo Dynamique
$site_logo = "logo-default.png";
if (isset($conn)) {
    $config_res = $conn->query("SELECT logo_path FROM site_settings WHERE id=1");
    if ($config_res && $row = $config_res->fetch_assoc()) {
        $site_logo = $row['logo_path'];
    }
}

// 2. Logique du Formulaire de Contact
if(isset($_POST['envoyer_contact'])){
    $nom = htmlspecialchars($_POST['nom_contact']);
    $email = htmlspecialchars($_POST['email_contact']);
    $msg = htmlspecialchars($_POST['message_contact']);
    
    $stmt = $conn->prepare("INSERT INTO contacts (nom, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nom, $email, $msg);
    if($stmt->execute()){
        echo "<script>alert('Merci ! Votre message a bien été envoyé.');</script>";
    }
}
?> 

<!DOCTYPE html> 
<html lang="fr"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Enfance et Paix | Accueil</title> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b5998;
            --secondary: #4a69bd;
            --accent: #10b981;
            --bg: #f8fafc;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); margin: 0; }

        /* Navigation Moderne */
        header {
            background: white;
            padding: 15px 8%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo-box { display: flex; align-items: center; gap: 12px; }
        .logo-box img { height: 50px; }
        .logo-box span { font-weight: 800; color: var(--primary); font-size: 1.2rem; }

        nav ul { list-style: none; display: flex; gap: 25px; margin: 0; padding: 0; align-items: center; }
        nav ul li a { text-decoration: none; color: #64748b; font-weight: 600; transition: 0.3s; }
        nav ul li a:hover { color: var(--primary); }
        
        .btn-cta { background: var(--primary); color: white !important; padding: 10px 20px; border-radius: 50px; }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(59, 89, 152, 0.8), rgba(59, 89, 152, 0.8)), url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?q=80&w=2070') center/cover;
            height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .hero h1 { font-size: 3rem; margin-bottom: 10px; }

        /* Section Contact */
        .contact-section { padding: 80px 8%; max-width: 800px; margin: auto; }
        .contact-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        input, textarea { width: 100%; padding: 15px; border: 1px solid #e2e8f0; border-radius: 10px; font-family: inherit; box-sizing: border-box; }
        .btn-send { width: 100%; background: var(--accent); color: white; border: none; padding: 15px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 1rem; }

        footer { text-align: center; padding: 40px; color: #94a3b8; }
    </style>
</head> 
<body> 

<header>
    <div class="logo-box">
        <img src="assets/img/<?= $site_logo ?>" alt="Logo">
        <span>ENFANCE & PAIX</span>
    </div>
    <nav>
        <ul>
            <li><a href="formation.php">Formations</a></li>
            <li><a href="demande-stage.php">Demande de Stage</a></li>
            <li><a href="#contact">Contactez-nous</a></li>
            <li><a href="login.php" class="btn-cta">Se connecter</a></li>
        </ul>
    </nav>
</header>

<section class="hero">
    <h1>Éduquer pour la Paix</h1>
    <p>Découvrez nos programmes et rejoignez notre mission humanitaire.</p>
</section>

<section id="contact" class="contact-section">
    <div class="contact-card">
        <h2 style="color: var(--primary); text-align: center; margin-bottom: 30px;">Envoyez-nous un message</h2>
        <form method="POST">
            <div class="form-group">
                <input type="text" name="nom_contact" placeholder="Votre nom complet" required>
            </div>
            <div class="form-group">
                <input type="email" name="email_contact" placeholder="Votre adresse email" required>
            </div>
            <div class="form-group">
                <textarea name="message_contact" rows="5" placeholder="Comment pouvons-nous vous aider ?" required></textarea>
            </div>
            <button type="submit" name="envoyer_contact" class="btn-send">
                <i class="fas fa-paper-plane"></i> Envoyer le message
            </button>
        </form>
    </div>
</section>

<footer>
    <p>© 2026 Enfance et Paix - Tous droits réservés</p>
</footer>

</body> 
</html>