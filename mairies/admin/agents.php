<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

$action = $_GET['action'] ?? 'liste';
$message = '';

// Ajouter un agent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $password = generatePassword();
    
    try {
        $pdo->beginTransaction();
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, nom, prenom, role, telephone) VALUES (?, ?, ?, ?, 'agent', ?)");
        $stmt->execute([$email, $hashed_password, $nom, $prenom, $telephone]);
        
        $pdo->commit();
        $message = "Agent ajouté avec succès. Mot de passe temporaire : " . $password;
        
        // Envoyer l'email avec le mot de passe (à implémenter)
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Erreur : " . $e->getMessage();
    }
}

// Supprimer un agent
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'agent'");
    $stmt->execute([$id]);
    $message = "Agent supprimé avec succès";
    header('Location: agents.php?message=' . urlencode($message));
    exit();
}

// Récupérer la liste des agents
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'agent' ORDER BY nom, prenom");
$agents = $stmt->fetchAll();

function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des agents - Mairie</title>
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
            transition: background 0.3s;
        }
        
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .main-content {
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3">
                    <h4><i class="fas fa-city me-2"></i>Admin</h4>
                    <hr>
                </div>
                <nav>
                    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                    <a href="agents.php"><i class="fas fa-users"></i>Gestion Agents</a>
                    <a href="demandes.php"><i class="fas fa-file-alt"></i>Toutes les demandes</a>
                    <a href="informations.php"><i class="fas fa-info-circle"></i>Infos Mairie</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i>Messagerie</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Gestion des agents</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                        <i class="fas fa-user-plus me-2"></i>Nouvel agent
                    </button>
                </div>
                
                <?php if (isset($_GET['message']) || $message): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message ?: $_GET['message']) ?></div>
                <?php endif; ?>
                
                <!-- Liste des agents -->
                <div class="card">
                    <div class="card-header">
                        <h5>Liste des agents municipaux</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Date création</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($agents as $agent): ?>
                                    <tr>
                                        <td><?= $agent['id'] ?></td>
                                        <td><?= htmlspecialchars($agent['nom']) ?></td>
                                        <td><?= htmlspecialchars($agent['prenom']) ?></td>
                                        <td><?= htmlspecialchars($agent['email']) ?></td>
                                        <td><?= htmlspecialchars($agent['telephone']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($agent['date_creation'])) ?></td>
                                        <td>
                                            <?php if ($agent['actif']): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_agent.php?id=<?= $agent['id'] ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?= $agent['id'] ?>" class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Supprimer cet agent ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajout Agent -->
    <div class="modal fade" id="addAgentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Un mot de passe temporaire sera généré automatiquement.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="ajouter" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>