<?php
require_once '../config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom            = trim($_POST['nom']            ?? '');
    $prenom         = trim($_POST['prenom']         ?? '');
    $email          = trim($_POST['email']          ?? '');
    $password       = $_POST['password']            ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telephone      = trim($_POST['telephone']      ?? '');
    $adresse        = trim($_POST['adresse']        ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $lieu_naissance = trim($_POST['lieu_naissance'] ?? '');

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (empty($date_naissance)) {
        $error = "La date de naissance est obligatoire pour générer le N°CIT.";
    } elseif (empty($nom) || empty($prenom) || empty($email)) {
        $error = "Les champs nom, prénom et email sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            try {
                $pdo->beginTransaction();

                // Créer l'utilisateur
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (email, password, nom, prenom, role, telephone) VALUES (?, ?, ?, ?, 'public', ?)")
                    ->execute([$email, $hashed_password, $nom, $prenom, $telephone]);
                $user_id = (int)$pdo->lastInsertId();

                // Créer le citoyen avec un N°CIT temporaire
                $pdo->prepare("INSERT INTO citoyens (user_id, numero_citoyen, adresse, date_naissance, lieu_naissance) VALUES (?, 'TEMP', ?, ?, ?)")
                    ->execute([$user_id, $adresse, $date_naissance, $lieu_naissance]);
                $citoyen_id = (int)$pdo->lastInsertId();

                // Générer le N°CIT définitif : CIT-AAAAMMJJ-NNNNN
                $numero_citoyen = generateNumeroCitoyen($date_naissance, $citoyen_id);

                // Mettre à jour avec le vrai N°CIT
                $pdo->prepare("UPDATE citoyens SET numero_citoyen = ? WHERE id = ?")
                    ->execute([$numero_citoyen, $citoyen_id]);

                $pdo->commit();
                $success = "Inscription réussie !<br>Votre <strong>N°CIT</strong> : <code class='fs-5'>"
                    . htmlspecialchars($numero_citoyen) . "</code><br><small class='text-muted'>Conservez ce numéro précieusement.</small>";

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
        body { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); min-height:100vh; padding:20px; }
        .register-card { background:white; border-radius:15px; box-shadow:0 10px 40px rgba(0,0,0,.1); padding:40px; max-width:700px; margin:0 auto; }
        .form-control,.form-select { border-radius:10px; padding:12px; border:1px solid #ddd; }
        .btn-register { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); border:none; border-radius:10px; padding:12px; color:white; font-weight:bold; width:100%; }
        .btn-register:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(102,126,234,.4); color:white; }
        .input-group-text { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; border:none; }
        .required-field::after { content:" *"; color:red; }
        .cit-info { background:#e8f4fd; border-left:4px solid #667eea; padding:12px 16px; border-radius:6px; }
    </style>
</head>
<body>
<div class="container">
    <div class="register-card">
        <div class="text-center mb-4">
            <i class="fas fa-user-plus fa-3x mb-3" style="color:#667eea;"></i>
            <h2>Inscription Citoyen</h2>
            <p class="text-muted">Créez votre compte pour accéder aux services municipaux</p>
        </div>

        <div class="cit-info mb-4">
            <i class="fas fa-info-circle me-2 text-primary"></i>
            <strong>N°CIT :</strong> Votre numéro de citoyen sera automatiquement généré au format
            <code>CIT-AAAAMMJJ-NNNNN</code> à partir de votre <strong>date de naissance</strong> et d'un <strong>numéro de registre unique</strong>.
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
            <div class="text-center mt-4">
                <a href="../login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </a>
            </div>
        <?php else: ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Nom</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="nom" value="<?= e($_POST['nom'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Prénom</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="prenom" value="<?= e($_POST['prenom'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label required-field">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    <div class="form-text">Minimum 6 caractères</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Confirmer</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="tel" class="form-control" name="telephone" value="<?= e($_POST['telephone'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label required-field">Date de naissance</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" class="form-control" name="date_naissance"
                               value="<?= e($_POST['date_naissance'] ?? '') ?>" required>
                    </div>
                    <div class="form-text text-primary"><i class="fas fa-key me-1"></i>Utilisée pour générer votre N°CIT</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lieu de naissance</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                        <input type="text" class="form-control" name="lieu_naissance"
                               value="<?= e($_POST['lieu_naissance'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Adresse complète</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                    <textarea class="form-control" name="adresse" rows="2"><?= e($_POST['adresse'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="accept_terms" required>
                <label class="form-check-label" for="accept_terms">
                    J'accepte les conditions d'utilisation et la politique de confidentialité
                </label>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus me-2"></i>S'inscrire et obtenir mon N°CIT
            </button>
        </form>

        <div class="text-center mt-4">
            <p class="text-muted">Déjà un compte ? <a href="../login.php" class="text-decoration-none">Connectez-vous</a></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
