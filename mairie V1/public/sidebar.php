<?php
// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'public') {
    return;
}

// Récupérer les informations du citoyen
$citoyen = null;
$messages_non_lus = 0;

try {
    global $pdo;
    
    if (isset($pdo)) {
        // Récupérer les infos du citoyen
        $stmt = $pdo->prepare("
            SELECT c.*, u.nom, u.prenom, u.email 
            FROM citoyens c 
            JOIN users u ON c.user_id = u.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $citoyen = $stmt->fetch();
        
        // Compter les messages non lus
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
        $stmt->execute([$_SESSION['user_id']]);
        $messages_non_lus = $stmt->fetchColumn();
    }
} catch (Exception $e) {
    error_log("Erreur dans sidebar public : " . $e->getMessage());
}

// Page courante pour le menu actif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-2 p-0 sidebar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="p-3 text-white">
        <h4><i class="fas fa-user me-2"></i>Mon Espace</h4>
        <hr>
        <div class="text-center mb-3">
            <i class="fas fa-user-circle fa-3x mb-2"></i>
            <p class="mb-0"><?= htmlspecialchars($citoyen['prenom'] ?? 'Citoyen') ?> <?= htmlspecialchars($citoyen['nom'] ?? '') ?></p>
            <?php if ($citoyen && isset($citoyen['numero_citoyen'])): ?>
                <small class="text-white-50">
                    <i class="fas fa-id-card me-1"></i><?= htmlspecialchars($citoyen['numero_citoyen']) ?>
                </small>
            <?php endif; ?>
        </div>
    </div>
    <nav>
        <a href="dashboard.php" class="text-white d-block p-3 <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'dashboard.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-home me-2"></i>Tableau de bord
        </a>
        
        <a href="nouvelle_demande.php" class="text-white d-block p-3 <?= $current_page == 'nouvelle_demande.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'nouvelle_demande.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-plus-circle me-2"></i>Nouvelle demande
        </a>
        
        <a href="mes_demandes.php" class="text-white d-block p-3 <?= $current_page == 'mes_demandes.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'mes_demandes.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-file-alt me-2"></i>Mes demandes
            <?php
            // Compter les demandes en cours pour le badge
            if ($citoyen) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes WHERE citoyen_id = ? AND statut IN ('en_attente', 'en_cours')");
                $stmt->execute([$citoyen['id']]);
                $demandes_en_cours = $stmt->fetchColumn();
                if ($demandes_en_cours > 0):
                ?>
                <span class="badge bg-warning float-end"><?= $demandes_en_cours ?></span>
                <?php endif; 
            } ?>
        </a>
        
        <a href="messagerie.php" class="text-white d-block p-3 <?= $current_page == 'messagerie.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'messagerie.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-envelope me-2"></i>Messagerie
            <?php if ($messages_non_lus > 0): ?>
                <span class="badge bg-danger float-end"><?= $messages_non_lus ?></span>
            <?php endif; ?>
        </a>
        
        <a href="profil.php" class="text-white d-block p-3 <?= $current_page == 'profil.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'profil.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-user-cog me-2"></i>Mon profil
        </a>
        
        <a href="infos_mairie.php" class="text-white d-block p-3 <?= $current_page == 'infos_mairie.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'infos_mairie.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-info-circle me-2"></i>Infos mairie
        </a>
        
        <a href="faq.php" class="text-white d-block p-3 <?= $current_page == 'faq.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'faq.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-question-circle me-2"></i>FAQ
        </a>
        
        <a href="contact.php" class="text-white d-block p-3 <?= $current_page == 'contact.php' ? 'active' : '' ?>" style="text-decoration: none; <?= $current_page == 'contact.php' ? 'background: rgba(255,255,255,0.2);' : '' ?>">
            <i class="fas fa-headset me-2"></i>Contact
        </a>
        
        <a href="../logout.php" class="text-white d-block p-3" style="text-decoration: none;" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
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
/* Pour que la sidebar reste fixe sur les grands écrans */
@media (min-width: 768px) {
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
    }
    .main-content {
        margin-left: 16.66666667%;
    }
}
</style>