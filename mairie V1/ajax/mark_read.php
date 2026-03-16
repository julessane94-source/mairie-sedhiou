<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

$id_message = (int) ($_GET['id'] ?? 0);

if ($id_message <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID invalide']);
    exit();
}

$stmt = $pdo->prepare("UPDATE messages SET lu = TRUE WHERE id = ? AND destinataire_id = ?");
$stmt->execute([$id_message, (int) $_SESSION['user_id']]);

echo json_encode(['success' => true, 'affected' => $stmt->rowCount()]);
?>
