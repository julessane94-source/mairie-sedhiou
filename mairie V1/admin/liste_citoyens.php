<?php
require_once '../config.php';
require_once '../security/logger.php';

$_logger = new Logger($pdo);

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

$search     = trim($_GET['search'] ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 20;
$offset     = ($page - 1) * $perPage;
$citoyens   = [];
$total      = 0;
$totalPages = 0;

try {
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmtCount = $pdo->prepare("
            SELECT COUNT(*) FROM citoyens c
            JOIN users u ON c.user_id = u.id
            WHERE c.numero_citoyen LIKE ?
               OR u.nom   LIKE ?
               OR u.prenom LIKE ?
               OR u.email  LIKE ?
        ");
        $stmtCount->execute([$like, $like, $like, $like]);
        $total = (int)$stmtCount->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT c.*, u.nom, u.prenom, u.email, u.telephone, u.actif
            FROM citoyens c
            JOIN users u ON c.user_id = u.id
            WHERE c.numero_citoyen LIKE ?
               OR u.nom   LIKE ?
               OR u.prenom LIKE ?
               OR u.email  LIKE ?
            ORDER BY c.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$like, $like, $like, $like, $perPage, $offset]);
    } else {
        $total = (int)$pdo->query("SELECT COUNT(*) FROM citoyens")->fetchColumn();
        $stmt  = $pdo->prepare("
            SELECT c.*, u.nom, u.prenom, u.email, u.telephone, u.actif
            FROM citoyens c
            JOIN users u ON c.user_id = u.id
            ORDER BY c.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
    }
    $citoyens   = $stmt->fetchAll();
    $totalPages = (int)ceil($total / $perPage);
} catch (Exception $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Citoyens - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0"><i class="fas fa-id-card text-primary me-2"></i>Liste des Citoyens</h2>
                    <small class="text-muted">Registre civil — <?= $total ?> citoyen(s) enregistré(s)</small>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Rechercher par N°CIT, nom, prénom, email..."
                                       value="<?= e($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                        </div>
                        <?php if ($search): ?>
                        <div class="col-md-2">
                            <a href="liste_citoyens.php" class="btn btn-outline-secondary w-100">Réinitialiser</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°CIT</th>
                                    <th>Nom complet</th>
                                    <th>Date de naissance</th>
                                    <th>Lieu de naissance</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Adresse</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($citoyens)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                            Aucun citoyen trouvé<?= $search ? " pour « " . e($search) . " »" : "" ?>.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($citoyens as $c): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6 fw-normal font-monospace">
                                                <?= e($c['numero_citoyen'] ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold"><?= e(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? '')) ?></td>
                                        <td>
                                            <?= $c['date_naissance'] ? date('d/m/Y', strtotime($c['date_naissance'])) : '<span class="text-muted">—</span>' ?>
                                        </td>
                                        <td><?= e($c['lieu_naissance'] ?? '—') ?></td>
                                        <td>
                                            <?php if ($c['email']): ?>
                                                <a href="mailto:<?= e($c['email']) ?>" class="text-decoration-none small"><?= e($c['email']) ?></a>
                                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                        </td>
                                        <td><?= e($c['telephone'] ?? '—') ?></td>
                                        <td><span class="small text-muted"><?= e(mb_strimwidth($c['adresse'] ?? '—', 0, 35, '…')) ?></span></td>
                                        <td class="text-center">
                                            <?php if ($c['actif']): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <?= min(($page-1)*$perPage+1, $total) ?> – <?= min($page*$perPage, $total) ?> sur <?= $total ?>
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i></a></li>
                            <?php endif; ?>
                            <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
                                <li class="page-item <?= $i===$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-right"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Format N°CIT :</strong> <code>CIT-AAAAMMJJ-NNNNN</code>
                — date de naissance (AAAAMMJJ) + numéro de registre (5 chiffres).
            </div>

        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
