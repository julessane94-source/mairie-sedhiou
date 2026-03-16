<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$id_message = $_GET['id'] ?? 0;

// Récupérer le message
$stmt = $pdo->prepare("
    SELECT m.*, 
           u_exp.nom as exp_nom, u_exp.prenom as exp_prenom, u_exp.email as exp_email,
           u_dest.nom as dest_nom, u_dest.prenom as dest_prenom
    FROM messages m
    JOIN users u_exp ON m.expediteur_id = u_exp.id
    JOIN users u_dest ON m.destinataire_id = u_dest.id
    WHERE m.id = ? AND (m.expediteur_id = ? OR m.destinataire_id = ?)
");
$stmt->execute([$id_message, $_SESSION['user_id'], $_SESSION['user_id']]);
$message = $stmt->fetch();

if (!$message) {
    header('Location: messages.php');
    exit();
}

// Marquer comme lu si c'est le destinataire
if ($message['destinataire_id'] == $_SESSION['user_id'] && !$message['lu']) {
    $stmt = $pdo->prepare("UPDATE messages SET lu = TRUE WHERE id = ?");
    $stmt->execute([$id_message]);
}

// Envoyer une réponse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repondre'])) {
    $reponse = $_POST['reponse'] ?? '';
    $sujet = "Re: " . $message['sujet'];
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'], 
        $message['expediteur_id'], 
        $sujet, 
        $reponse
    ]);
    
    $_SESSION['success'] = "Réponse envoyée";
    header('Location: messages.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar selon le rôle -->
            <?php include $_SESSION['user_role'] . '/../sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Message</h3>
                    <a href="messages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= htmlspecialchars($message['sujet']) ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>De :</strong> <?= htmlspecialchars($message['exp_prenom'] . ' ' . $message['exp_nom']) ?>
                            <span class="text-muted">(<?= htmlspecialchars($message['exp_email']) ?>)</span>
                        </div>
                        <div class="mb-3">
                            <strong>À :</strong> <?= htmlspecialchars($message['dest_prenom'] . ' ' . $message['dest_nom']) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($message['date_envoi'])) ?>
                        </div>
                        <hr>
                        <div class="message-content p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($message['contenu'])) ?>
                        </div>
                        
                        <?php if ($message['expediteur_id'] != $_SESSION['user_id']): ?>
                        <hr>
                        <h5 class="mb-3">Répondre</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <textarea class="form-control" name="reponse" rows="5" required></textarea>
                            </div>
                            <button type="submit" name="repondre" class="btn btn-primary">
                                <i class="fas fa-reply me-2"></i>Envoyer la réponse
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>