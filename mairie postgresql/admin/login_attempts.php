<?php
require_once '../config.php';
require_once '../security/rate_limiter.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$limiter = new RateLimiter($pdo);

// Débloquer une IP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unblock_ip'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Action non autorisée.";
        redirect('login_attempts.php');
    }
    $ip = trim($_POST['ip'] ?? '');
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        $limiter->clearIp($ip);
        $_SESSION['success'] = "IP $ip débloquée.";
    }
    redirect('login_attempts.php');
}

// Stats des tentatives
$stats_stmt = $pdo->query("
    SELECT
        ip,
        COUNT(*) AS total,
        SUM(success = 0) AS echecs,
        SUM(success = 1) AS succes,
        MAX(attempted_at) AS derniere,
        MIN(attempted_at) AS premiere
    FROM login_attempts
    WHERE attempted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY ip
    ORDER BY echecs DESC
    LIMIT 50
");
$ip_stats = $stats_stmt->fetchAll();

// Historique récent
$history_stmt = $pdo->query("
    SELECT la.*, u.email AS user_email
    FROM login_attempts la
    LEFT JOIN users u ON u.email = la.email
    ORDER BY la.attempted_at DESC
    LIMIT 100
");
$history = $history_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentatives de connexion - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height:100vh; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background .3s; }
        .sidebar a:hover { background:rgba(255,255,255,.1); }
        .main-content { padding:20px; background:#f8f9fa; min-height:100vh; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10 main-content">
            <h3 class="mb-4"><i class="fas fa-shield-alt me-2 text-danger"></i>Tentatives de connexion (24h)</h3>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- IPs suspectes -->
            <div class="card mb-4">
                <div class="card-header fw-bold"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>IPs avec tentatives échouées</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>IP</th>
                                <th>Échecs</th>
                                <th>Succès</th>
                                <th>Première tentative</th>
                                <th>Dernière tentative</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ip_stats as $row):
                            $check = $limiter->check('');
                            $blocked = ($row['echecs'] >= 5);
                        ?>
                        <tr class="<?= $blocked ? 'table-danger' : '' ?>">
                            <td><code><?= htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><span class="badge bg-danger"><?= $row['echecs'] ?></span></td>
                            <td><span class="badge bg-success"><?= $row['succes'] ?></span></td>
                            <td><?= date('d/m H:i', strtotime($row['premiere'])) ?></td>
                            <td><?= date('d/m H:i', strtotime($row['derniere'])) ?></td>
                            <td>
                                <?php if ($blocked): ?>
                                    <span class="badge bg-danger">Bloquée</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Libre</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($blocked): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="ip" value="<?= htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" name="unblock_ip" class="btn btn-sm btn-success">
                                        <i class="fas fa-unlock me-1"></i>Débloquer
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ip_stats)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">Aucune tentative enregistrée dans les dernières 24h</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Historique -->
            <div class="card">
                <div class="card-header fw-bold"><i class="fas fa-history me-2"></i>100 dernières tentatives</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-secondary">
                            <tr><th>Date</th><th>IP</th><th>Email</th><th>Résultat</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr class="<?= $h['success'] ? '' : 'table-warning' ?>">
                            <td><?= date('d/m/Y H:i:s', strtotime($h['attempted_at'])) ?></td>
                            <td><code><?= htmlspecialchars($h['ip'], ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= htmlspecialchars($h['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($h['success']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Succès</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Échec</span>
                                <?php endif; ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
