<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    $destinataire_id = $_POST['destinataire_id'] ?? 0;
    $sujet = $_POST['sujet'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $destinataire_id, $sujet, $contenu])) {
        $_SESSION['success'] = "Message envoyé avec succès";
    } else {
        $_SESSION['error'] = "Erreur lors de l'envoi du message";
    }
}

header('Location: messages.php');
exit();
?>