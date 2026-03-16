<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Ajouter une information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    $categorie = $_POST['categorie'] ?? 'general';
    
    $stmt = $pdo->prepare("INSERT INTO infos_mairie (titre, contenu, auteur_id, categorie) VALUES (?, ?, ?, ?)");
    $stmt->execute([$titre, $contenu, $_SESSION['user_id'], $categorie]);
    
    $_SESSION['message'] = "Information publiée avec succès";
    header('Location: informations.php');
    exit();
}

// Supprimer une information
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM infos_mairie WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['message'] = "Information supprimée";
    header('Location: informations.php');
    exit();
}

// Récupérer les informations
$stmt = $pdo->query("
    SELECT i.*, u.nom, u.prenom 
    FROM infos_mairie i
    JOIN users u ON i.auteur_id = u.id
    ORDER BY i.date_publication DESC
");
$infos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des informations - Mairie</title>
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
        
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                    <a href="demandes.php"><i class="fas fa-file-alt"></i>Demandes</a>
                    <a href="informations.php"><i class="fas fa-info-circle"></i>Infos Mairie</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i>Messagerie</a>
                    <a href="statistiques.php"><i class="fas fa-chart-bar"></i>Statistiques</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Gestion des informations</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInfoModal">
                        <i class="fas fa-plus-circle me-2"></i>Nouvelle information
                    </button>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                
                <!-- Liste des informations -->
                <div class="row">
                    <?php foreach($infos as $info): ?>
                    <div class="col-md-6">
                        <div class="info-card">
                            <div class="d-flex justify-content-between">
                                <h5><?= htmlspecialchars($info['titre']) ?></h5>
                                <div>
                                    <a href="edit_info.php?id=<?= $info['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?= $info['id'] ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Supprimer cette information ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <p class="text-muted">
                                <small>
                                    Publié par <?= htmlspecialchars($info['prenom'] . ' ' . $info['nom']) ?> 
                                    le <?= date('d/m/Y H:i', strtotime($info['date_publication'])) ?>
                                </small>
                            </p>
                            <p class="mt-3"><?= nl2br(htmlspecialchars($info['contenu'])) ?></p>
                            <span class="badge bg-info"><?= htmlspecialchars($info['categorie']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajout Information -->
    <div class="modal fade" id="addInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Publier une information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="titre" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="titre" name="titre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="categorie" class="form-label">Catégorie</label>
                            <select class="form-select" id="categorie" name="categorie">
                                <option value="general">Général</option>
                                <option value="urgence">Urgence</option>
                                <option value="evenement">Événement</option>
                                <option value="travaux">Travaux</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="contenu" class="form-label">Contenu</label>
                            <textarea class="form-control" id="contenu" name="contenu" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="ajouter" class="btn btn-primary">Publier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>