<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

// ─────────────────────────────────────────────
// TESTS
// ─────────────────────────────────────────────

$tests = [];

// 1. Version PHP
$php_version = phpversion();
$php_ok = version_compare($php_version, '8.0', '>=');
$tests[] = [
    'category' => 'PHP',
    'label'    => 'Version PHP',
    'status'   => $php_ok ? 'ok' : 'warn',
    'value'    => $php_version,
    'message'  => $php_ok ? 'Version supportée (≥ 8.0)' : 'Mise à jour recommandée vers PHP 8.0+',
];

// 2. Extensions requises
$extensions_requises = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'fileinfo', 'session'];
foreach ($extensions_requises as $ext) {
    $loaded = extension_loaded($ext);
    $tests[] = [
        'category' => 'PHP',
        'label'    => "Extension $ext",
        'status'   => $loaded ? 'ok' : 'error',
        'value'    => $loaded ? 'Chargée' : 'Manquante',
        'message'  => $loaded ? '' : "Installez l'extension $ext",
    ];
}

// 3. Configuration PHP sécurité
$config_checks = [
    ['display_errors',          '0',    'Off recommandé en production',     'warn'],
    ['expose_php',              '0',    'Masquer la version PHP aux clients','warn'],
    ['session.cookie_httponly', '1',    'Cookie HttpOnly requis',            'error'],
    ['session.use_strict_mode', '1',    'Mode strict de session requis',     'warn'],
];
foreach ($config_checks as [$key, $expected, $msg, $fail_level]) {
    $current = ini_get($key);
    $ok = ($current == $expected || ($expected === '1' && filter_var($current, FILTER_VALIDATE_BOOLEAN)));
    $tests[] = [
        'category' => 'PHP Config',
        'label'    => "php.ini: $key",
        'status'   => $ok ? 'ok' : $fail_level,
        'value'    => $current ?: '(non défini)',
        'message'  => $ok ? '' : $msg,
    ];
}

// 4. Connexion base de données
try {
    $test_stmt = $pdo->query("SELECT 1");
    $tests[] = [
        'category' => 'Base de données',
        'label'    => 'Connexion PDO MySQL',
        'status'   => 'ok',
        'value'    => 'Connectée',
        'message'  => '',
    ];

    // Version MySQL
    $mysql_version = $pdo->query("SELECT VERSION()")->fetchColumn();
    $mysql_ok = version_compare(explode('-', $mysql_version)[0], '5.7', '>=');
    $tests[] = [
        'category' => 'Base de données',
        'label'    => 'Version MySQL/MariaDB',
        'status'   => $mysql_ok ? 'ok' : 'warn',
        'value'    => $mysql_version,
        'message'  => $mysql_ok ? '' : 'Version ancienne, mise à jour recommandée',
    ];

    // Tables requises
    $tables_requises = ['users', 'citoyens', 'demandes', 'messages', 'infos_mairie', 'settings', 'login_attempts', 'activity_logs'];
    $existing = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables_requises as $table) {
        $exists = in_array($table, $existing);
        $tests[] = [
            'category' => 'Base de données',
            'label'    => "Table: $table",
            'status'   => $exists ? 'ok' : 'warn',
            'value'    => $exists ? 'Présente' : 'Absente',
            'message'  => $exists ? '' : "Exécutez database.sql pour créer cette table",
        ];
    }

    // Espace disque table
    $size_stmt = $pdo->query("
        SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
    ");
    $db_size = round((float)$size_stmt->fetchColumn(), 2);
    $tests[] = [
        'category' => 'Base de données',
        'label'    => 'Taille de la BDD',
        'status'   => 'ok',
        'value'    => $db_size . ' Mo',
        'message'  => '',
    ];

} catch (PDOException $e) {
    $tests[] = [
        'category' => 'Base de données',
        'label'    => 'Connexion PDO MySQL',
        'status'   => 'error',
        'value'    => 'Échec',
        'message'  => 'Erreur : ' . $e->getMessage(),
    ];
}

// 5. Permissions des dossiers
$dirs = [
    ROOT_PATH . '/uploads/documents' => 'Dossier uploads/documents (écriture requise)',
    ROOT_PATH . '/logs'              => 'Dossier logs (écriture requise)',
    ROOT_PATH . '/config'            => 'Dossier config (lecture requise)',
];
foreach ($dirs as $dir => $label) {
    $exists   = is_dir($dir);
    $writable = $exists && is_writable($dir);
    $readable = $exists && is_readable($dir);

    if (str_contains($dir, 'logs') || str_contains($dir, 'uploads')) {
        $ok = $writable;
        $status = !$exists ? 'error' : ($writable ? 'ok' : 'error');
        $msg    = !$exists ? 'Dossier manquant — créez-le' : ($writable ? '' : 'Permissions insuffisantes — chmod 0750');
    } else {
        $ok = $readable;
        $status = !$exists ? 'warn' : ($readable ? 'ok' : 'warn');
        $msg    = !$exists ? 'Dossier absent' : ($readable ? '' : 'Non lisible');
    }

    $tests[] = [
        'category' => 'Système de fichiers',
        'label'    => $label,
        'status'   => $status,
        'value'    => !$exists ? 'Absent' : ($writable ? 'Lecture + Écriture' : ($readable ? 'Lecture seule' : 'Inaccessible')),
        'message'  => $msg,
    ];
}

// 6. Fichiers sensibles exposés
$sensitive = [
    ROOT_PATH . '/config/settings.json' => 'config/settings.json',
    ROOT_PATH . '/.env'                 => '.env',
    ROOT_PATH . '/database.sql'         => 'database.sql (accessible web ?)',
];
foreach ($sensitive as $path => $name) {
    if (file_exists($path)) {
        $tests[] = [
            'category' => 'Sécurité fichiers',
            'label'    => $name,
            'status'   => 'warn',
            'value'    => 'Présent',
            'message'  => 'Vérifiez que ce fichier n\'est pas accessible via l\'URL publique — ajoutez-le au .htaccess',
        ];
    }
}

// 7. Session
$tests[] = [
    'category' => 'Session',
    'label'    => 'Session active',
    'status'   => session_status() === PHP_SESSION_ACTIVE ? 'ok' : 'error',
    'value'    => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive',
    'message'  => '',
];
$tests[] = [
    'category' => 'Session',
    'label'    => 'ID de session',
    'status'   => 'ok',
    'value'    => mb_substr(session_id(), 0, 8) . '…',
    'message'  => '',
];

// 8. SMTP (juste vérification de config)
try {
    $settings = loadSettings();
    $smtp_configured = !empty($settings['smtp_host']) && !empty($settings['smtp_user']);
    $tests[] = [
        'category' => 'Email (SMTP)',
        'label'    => 'Configuration SMTP',
        'status'   => $smtp_configured ? 'ok' : 'warn',
        'value'    => $smtp_configured ? $settings['smtp_host'] . ':' . $settings['smtp_port'] : 'Non configuré',
        'message'  => $smtp_configured ? '' : 'Configurez le SMTP dans Paramètres pour les emails automatiques',
    ];
} catch (Exception $e) {}

// 9. Espace disque
$disk_free  = disk_free_space(ROOT_PATH);
$disk_total = disk_total_space(ROOT_PATH);
$disk_pct   = $disk_total > 0 ? round((1 - $disk_free / $disk_total) * 100) : 0;
$tests[] = [
    'category' => 'Système',
    'label'    => 'Espace disque disponible',
    'status'   => $disk_pct > 90 ? 'error' : ($disk_pct > 75 ? 'warn' : 'ok'),
    'value'    => round($disk_free / 1024 / 1024) . ' Mo libres (' . $disk_pct . '% utilisé)',
    'message'  => $disk_pct > 90 ? 'Espace critique — libérez de l\'espace' : '',
];

// 10. Mémoire PHP
$mem_limit = ini_get('memory_limit');
$tests[] = [
    'category' => 'Système',
    'label'    => 'Limite mémoire PHP',
    'status'   => 'ok',
    'value'    => $mem_limit,
    'message'  => '',
];

// ─────────────────────────────────────────────
// Compteurs résumé
// ─────────────────────────────────────────────
$counts = ['ok' => 0, 'warn' => 0, 'error' => 0];
foreach ($tests as $t) $counts[$t['status']]++;

$categories = array_unique(array_column($tests, 'category'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic système - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height:100vh; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background .3s; }
        .sidebar a:hover { background:rgba(255,255,255,.1); }
        .main-content { padding:20px; background:#f8f9fa; min-height:100vh; }
        .status-ok    { color:#198754; }
        .status-warn  { color:#fd7e14; }
        .status-error { color:#dc3545; }
        .badge-ok    { background:#d1e7dd; color:#0f5132; }
        .badge-warn  { background:#fff3cd; color:#664d03; }
        .badge-error { background:#f8d7da; color:#842029; }
        .test-row:hover { background:#f0f4ff; }
        .category-header { background:#e9ecef; font-weight:600; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-stethoscope me-2 text-primary"></i>Diagnostic système</h3>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
                    <a href="diagnostic.php" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-1"></i>Relancer
                    </a>
                </div>
            </div>

            <!-- Résumé -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <div class="display-4 fw-bold text-success"><?= $counts['ok'] ?></div>
                            <div class="text-muted">Tests réussis</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <div class="display-4 fw-bold text-warning"><?= $counts['warn'] ?></div>
                            <div class="text-muted">Avertissements</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <div class="display-4 fw-bold text-danger"><?= $counts['error'] ?></div>
                            <div class="text-muted">Erreurs critiques</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($counts['error'] > 0): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong><?= $counts['error'] ?> erreur(s) critique(s) détectée(s)</strong> — Corrigez-les avant de mettre en production.
            </div>
            <?php elseif ($counts['warn'] > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>
                Aucune erreur critique, mais <strong><?= $counts['warn'] ?> point(s)</strong> mérite(nt) votre attention.
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                Tous les tests ont réussi — le serveur est correctement configuré.
            </div>
            <?php endif; ?>

            <!-- Tableau de tests par catégorie -->
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="20%">Catégorie</th>
                                <th width="30%">Test</th>
                                <th width="10%">Statut</th>
                                <th width="20%">Valeur</th>
                                <th>Détail</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $prev_cat = null;
                        foreach ($tests as $t):
                            if ($t['category'] !== $prev_cat):
                                $prev_cat = $t['category'];
                        ?>
                        <tr class="category-header">
                            <td colspan="5" class="py-2 ps-3">
                                <i class="fas fa-<?= match($t['category']) {
                                    'PHP'             => 'code',
                                    'PHP Config'      => 'sliders-h',
                                    'Base de données' => 'database',
                                    'Système de fichiers' => 'folder',
                                    'Sécurité fichiers'   => 'lock',
                                    'Session'         => 'key',
                                    'Email (SMTP)'    => 'envelope',
                                    'Système'         => 'server',
                                    default           => 'circle'
                                } ?> me-2"></i><?= htmlspecialchars($t['category'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr class="test-row">
                            <td></td>
                            <td><?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($t['status'] === 'ok'): ?>
                                    <span class="badge badge-ok"><i class="fas fa-check me-1"></i>OK</span>
                                <?php elseif ($t['status'] === 'warn'): ?>
                                    <span class="badge badge-warn"><i class="fas fa-exclamation-triangle me-1"></i>Attention</span>
                                <?php else: ?>
                                    <span class="badge badge-error"><i class="fas fa-times me-1"></i>Erreur</span>
                                <?php endif; ?>
                            </td>
                            <td class="status-<?= $t['status'] ?>">
                                <code><?= htmlspecialchars($t['value'], ENT_QUOTES, 'UTF-8') ?></code>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($t['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-muted mt-3 small">
                <i class="fas fa-clock me-1"></i>Diagnostic généré le <?= date('d/m/Y à H:i:s') ?>
                — Serveur <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'inconnu', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
