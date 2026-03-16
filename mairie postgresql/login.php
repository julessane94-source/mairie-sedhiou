<?php
require_once 'config.php';
require_once 'security/rate_limiter.php';
require_once 'security/logger.php';

$logger  = new Logger($pdo);
$limiter = new RateLimiter($pdo, 5, 900, 900); // 5 essais max, fenêtre 15 min, blocage 15 min

// Générer le token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error          = '';
$lockout_seconds = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérifier CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $logger->security('CSRF_VIOLATION', 'Tentative de connexion sans token valide');
        $error = "Requête invalide. Rechargez la page et réessayez.";
        goto show_form;
    }

    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Vérifier le rate-limit AVANT de toucher à la BDD
    $rl = $limiter->check($email);
    if ($rl['blocked']) {
        $lockout_seconds = $rl['remaining_seconds'];
        $logger->security('RATE_LIMITED', "IP " . RateLimiter::getClientIp() . " bloquée — $email");
        $error = "Trop de tentatives. Réessayez dans " . RateLimiter::formatSeconds($lockout_seconds) . ".";
        goto show_form;
    }

    // Validation minimale
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        $error = "Identifiants invalides.";
        goto show_form;
    }

    // Authentification BDD uniquement (plus de compte hardcodé)
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, password, role, actif FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && (bool)$user['actif'] && password_verify($password, $user['password'])) {
        // ✅ Succès
        $limiter->record($email, true);
        $logger->audit('LOGIN_SUCCESS', "Utilisateur #{$user['id']} ({$user['email']}) connecté — rôle: {$user['role']}");

        // Régénérer l'ID de session (protection contre la fixation)
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_nom']   = $user['prenom'] . ' ' . $user['nom'];
        // Renouveler le CSRF après connexion
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        switch ($user['role']) {
            case 'admin':  redirect('admin/dashboard.php');
            case 'agent':  redirect('agent/dashboard.php');
            case 'public': redirect('public/dashboard.php');
            default:       redirect('index.php');
        }
    } else {
        // ❌ Échec
        $limiter->record($email, false);
        $logger->security('LOGIN_FAILURE', "Échec pour $email depuis " . RateLimiter::getClientIp());
        $error = "Email ou mot de passe incorrect.";

        // Vérifier combien il reste de tentatives
        $rl = $limiter->check($email);
        if (!$rl['blocked'] && $rl['attempts'] >= 3) {
            $remaining_tries = 5 - $rl['attempts'];
            $error .= " Attention : encore $remaining_tries tentative(s) avant blocage temporaire.";
        } elseif ($rl['blocked']) {
            $lockout_seconds = $rl['remaining_seconds'];
            $error = "Compte temporairement bloqué. Réessayez dans " . RateLimiter::formatSeconds($lockout_seconds) . ".";
        }
    }
}

show_form:
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mairie Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
        }
        .login-card {
            background:white; border-radius:15px;
            box-shadow:0 10px 40px rgba(0,0,0,.15);
            padding:40px; width:100%; max-width:420px;
        }
        .login-header { text-align:center; margin-bottom:30px; }
        .login-header i { font-size:50px; color:#667eea; }
        .form-control { border-radius:10px; padding:12px; border:1px solid #ddd; }
        .form-control:focus { border-color:#667eea; box-shadow:0 0 0 .2rem rgba(102,126,234,.25); }
        .btn-login {
            background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            border:none; border-radius:10px; padding:12px; color:white;
            font-weight:bold; width:100%; transition:transform .2s;
        }
        .btn-login:hover:not(:disabled) { transform:translateY(-2px); color:white; }
        .btn-login:disabled { opacity:.65; cursor:not-allowed; }
        .back-link { text-align:center; margin-top:20px; }
        .back-link a { color:#667eea; text-decoration:none; }
        .attempt-bar { height:4px; border-radius:2px; background:#f0f0f0; margin-top:8px; }
        .attempt-fill { height:100%; border-radius:2px; transition:width .5s; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <i class="fas fa-city mb-3"></i>
        <h2>Connexion</h2>
        <p class="text-muted">Plateforme des services municipaux</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2">
        <i class="fas fa-exclamation-triangle mt-1"></i>
        <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <?php endif; ?>

    <form method="POST" id="loginForm" <?= $lockout_seconds > 0 ? 'onsubmit="return false;"' : '' ?>>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email"
                    autocomplete="username" required
                    <?= $lockout_seconds > 0 ? 'disabled' : '' ?>>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password"
                    autocomplete="current-password" required
                    <?= $lockout_seconds > 0 ? 'disabled' : '' ?>>
                <button class="btn btn-outline-secondary" type="button" id="togglePwd" tabindex="-1">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login" <?= $lockout_seconds > 0 ? 'disabled' : '' ?>>
            <i class="fas fa-sign-in-alt me-2"></i>
            <?= $lockout_seconds > 0 ? 'Compte bloqué temporairement' : 'Se connecter' ?>
        </button>

        <?php if ($lockout_seconds > 0): ?>
        <div class="mt-3 text-center text-muted small">
            <i class="fas fa-clock me-1"></i>
            Déblocage automatique dans <span id="countdown"><?= $lockout_seconds ?></span> seconde(s).
        </div>
        <?php endif; ?>
    </form>

    <div class="back-link mt-4">
        <a href="index.php"><i class="fas fa-arrow-left me-1"></i>Retour à l'accueil</a>
    </div>
</div>

<script>
// Afficher/masquer mot de passe
const pwd = document.getElementById('password');
const eye = document.getElementById('eyeIcon');
document.getElementById('togglePwd')?.addEventListener('click', () => {
    const show = pwd.type === 'password';
    pwd.type = show ? 'text' : 'password';
    eye.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
});

<?php if ($lockout_seconds > 0): ?>
// Compte à rebours de déverrouillage
let remaining = <?= (int)$lockout_seconds ?>;
const countdown = document.getElementById('countdown');
const interval = setInterval(() => {
    remaining--;
    if (countdown) countdown.textContent = remaining;
    if (remaining <= 0) {
        clearInterval(interval);
        window.location.reload();
    }
}, 1000);
<?php endif; ?>
</script>
</body>
</html>
