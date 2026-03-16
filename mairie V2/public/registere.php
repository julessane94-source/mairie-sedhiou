<?php
require_once '../config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $numero_registre = $_POST['numero_registre'] ?? '';
    $date_naissance = $_POST['date_naissance'] ?? '';
    $lieu_naissance = $_POST['lieu_naissance'] ?? '';
    
    // Validation
    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé";
        } else {
            try {
                $pdo->beginTransaction();
                
                // Créer l'utilisateur
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password, nom, prenom, role, telephone) VALUES (?, ?, ?, ?, 'public', ?)");
                $stmt->execute([$email, $hashed_password, $nom, $prenom, $telephone]);
                $user_id = $pdo->lastInsertId();
                
                // Générer numéro citoyen
                $numero_citoyen = generateNumeroCitoyen();
                
                // Créer le citoyen
                $stmt = $pdo->prepare("INSERT INTO citoyens (user_id, numero_citoyen, adresse, date_naissance, lieu_naissance) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $numero_citoyen, $adresse, $date_naissance, $lieu_naissance]);
                
                $pdo->commit();
                $success = "Inscription réussie ! Votre numéro citoyen est : " . $numero_citoyen;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Erreur lors de l'inscription : " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header i {
            font-size: 50px;
            color: #667eea;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            color: white;
            font-weight: bold;
            width: 100%;
            transition: transform 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <i class="fas fa-user-plus mb-3"></i>
            <h2>Inscription Citoyen</h2>
            <p class="text-muted">Créez votre compte pour accéder aux services</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <div class="text-center mt-3">
                <a href="../login.php" class="btn btn-primary">Se connecter</a>
            </div>
        <?php else: ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirmer mot de passe</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="tel" class="form-control" id="telephone" name="telephone" required>
            </div>

           <div class="mb-3">
                <label for="numero_registre" class="form-label">numero_registre</label>
                <input type="numero_registre" class="form-control" id="numero_registre" name="numero_registre" required>
            </div>
            
            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse complète</label>
                <textarea class="form-control" id="adresse" name="adresse" rows="2" required></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_naissance" class="form-label">Date de naissance</label>
                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="lieu_naissance" class="form-label">Lieu de naissance</label>
                    <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" required>
                </div>
            </div>
            
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus me-2"></i>S'inscrire
            </button>
        </form>
        
        <div class="text-center mt-3">
            <a href="../login.php">Déjà un compte ? Connectez-vous</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>