<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    http_response_code(403);
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('messages.php');
}

// Vérifier CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    $_SESSION['error'] = "Requête invalide.";
    redirect('messages.php');
}

$expediteur_id   = (int) $_SESSION['user_id'];
$destinataire_id = (int) ($_POST['destinataire_id'] ?? 0);
$sujet           = trim($_POST['sujet'] ?? '');
$contenu         = trim($_POST['contenu'] ?? '');

// Validations
if ($destinataire_id <= 0 || empty($sujet) || empty($contenu)) {
    $_SESSION['error'] = "Tous les champs sont requis.";
    redirect('messages.php');
}

// Vérifier que le destinataire existe et est autorisé (admin ou public seulement)
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role IN ('admin', 'public') AND actif = 1");
$stmt->execute([$destinataire_id]);
if (!$stmt->fetch()) {
    $_SESSION['error'] = "Destinataire invalide.";
    redirect('messages.php');
}

// Insérer le message
$stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) VALUES (?, ?, ?, ?)");
$stmt->execute([$expediteur_id, $destinataire_id, $sujet, $contenu]);

$_SESSION['success'] = "Message envoyé avec succès.";
redirect('messages.php');
?>
