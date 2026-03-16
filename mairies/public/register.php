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
    } elseif (empty($numero_registre)) {
        $error = "Le numéro de registre est obligatoire";
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
                
                // Créer le citoyen avec le numéro de registre
                $stmt = $pdo->prepare("INSERT INTO citoyens (user_id, numero_citoyen, numero_registre, adresse, date_naissance, lieu_naissance) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $numero_citoyen, $numero_registre, $adresse, $date_naissance, $lieu_naissance]);
                
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
    <title>Inscription Citoyen - Mairie</title>
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
            max-width: 700px;
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .required-field::after {
            content: " *";
            color: red;
        }
        
        .info-text {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .input-group-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus mb-3"></i>
                <h2>Inscription Citoyen</h2>
                <p class="text-muted">Créez votre compte pour accéder aux services municipaux</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?= $success ?>
                </div>
                <div class="text-center mt-4">
                    <a href="../login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                    </a>
                </div>
            <?php else: ?>
            
            <form method="POST" id="registerForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nom" class="form-label required-field">Nom</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="prenom" class="form-label required-field">Prénom</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label required-field">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label required-field">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="info-text">Minimum 6 caractères</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label required-field">Confirmer</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="telephone" class="form-label required-field">Téléphone</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="numero_registre" class="form-label required-field">Numéro de registre</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        <input type="text" class="form-control" id="numero_registre" name="numero_registre" value="<?= htmlspecialchars($_POST['numero_registre'] ?? '') ?>" required>
                    </div>
                    <div class="info-text">Votre numéro d'enregistrement à l'état civil</div>
                </div>
                
                <div class="mb-3">
                    <label for="adresse" class="form-label required-field">Adresse complète</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                        <textarea class="form-control" id="adresse" name="adresse" rows="2" required><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date_naissance" class="form-label required-field">Date de naissance</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="lieu_naissance" class="form-label required-field">Lieu de naissance</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                            <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" value="<?= htmlspecialchars($_POST['lieu_naissance'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="accept_terms" required>
                    <label class="form-check-label" for="accept_terms">
                        J'accepte les conditions d'utilisation et la politique de confidentialité
                    </label>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i>S'inscrire
                </button>
            </form>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    Déjà un compte ? 
                    <a href="../login.php" class="text-decoration-none">Connectez-vous</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation du formulaire côté client
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères !');
            }
        });

        // Formatage automatique du numéro de registre (optionnel)
        document.getElementById('numero_registre')?.addEventListener('input', function(e) {
            // Supprimer les caractères non alphanumériques
            this.value = this.value.replace(/[^a-zA-Z0-9-]/g, '');
        });
    </script>
</body>
</html>