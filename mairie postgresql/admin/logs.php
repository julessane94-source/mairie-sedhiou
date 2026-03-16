<?php
require_once '../config.php';
require_once '../security/logger.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$logger = new Logger($pdo);

$level  = $_GET['level']   ?? '';
$userId = (int)($_GET['uid'] ?? 0);
$limit  = min((int)($_GET['limit'] ?? 100), 500);

$valid_levels = ['', 'INFO', 'WARNING', 'ERROR', 'AUDIT', 'SECURITY'];
if (!in_array($level, $valid_levels, true)) $level = '';

$logs = $logger->getRecent($limit, $level, $userId);

// Compteurs par niveau
$counts_stmt = $pdo->query("SELECT level, COUNT(*) AS cnt FROM activity_logs GROUP BY level ORDER BY cnt DESC");
$level_counts = [];
foreach ($counts_stmt->fetchAll() as $row) $level_counts[$row['level']] = $row['cnt'];

$level_colors = [
    'INFO'     => 'secondary',
    'WARNING'  => 'warning',
    'ERROR'    => 'danger',
    'AUDIT'    => 'primary',
    'SECURITY' => 'dark',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journaux d'activité - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height:100vh; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background .3s; }
        .sidebar a:hover { background:rgba(255,255,255,.1); }
        .main-content { padding:20px; background:#f8f9fa; min-height:100vh; }
        .log-row td { font-family:monospace; font-size:.85rem; }
        .log-row:hover { background:#f0f4ff; }
        .detail-cell { max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-clipboard-list me-2 text-primary"></i>Journaux d'activité</h3>
                <a href="diagnostic.php" class="btn btn-outline-secondary">
                    <i class="fas fa-stethoscope me-1"></i>Diagnostic système
                </a>
            </div>

            <!-- Compteurs par niveau -->
            <div class="row mb-4">
                <?php
                $level_icons = ['SECURITY'=>'shield-alt','AUDIT'=>'user-check','ERROR'=>'times-circle','WARNING'=>'exclamation-triangle','INFO'=>'info-circle'];
                foreach ($level_counts as $lvl => $cnt):
                    $col = $level_colors[$lvl] ?? 'secondary';
                ?>
                <div class="col">
                    <a href="?level=<?= urlencode($lvl) ?>" class="card text-decoration-none border-<?= $col ?>">
                        <div class="card-body text-center py-2">
                            <i class="fas fa-<?= $level_icons[$lvl] ?? 'circle' ?> text-<?= $col ?>"></i>
                            <div class="fw-bold fs-5 text-<?= $col ?>"><?= number_format($cnt) ?></div>
                            <div class="text-muted small"><?= $lvl ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filtres -->
            <form method="GET" class="card card-body mb-3 d-flex flex-row gap-3 align-items-end">
                <div>
                    <label class="form-label mb-1">Niveau</label>
                    <select name="level" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <?php foreach (['SECURITY','AUDIT','ERROR','WARNING','INFO'] as $l): ?>
                        <option value="<?= $l ?>" <?= $level === $l ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1">ID Utilisateur</label>
                    <input type="number" name="uid" class="form-control form-control-sm" value="<?= $userId ?: '' ?>" placeholder="tous">
                </div>
                <div>
                    <label class="form-label mb-1">Lignes max</label>
                    <select name="limit" class="form-select form-select-sm">
                        <?php foreach ([50, 100, 200, 500] as $n): ?>
                        <option value="<?= $n ?>" <?= $limit === $n ? 'selected' : '' ?>><?= $n ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                <a href="logs.php" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
            </form>

            <!-- Tableau des logs -->
            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Aucune entrée de journal pour ces critères.</p>
                        </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Niveau</th>
                                    <th>Utilisateur</th>
                                    <th>IP</th>
                                    <th>Action</th>
                                    <th>Détail</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr class="log-row" title="<?= htmlspecialchars($log['details'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <td class="text-nowrap"><?= date('d/m H:i:s', strtotime($log['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $level_colors[$log['level']] ?? 'secondary' ?>">
                                        <?= $log['level'] ?>
                                    </span>
                                </td>
                                <td><?= $log['user_id'] ? '#' . $log['user_id'] : '<em class="text-muted">anon</em>' ?></td>
                                <td><?= htmlspecialchars($log['ip'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="detail-cell text-muted"><?= htmlspecialchars($log['details'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <p class="text-muted small mt-2">
                <?= count($logs) ?> entrée(s) affichée(s). Survolez une ligne pour voir le détail complet.
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
