<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Marquer comme lu
if (isset($_GET['read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET lu = 1 WHERE id = ? AND destinataire_id = ?");
    $stmt->execute([(int)$_GET['read'], (int)$_SESSION['user_id']]);
    header('Location: messages.php');
    exit();
}

// Récupérer les messages
$stmt = $pdo->prepare("
    SELECT m.*, 
           u_exp.nom as exp_nom, u_exp.prenom as exp_prenom, u_exp.role as exp_role,
           u_dest.nom as dest_nom, u_dest.prenom as dest_prenom
    FROM messages m
    JOIN users u_exp ON m.expediteur_id = u_exp.id
    JOIN users u_dest ON m.destinataire_id = u_dest.id
    WHERE m.expediteur_id = ? OR m.destinataire_id = ?
    ORDER BY m.date_envoi DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$messages = $stmt->fetchAll();

// Statistiques - CORRECTION ICI
$stats = [
    'recus' => 0,
    'envoyes' => 0,
    'non_lus' => 0
];

try {
    // Compter les messages reçus
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['recus'] = $stmt->fetchColumn() ?: 0;
    
    // Compter les messages envoyés
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE expediteur_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['envoyes'] = $stmt->fetchColumn() ?: 0;
    
    // Compter les messages non lus
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['non_lus'] = $stmt->fetchColumn() ?: 0;
    
} catch (Exception $e) {
    error_log("Erreur dans les statistiques messages: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Admin</title>
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
        
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .main-content {
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
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
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Messagerie</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                        <i class="fas fa-pen me-2"></i>Nouveau message
                    </button>
                </div>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number"><?= $stats['recus'] ?></div>
                            <div class="stats-label">Messages reçus</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number"><?= $stats['envoyes'] ?></div>
                            <div class="stats-label">Messages envoyés</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number"><?= $stats['non_lus'] ?></div>
                            <div class="stats-label">Non lus</div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="messageTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button">
                            <i class="fas fa-inbox me-2"></i>Boîte de réception
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button">
                            <i class="fas fa-paper-plane me-2"></i>Messages envoyés
                        </button>
                    </li>
                </ul>
                
                <!-- Tab content -->
                <div class="tab-content">
                    <!-- Boîte de réception -->
                    <div class="tab-pane fade show active" id="inbox">
                        <div class="card">
                            <div class="card-body">
                                <?php 
                                $inbox = array_filter($messages, function($m) {
                                    return $m['destinataire_id'] == $_SESSION['user_id'];
                                });
                                ?>
                                <?php if (count($inbox) > 0): ?>
                                    <?php foreach($inbox as $msg): ?>
                                    <div class="message-item <?= !$msg['lu'] ? 'unread' : '' ?>" 
                                         onclick="window.location.href='lire_message.php?id=<?= $msg['id'] ?>'">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?= htmlspecialchars($msg['exp_prenom'] . ' ' . $msg['exp_nom']) ?></strong>
                                                <span class="badge bg-secondary ms-2"><?= $msg['exp_role'] ?></span>
                                                <?php if (!$msg['lu']): ?>
                                                    <span class="badge bg-danger">Nouveau</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></small>
                                        </div>
                                        <div class="mt-2">
                                            <strong><?= htmlspecialchars($msg['sujet']) ?></strong>
                                        </div>
                                        <div class="text-muted">
                                            <?= substr(htmlspecialchars($msg['contenu']), 0, 100) ?>...
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted my-3">Aucun message dans la boîte de réception</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages envoyés -->
                    <div class="tab-pane fade" id="sent">
                        <div class="card">
                            <div class="card-body">
                                <?php 
                                $sent = array_filter($messages, function($m) {
                                    return $m['expediteur_id'] == $_SESSION['user_id'];
                                });
                                ?>
                                <?php if (count($sent) > 0): ?>
                                    <?php foreach($sent as $msg): ?>
                                    <div class="message-item" 
                                         onclick="window.location.href='lire_message.php?id=<?= $msg['id'] ?>'">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>À: <?= htmlspecialchars($msg['dest_prenom'] . ' ' . $msg['dest_nom']) ?></strong>
                                                <span class="badge bg-secondary ms-2"><?= $msg['dest_role'] ?></span>
                                            </div>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></small>
                                        </div>
                                        <div class="mt-2">
                                            <strong><?= htmlspecialchars($msg['sujet']) ?></strong>
                                        </div>
                                        <div class="text-muted">
                                            <?= substr(htmlspecialchars($msg['contenu']), 0, 100) ?>...
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted my-3">Aucun message envoyé</p>
                                <?php endif; ?>
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
                                $users = $pdo->query("
                                    SELECT id, nom, prenom, role, email 
                                    FROM users 
                                    WHERE id != " . $_SESSION['user_id'] . "
                                    ORDER BY role, nom
                                ")->fetchAll();
                                foreach($users as $user):
                                ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?> 
                                    (<?= $user['role'] ?>) - <?= $user['email'] ?>
                                </option>
                                <?php endforeach; ?>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>