<?php
// Inclure config.php seulement si pas déjà fait
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/config.php';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/agent') ?>/index.php">
            <i class="fas fa-city me-2"></i>Mairie Connect
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isLoggedIn() && hasRole('agent')): ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_attente.php">Demandes en attente</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="liste_citoyens.php">Citoyens</a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center">
                <?php if (isLoggedIn()): ?>
                <span class="navbar-text me-3 text-white">
                    <i class="fas fa-user-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['user_nom'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <a href="../logout.php?token=<?= urlencode($_SESSION['csrf_token'] ?? '') ?>" class="btn btn-outline-danger btn-sm">
                    Déconnexion
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
