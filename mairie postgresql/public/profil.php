<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

// Générer CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$citoyen = null;
if ($user['role'] === 'public') {
    $stmt = $pdo->prepare("SELECT * FROM citoyens WHERE user_id = ?");
    $stmt->execute([$userId]);
    $citoyen = $stmt->fetch();
}

// Mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Requête invalide.";
        redirect('profil.php');
    }

    if (isset($_POST['update_info'])) {
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse   = trim($_POST['adresse'] ?? '');

        $stmt = $pdo->prepare("UPDATE users SET telephone = ? WHERE id = ?");
        $stmt->execute([$telephone, $userId]);

        if ($citoyen) {
            $stmt = $pdo->prepare("UPDATE citoyens SET adresse = ? WHERE user_id = ?");
            $stmt->execute([$adresse, $userId]);
        }

        $_SESSION['success'] = "Profil mis à jour avec succès.";
        redirect('profil.php');
    }

    if (isset($_POST['change_password'])) {
        $old_password    = $_POST['old_password'] ?? '';
        $new_password    = $_POST['new_password'] ?? '';
        $confirm_password= $_POST['confirm_password'] ?? '';

        if (!password_verify($old_password, $user['password'])) {
            $_SESSION['error'] = "Ancien mot de passe incorrect.";
        } elseif (strlen($new_password) < 8) {
            $_SESSION['error'] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $userId]);
            $_SESSION['success'] = "Mot de passe modifié avec succès.";
        }
        redirect('profil.php');
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height:100vh; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background .3s; }
        .sidebar a:hover { background:rgba(255,255,255,.1); }
        .profile-header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:30px; border-radius:10px; margin-bottom:20px; }
        .profile-avatar { width:80px; height:80px; background:white; border-radius:50%; display:flex; align-items:center; justify-content:center; }
        .profile-avatar i { font-size:40px; color:#667eea; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0 sidebar">
            <div class="p-3"><h4><i class="fas fa-user me-2"></i>Mon Espace</h4><hr></div>
            <nav>
                <a href="dashboard.php"><i class="fas fa-home"></i>Accueil</a>
                <a href="nouvelle_demande.php"><i class="fas fa-plus-circle"></i>Nouvelle demande</a>
                <a href="mes_demandes.php"><i class="fas fa-file-alt"></i>Mes demandes</a>
                <a href="messagerie.php"><i class="fas fa-envelope"></i>Messagerie</a>
                <a href="profil.php" class="active"><i class="fas fa-user-cog"></i>Mon profil</a>
                <a href="../logout.php?token=<?= urlencode($_SESSION['csrf_token'] ?? '') ?>"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
            </nav>
        </div>

        <div class="col-md-10 p-4">
            <h3 class="mb-4">Mon profil</h3>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- En-tête profil -->
            <div class="profile-header d-flex align-items-center gap-4">
                <div class="profile-avatar"><i class="fas fa-user"></i></div>
                <div>
                    <h4><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom'], ENT_QUOTES, 'UTF-8') ?></h4>
                    <p class="mb-1"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                    <span class="badge bg-light text-dark"><?= htmlspecialchars(ucfirst($user['role']), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php if ($citoyen): ?>
                        <span class="badge bg-info ms-2">N° <?= htmlspecialchars($citoyen['numero_citoyen'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <!-- Modifier les infos -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header"><h5>Informations personnelles</h5></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" name="telephone"
                                        value="<?= htmlspecialchars($user['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <?php if ($citoyen): ?>
                                <div class="mb-3">
                                    <label class="form-label">Adresse</label>
                                    <textarea class="form-control" name="adresse" rows="3"><?= htmlspecialchars($citoyen['adresse'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <?php endif; ?>
                                <button type="submit" name="update_info" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Changer le mot de passe -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header"><h5>Changer le mot de passe</h5></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <div class="mb-3">
                                    <label class="form-label">Ancien mot de passe</label>
                                    <input type="password" class="form-control" name="old_password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" name="new_password" required minlength="8">
                                    <div class="form-text">8 caractères minimum.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirmer</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-warning">
                                    <i class="fas fa-key me-2"></i>Changer le mot de passe
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
