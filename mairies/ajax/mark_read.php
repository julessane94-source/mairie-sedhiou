<?php
require_once '../config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    exit();
}

$id_message = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("UPDATE messages SET lu = TRUE WHERE id = ? AND destinataire_id = ?");
$stmt->execute([$id_message, $_SESSION['user_id']]);

echo json_encode(['success' => true]);
?>