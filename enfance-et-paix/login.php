<?php 
session_start();
// 1. Connexion à la base de données (Essentiel avant toute requête)
include('database/config.php'); 

// 2. Récupération dynamique du Logo
$site_logo = "logo-default.png"; // Logo par défaut si la base est vide
if (isset($conn)) {
    // On récupère le chemin du logo stocké dans la table de configuration
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
    <title>Connexion | Enfance et Paix</title> 
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b5998;
            --secondary: #4a69bd;
            --bg: #f1f5f9;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; 
        }

        .login-card {
            background: white; width: 100%; max-width: 400px; padding: 40px;
            border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .login-card img { height: 70px; margin-bottom: 20px; object-fit: contain; }
        .login-card h2 { margin: 0 0 10px; color: #1e293b; font-weight: 800; }
        .login-card p { color: #64748b; margin-bottom: 30px; font-size: 0.9rem; }

        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #475569; font-size: 0.85rem; }
        
        input, select {
            width: 100%; padding: 14px; border: 1px solid #e2e8f0;
            border-radius: 12px; font-family: inherit; font-size: 1rem;
            box-sizing: border-box; transition: 0.3s; background: #f8fafc;
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(59, 89, 152, 0.1); }

        .btn-login {
            width: 100%; background: var(--primary); color: white; border: none;
            padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;
            font-size: 1rem; transition: 0.3s; margin-top: 10px;
        }
        .btn-login:hover { background: var(--secondary); transform: translateY(-1px); }

        .back-link { display: inline-block; margin-top: 25px; color: #64748b; text-decoration: none; font-size: 0.85rem; transition: 0.3s; }
        .back-link:hover { color: var(--primary); }

        .error-msg { color: #ef4444; background: #fef2f2; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; border: 1px solid #fee2e2; }
    </style>
</head> 
<body> 

<div class="login-card">
    <img src="assets/img/<?= htmlspecialchars($site_logo) ?>" alt="Logo">
    
    <h2>Bienvenue</h2>
    <p>Connectez-vous à votre espace membre</p>

    <?php if(isset($_GET['error'])): ?>
        <div class="error-msg">Identifiants incorrects ou accès refusé.</div>
    <?php endif; ?>

    <form action="auth.php" method="post"> 
        <div class="form-group">
            <label>Identifiant (Email)</label>
            <input type="email" name="email" placeholder="nom@exemple.com" required> 
        </div>

        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="••••••••" required> 
        </div>

        <div class="form-group">
            <label>Type de compte</label>
            <select name="role"> 
                <option value="agent">Espace Agent</option> 
                <option value="admin">Espace Administrateur</option> 
            </select> 
        </div>

        <button type="submit" class="btn-login">Se connecter</button> 
    </form> 

    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à l'accueil public</a>
</div>

</body> 
</html>