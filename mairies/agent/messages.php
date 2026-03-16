<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    header('Location: ../login.php');
    exit();
}

// Récupérer les messages
$stmt = $pdo->prepare("
    SELECT m.*, 
           u_exp.nom as exp_nom, u_exp.prenom as exp_prenom,
           u_dest.nom as dest_nom, u_dest.prenom as dest_prenom
    FROM messages m
    JOIN users u_exp ON m.expediteur_id = u_exp.id
    JOIN users u_dest ON m.destinataire_id = u_dest.id
    WHERE m.expediteur_id = ? OR m.destinataire_id = ?
    ORDER BY m.date_envoi DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$messages = $stmt->fetchAll();

// Compter les non lus
$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
$stmt->execute([$_SESSION['user_id']]);
$non_lus = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
        }
        
        .message-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }
        
        .message-item:hover {
            background: #f8f9fa;
        }
        
        .message-item.unread {
            background: #e3f2fd;
            font-weight: bold;
        }
        
        .badge-nonlu {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3">
                    <h4><i class="fas fa-user-tie me-2"></i>Agent</h4>
                    <hr>
                </div>
                <nav>
                    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                    <a href="demandes_attente.php"><i class="fas fa-clock"></i>Demandes en attente</a>
                    <a href="mes_demandes.php"><i class="fas fa-file-alt"></i>Mes demandes</a>
                    <a href="rechercher.php"><i class="fas fa-search"></i>Rechercher</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i>Messagerie
                        <?php if ($non_lus > 0): ?>
                            <span class="badge-nonlu"><?= $non_lus ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Messagerie</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                        <i class="fas fa-pen me-2"></i>Nouveau message
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Messages reçus</h5>
                            </div>
                            <div class="card-body message-list">
                                <?php 
                                $messages_recus = array_filter($messages, function($m) {
                                    return $m['destinataire_id'] == $_SESSION['user_id'];
                                });
                                ?>
                                <?php if (count($messages_recus) > 0): ?>
                                    <?php foreach($messages_recus as $msg): ?>
                                    <div class="message-item <?= !$msg['lu'] ? 'unread' : '' ?>" 
                                         onclick="loadMessage(<?= $msg['id'] ?>)">
                                        <div class="d-flex justify-content-between">
                                            <strong><?= htmlspecialchars($msg['exp_prenom'] . ' ' . $msg['exp_nom']) ?></strong>
                                            <small><?= date('d/m', strtotime($msg['date_envoi'])) ?></small>
                                        </div>
                                        <div><?= htmlspecialchars($msg['sujet']) ?></div>
                                        <small class="text-muted"><?= substr($msg['contenu'], 0, 50) ?>...</small>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center">Aucun message</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card" id="messageDisplay">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">Sélectionnez un message</h5>
                            </div>
                            <div class="card-body text-center py-5">
                                <i class="fas fa-envelope-open fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Cliquez sur un message pour lire son contenu</p>
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
                <form method="POST" action="envoyer_message.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="destinataire" class="form-label">Destinataire</label>
                            <select class="form-select" id="destinataire" name="destinataire_id" required>
                                <option value="">Sélectionner...</option>
                                <?php
                                // Liste des administrateurs et citoyens
                                $stmt = $pdo->query("
                                    SELECT id, nom, prenom, role 
                                    FROM users 
                                    WHERE role IN ('admin', 'public')
                                    ORDER BY role, nom
                                ");
                                while($user = $stmt->fetch()) {
                                    $role_label = $user['role'] == 'admin' ? '(Admin)' : '(Citoyen)';
                                    echo '<option value="' . $user['id'] . '">' . 
                                         htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ' ' . $role_label . 
                                         '</option>';
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
        fetch('../ajax/mark_read.php?id=' + id)
            .then(() => {
                // Charger le contenu
                window.location.href = 'lire_message.php?id=' + id;
            });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>