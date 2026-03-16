<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
    $stmt->execute([(int) $_SESSION['user_id']]);
    $count = (int) $stmt->fetchColumn();

    echo json_encode(['success' => true, 'count' => $count]);
} catch (Exception $e) {
    error_log("Erreur get_message_count: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur interne']);
}
?>
