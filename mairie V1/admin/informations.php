<?php
require_once '../config.php';

require_once '../security/logger.php';
$_logger = new Logger($pdo);
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

// Générer CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message      = '';
$message_type = 'success';

// Ajouter une information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Requête invalide.";
        $message_type = 'danger';
    } else {
        $titre    = trim($_POST['titre'] ?? '');
        $contenu  = trim($_POST['contenu'] ?? '');
        $categorie = $_POST['categorie'] ?? 'general';

        $cats_valides = ['general', 'urgence', 'evenement', 'travaux'];
        if (empty($titre) || empty($contenu)) {
            $message = "Titre et contenu sont obligatoires.";
            $message_type = 'danger';
        } elseif (!in_array($categorie, $cats_valides, true)) {
            $message = "Catégorie invalide.";
            $message_type = 'danger';
        } else {
            $stmt = $pdo->prepare("INSERT INTO infos_mairie (titre, contenu, auteur_id, categorie) VALUES (?, ?, ?, ?)");
            $stmt->execute([$titre, $contenu, (int)$_SESSION['user_id'], $categorie]);
            $_SESSION['message'] = "Information publiée avec succès.";
            redirect('informations.php');
        }
    }
}

// Supprimer une information (avec token CSRF dans l'URL)
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
        $id = (int) $_GET['delete'];
        $stmt = $pdo->prepare("DELETE FROM infos_mairie WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Information supprimée.";
    } else {
        $_SESSION['message'] = "Action non autorisée.";
    }
    redirect('informations.php');
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Récupérer les informations
$stmt = $pdo->query("
    SELECT i.*, u.nom, u.prenom
    FROM infos_mairie i
    LEFT JOIN users u ON i.auteur_id = u.id
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
        .sidebar { min-height:100vh; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background .3s; }
        .sidebar a:hover { background:rgba(255,255,255,.1); }
        .main-content { padding:20px; background:#f8f9fa; min-height:100vh; }
        .info-card { background:white; border-radius:10px; padding:20px; margin-bottom:20px; box-shadow:0 2px 10px rgba(0,0,0,.1); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Gestion des informations</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInfoModal">
                    <i class="fas fa-plus-circle me-2"></i>Nouvelle information
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($infos as $info): ?>
                <div class="col-md-6">
                    <div class="info-card">
                        <div class="d-flex justify-content-between">
                            <h5><?= htmlspecialchars($info['titre'], ENT_QUOTES, 'UTF-8') ?></h5>
                            <div>
                                <a href="edit_info.php?id=<?= (int)$info['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="informations.php?delete=<?= (int)$info['id'] ?>&token=<?= urlencode($_SESSION['csrf_token']) ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Supprimer cette information ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <p class="text-muted"><small>
                            Publié par <?= htmlspecialchars(($info['prenom'] ?? '') . ' ' . ($info['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            le <?= date('d/m/Y H:i', strtotime($info['date_publication'])) ?>
                        </small></p>
                        <p class="mt-2"><?= nl2br(htmlspecialchars($info['contenu'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <span class="badge bg-info"><?= htmlspecialchars($info['categorie'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($infos)): ?>
                    <p class="text-muted text-center">Aucune information publiée.</p>
                <?php endif; ?>
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="titre" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="categorie">
                            <option value="general">Général</option>
                            <option value="urgence">Urgence</option>
                            <option value="evenement">Événement</option>
                            <option value="travaux">Travaux</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contenu <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="contenu" rows="5" required maxlength="10000"></textarea>
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
