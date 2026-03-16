<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('public')) {
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
    $stmt->execute([$_SESSION['user_id']]);
    $count = $stmt->fetchColumn();
    
    echo json_encode(['success' => true, 'count' => (int)$count]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>