<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF guard
if (isset($_GET['token']) && isset($_SESSION['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        http_response_code(403);
        exit('Action non autorisée.');
    }
}

// Journaliser avant de détruire la session
if (!empty($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/config.php';
        require_once __DIR__ . '/security/logger.php';
        $logger = new Logger($pdo);
        $logger->audit('LOGOUT', "Utilisateur #{$_SESSION['user_id']} ({$_SESSION['user_email']}) déconnecté");
    } catch (Throwable $e) { /* Silencieux si la BDD est inaccessible */ }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();
header('Location: login.php');
exit();
?>
