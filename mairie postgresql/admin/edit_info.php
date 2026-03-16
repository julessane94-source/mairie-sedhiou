<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

$id_info = $_GET['id'] ?? 0;

// Récupérer l'information
$stmt = $pdo->prepare("SELECT * FROM infos_mairie WHERE id = ?");
$stmt->execute([$id_info]);
$info = $stmt->fetch();

if (!$info) {
    header('Location: informations.php');
    exit();
}

// Mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    $categorie = $_POST['categorie'] ?? 'general';
    
    $stmt = $pdo->prepare("UPDATE infos_mairie SET titre = ?, contenu = ?, categorie = ? WHERE id = ?");
    $stmt->execute([$titre, $contenu, $categorie, $id_info]);
    
    $_SESSION['success'] = "Information modifiée avec succès";
    header('Location: informations.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier information - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Modifier l'information</h3>
                    <a href="informations.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="titre" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="titre" name="titre" 
                                       value="<?= htmlspecialchars($info['titre']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="categorie" class="form-label">Catégorie</label>
                                <select class="form-select" id="categorie" name="categorie">
                                    <option value="general" <?= $info['categorie'] == 'general' ? 'selected' : '' ?>>Général</option>
                                    <option value="urgence" <?= $info['categorie'] == 'urgence' ? 'selected' : '' ?>>Urgence</option>
                                    <option value="evenement" <?= $info['categorie'] == 'evenement' ? 'selected' : '' ?>>Événement</option>
                                    <option value="travaux" <?= $info['categorie'] == 'travaux' ? 'selected' : '' ?>>Travaux</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contenu" class="form-label">Contenu</label>
                                <textarea class="form-control" id="contenu" name="contenu" rows="10" required><?= htmlspecialchars($info['contenu']) ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>