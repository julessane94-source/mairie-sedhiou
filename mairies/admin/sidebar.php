<?php
// Sidebar pour l'espace admin
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-2 p-0 sidebar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="p-3 text-white">
        <h4><i class="fas fa-city me-2"></i>Admin</h4>
        <hr>
        <div class="text-center mb-3">
            <i class="fas fa-user-cog fa-3x mb-2"></i>
            <p class="mb-0"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Administrateur') ?></p>
        </div>
    </div>
    <nav>
        <a href="dashboard.php" class="text-white d-block p-3 <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'dashboard.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
        <a href="agents.php" class="text-white d-block p-3 <?= strpos($current_page, 'agent') !== false ? 'active' : '' ?>" style="text-decoration: none; <?= strpos($current_page, 'agent') !== false ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-users me-2"></i>Gestion Agents
        </a>
        <a href="demandes.php" class="text-white d-block p-3 <?= strpos($current_page, 'demande') !== false ? 'active' : '' ?>" style="text-decoration: none; <?= strpos($current_page, 'demande') !== false ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-file-alt me-2"></i>Toutes les demandes
        </a>
        <a href="informations.php" class="text-white d-block p-3 <?= $current_page == 'informations.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'informations.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-info-circle me-2"></i>Infos Mairie
        </a>
        <a href="messages.php" class="text-white d-block p-3 <?= $current_page == 'messages.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'messages.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-envelope me-2"></i>Messagerie
            <?php
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
            $stmt->execute([$_SESSION['user_id']]);
            $non_lus = $stmt->fetchColumn();
            if ($non_lus > 0):
            ?>
            <span class="badge bg-danger float-end"><?= $non_lus ?></span>
            <?php endif; ?>
        </a>
        <a href="statistiques.php" class="text-white d-block p-3 <?= $current_page == 'statistiques.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'statistiques.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-chart-bar me-2"></i>Statistiques
        </a>
        <a href="parametres.php" class="text-white d-block p-3 <?= $current_page == 'parametres.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'parametres.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-cog me-2"></i>Paramètres
        </a>
        <a href="../logout.php" class="text-white d-block p-3" style="text-decoration: none;">
            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
        </a>
    </nav>
</div>

<style>
.sidebar nav a:hover {
    background: rgba(255,255,255,0.1) !important;
}
.sidebar nav a.active {
    background: rgba(255,255,255,0.2);
    border-left: 4px solid white;
}
</style>