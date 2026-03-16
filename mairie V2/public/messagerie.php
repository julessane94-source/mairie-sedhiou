<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? 'inbox';
$destinataire_type = $_SESSION['user_role'] === 'public' ? 'agents' : 'citoyens';

// Envoyer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    $destinataire_id = $_POST['destinataire_id'] ?? 0;
    $sujet = $_POST['sujet'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $destinataire_id, $sujet, $contenu])) {
        $success = "Message envoyé avec succès";
    } else {
        $error = "Erreur lors de l'envoi du message";
    }
}

// Marquer comme lu
if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET lu = 1 WHERE id = ? AND destinataire_id = ?");
    $stmt->execute([(int)$_GET['read'], (int)$_SESSION['user_id']]);
}

// Récupérer les messages
$stmt = $pdo->prepare("
    SELECT m.*, 
           u_exp.nom as exp_nom, u_exp.prenom as exp_prenom, u_exp.role as exp_role,
           u_dest.nom as dest_nom, u_dest.prenom as dest_prenom, u_dest.role as dest_role
    FROM messages m
    JOIN users u_exp ON m.expediteur_id = u_exp.id
    JOIN users u_dest ON m.destinataire_id = u_dest.id
    WHERE m.expediteur_id = ? OR m.destinataire_id = ?
    ORDER BY m.date_envoi DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .message-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .message-item:hover {
            background: #f8f9fa;
        }
        
        .message-item.unread {
            background: #e3f2fd;
            font-weight: bold;
        }
        
        .message-content {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar selon le rôle -->
            <?php include __DIR__ . '/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Messagerie</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                        <i class="fas fa-pen me-2"></i>Nouveau message
                    </button>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Liste des messages -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <ul class="nav nav-tabs card-header-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link <?= $action == 'inbox' ? 'active' : '' ?>" href="?action=inbox">Reçus</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $action == 'sent' ? 'active' : '' ?>" href="?action=sent">Envoyés</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body message-list">
                                <?php 
                                $filtered_messages = array_filter($messages, function($msg) use ($action) {
                                    if ($action == 'inbox') {
                                        return $msg['destinataire_id'] == $_SESSION['user_id'];
                                    } else {
                                        return $msg['expediteur_id'] == $_SESSION['user_id'];
                                    }
                                });
                                
                                foreach($filtered_messages as $msg): 
                                ?>
                                    <div class="message-item <?= !$msg['lu'] && $msg['destinataire_id'] == $_SESSION['user_id'] ? 'unread' : '' ?>" 
                                         onclick="loadMessage(<?= $msg['id'] ?>)">
                                        <div class="d-flex justify-content-between">
                                            <strong>
                                                <?php if ($action == 'inbox'): ?>
                                                    <?= htmlspecialchars($msg['exp_prenom'] . ' ' . $msg['exp_nom']) ?>
                                                <?php else: ?>
                                                    À: <?= htmlspecialchars($msg['dest_prenom'] . ' ' . $msg['dest_nom']) ?>
                                                <?php endif; ?>
                                            </strong>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($msg['date_envoi'])) ?></small>
                                        </div>
                                        <div class="mt-2">
                                            <strong><?= htmlspecialchars($msg['sujet']) ?></strong>
                                        </div>
                                        <small class="text-muted">
                                            <?= substr(htmlspecialchars($msg['contenu']), 0, 50) ?>...
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Affichage du message -->
                    <div class="col-md-8">
                        <div class="card" id="messageDisplay">
                            <div class="card-header">
                                <h5>Sélectionnez un message</h5>
                            </div>
                            <div class="card-body text-center text-muted">
                                <i class="fas fa-envelope-open fa-3x mb-3"></i>
                                <p>Cliquez sur un message pour lire son contenu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Nouveau Message -->
    <div class="modal fade" id="newMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="destinataire" class="form-label">Destinataire</label>
                            <select class="form-select" id="destinataire" name="destinataire_id" required>
                                <option value="">Sélectionner...</option>
                                <?php
                                if ($_SESSION['user_role'] == 'public') {
                                    // Pour les citoyens : lister les agents
                                    $stmt = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'agent'");
                                } else {
                                    // Pour admin/agent : lister les citoyens
                                    $stmt = $pdo->query("
                                        SELECT u.id, u.nom, u.prenom 
                                        FROM users u 
                                        JOIN citoyens c ON u.id = c.user_id
                                    ");
                                }
                                while($user = $stmt->fetch()) {
                                    echo '<option value="' . $user['id'] . '">' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sujet" class="form-label">Sujet</label>
                            <input type="text" class="form-control" id="sujet" name="sujet" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contenu" class="form-label">Message</label>
                            <textarea class="form-control" id="contenu" name="contenu" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="envoyer" class="btn btn-primary">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function loadMessage(id) {
        // Marquer comme lu
        fetch('ajax/mark_read.php?id=' + id)
            .then(() => {
                // Charger le message
                window.location.href = '?action=view&id=' + id;
            });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>