<?php
$current_page = basename($_SERVER['PHP_SELF']);
$active = fn($page) => $current_page === $page ? 'background:rgba(255,255,255,.2);border-left:4px solid white;' : '';
?>
<div class="col-md-2 p-0" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;">
    <div class="p-3 text-white text-center">
        <i class="fas fa-city fa-2x mb-2"></i>
        <h5 class="mb-0">Administration</h5>
        <hr class="border-white opacity-50">
        <small><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Administrateur', ENT_QUOTES, 'UTF-8') ?></small>
    </div>
    <nav>
        <?php
        $links = [
            ['dashboard.php',       'fa-home',              'Dashboard'],
            ['liste_citoyens.php',  'fa-id-card',           'Citoyens'],
            ['agents.php',          'fa-users',             'Agents'],
            ['demandes.php',        'fa-file-alt',          'Demandes'],
            ['informations.php',    'fa-info-circle',       'Infos Mairie'],
            ['messages.php',        'fa-envelope',          'Messagerie'],
            ['statistiques.php',    'fa-chart-bar',         'Statistiques'],
            ['export.php',          'fa-file-export',       'Exports'],
            ['parametres.php',      'fa-cog',               'Paramètres'],
        ];
        foreach ($links as [$href, $icon, $label]):
        ?>
        <a href="<?= $href ?>" class="text-white d-flex align-items-center gap-2 px-3 py-2"
           style="text-decoration:none;transition:background .2s;<?= $active($href) ?>">
            <i class="fas <?= $icon ?>" style="width:16px;"></i>
            <?= $label ?>
            <?php if ($href === 'messages.php'): ?>
            <?php
                try {
                    $s = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = FALSE");
                    $s->execute([$_SESSION['user_id']]);
                    $n = (int)$s->fetchColumn();
                    if ($n > 0) echo '<span class="badge bg-danger ms-auto">' . $n . '</span>';
                } catch (Exception $e) {}
            ?>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>

        <!-- Séparateur Outils admin -->
        <div class="px-3 py-2 mt-2" style="border-top:1px solid rgba(255,255,255,.2);">
            <small class="text-white opacity-75 text-uppercase" style="font-size:.7rem;letter-spacing:.1em;">Outils</small>
        </div>

        <?php
        $tools = [
            ['diagnostic.php',       'fa-stethoscope',    'Diagnostic'],
            ['logs.php',             'fa-clipboard-list', 'Journaux'],
            ['login_attempts.php',   'fa-shield-alt',     'Sécurité'],
        ];
        foreach ($tools as [$href, $icon, $label]):
        ?>
        <a href="<?= $href ?>" class="text-white d-flex align-items-center gap-2 px-3 py-2"
           style="text-decoration:none;transition:background .2s;<?= $active($href) ?>">
            <i class="fas <?= $icon ?>" style="width:16px;"></i>
            <?= $label ?>
        </a>
        <?php endforeach; ?>

        <a href="../logout.php?token=<?= urlencode($_SESSION['csrf_token'] ?? '') ?>"
           class="text-white d-flex align-items-center gap-2 px-3 py-2 mt-2"
           style="text-decoration:none;border-top:1px solid rgba(255,255,255,.2);">
            <i class="fas fa-sign-out-alt" style="width:16px;"></i>Déconnexion
        </a>
    </nav>
</div>
<style>
    nav a:hover { background:rgba(255,255,255,.12) !important; }
</style>
