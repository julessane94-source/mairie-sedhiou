<?php
require_once '../config.php';
require_once '../security/logger.php';

$_logger = new Logger($pdo);

if (!isLoggedIn()) {
    redirect('../login.php');
}

// Génération CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message      = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message      = "Requête invalide. Rechargez la page.";
        $message_type = 'danger';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password     = $_POST['new_password']     ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validations
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message      = "Tous les champs sont obligatoires.";
            $message_type = 'danger';
        } elseif ($new_password !== $confirm_password) {
            $message      = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
            $message_type = 'danger';
        } elseif (strlen($new_password) < 6) {
            $message      = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            $message_type = 'danger';
        } else {
            // Vérifier le mot de passe actuel
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($current_password, $user['password'])) {
                $message      = "Le mot de passe actuel est incorrect.";
                $message_type = 'danger';
                $_logger->security('PASSWORD_CHANGE_FAIL', "Tentative échouée pour user #{$_SESSION['user_id']}");
            } else {
                // Mettre à jour le mot de passe
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_hash, $_SESSION['user_id']]);

                $_logger->audit('PASSWORD_CHANGED', "Mot de passe changé pour user #{$_SESSION['user_id']}");
                $message      = "Votre mot de passe a été modifié avec succès.";
                $message_type = 'success';

                // Régénérer le token CSRF
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    <title>Changer mon mot de passe - Espace Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10 p-4">

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h2 class="mb-4">
                        <i class="fas fa-key text-warning me-2"></i>Changer mon mot de passe
                    </h2>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                            <?= e($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">
                                <i class="fas fa-info-circle me-1"></i>
                                Connecté en tant que : <strong><?= e($_SESSION['user_email'] ?? '') ?></strong>
                                (<?= e(ucfirst($_SESSION['user_role'] ?? '')) ?>)
                            </p>

                            <form method="POST" action="changer_mot_de_passe.php">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                                <div class="mb-3">
                                    <label for="current_password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-1"></i>Mot de passe actuel
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password"
                                               name="current_password" required autocomplete="current-password">
                                        <button class="btn btn-outline-secondary" type="button"
                                                onclick="toggleVisibility('current_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-semibold">
                                        <i class="fas fa-key me-1"></i>Nouveau mot de passe
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password"
                                               name="new_password" required minlength="6"
                                               autocomplete="new-password"
                                               oninput="checkStrength(this.value)">
                                        <button class="btn btn-outline-secondary" type="button"
                                                onclick="toggleVisibility('new_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="strength-bar" class="mt-2" style="display:none;">
                                        <div class="progress" style="height:6px;">
                                            <div id="strength-progress" class="progress-bar" style="width:0%"></div>
                                        </div>
                                        <small id="strength-text" class="text-muted"></small>
                                    </div>
                                    <div class="form-text">Minimum 6 caractères.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-semibold">
                                        <i class="fas fa-check me-1"></i>Confirmer le nouveau mot de passe
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password"
                                               name="confirm_password" required minlength="6"
                                               autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button"
                                                onclick="toggleVisibility('confirm_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="fas fa-save me-2"></i>Enregistrer le nouveau mot de passe
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <a href="dashboard.php" class="text-muted small">
                            <i class="fas fa-arrow-left me-1"></i>Retour au dashboard
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkStrength(password) {
    const bar  = document.getElementById('strength-bar');
    const prog = document.getElementById('strength-progress');
    const text = document.getElementById('strength-text');

    if (!password) { bar.style.display = 'none'; return; }
    bar.style.display = 'block';

    let score = 0;
    if (password.length >= 6)  score++;
    if (password.length >= 10) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;

    const levels = [
        { pct:20, cls:'bg-danger',  label:'Très faible' },
        { pct:40, cls:'bg-warning', label:'Faible' },
        { pct:60, cls:'bg-info',    label:'Moyen' },
        { pct:80, cls:'bg-primary', label:'Fort' },
        { pct:100,cls:'bg-success', label:'Très fort' },
    ];
    const lvl = levels[score - 1] || levels[0];
    prog.style.width = lvl.pct + '%';
    prog.className = 'progress-bar ' + lvl.cls;
    text.textContent = lvl.label;
}
</script>
</body>
</html>
