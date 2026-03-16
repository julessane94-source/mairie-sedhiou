<?php
// Sidebar pour l'espace agent
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-2 p-0 sidebar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="p-3 text-white">
        <h4><i class="fas fa-user-tie me-2"></i>Agent</h4>
        <hr>
        <div class="text-center mb-3">
            <i class="fas fa-user fa-3x mb-2"></i>
            <p class="mb-0"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Agent') ?></p>
        </div>
    </div>
    <nav>
        <a href="dashboard.php" class="text-white d-block p-3 <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'dashboard.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
        <a href="demandes_attente.php" class="text-white d-block p-3 <?= $current_page == 'demandes_attente.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'demandes_attente.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-clock me-2"></i>Demandes en attente
            <?php
            $stmt = $pdo->query("SELECT COUNT(*) FROM demandes WHERE statut = 'en_attente'");
            $attente = $stmt->fetchColumn();
            if ($attente > 0):
            ?>
            <span class="badge bg-warning float-end"><?= $attente ?></span>
            <?php endif; ?>
        </a>
        <a href="mes_demandes.php" class="text-white d-block p-3 <?= $current_page == 'mes_demandes.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'mes_demandes.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-file-alt me-2"></i>Mes demandes
        </a>
        <a href="rechercher.php" class="text-white d-block p-3 <?= $current_page == 'rechercher.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'rechercher.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-search me-2"></i>Rechercher
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
            <i class="fas fa-chart-bar me-2"></i>Mes statistiques
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