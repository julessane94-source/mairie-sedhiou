<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('agent')) {
    redirect('../login.php');
}

// Pagination
$par_page   = 12;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $par_page;

// Filtre par type
$type_filtre = trim($_GET['type'] ?? '');

$sql_count = "SELECT COUNT(*) FROM demandes d WHERE d.statut = 'en_attente'";
$sql       = "
    SELECT d.*, c.numero_citoyen,
           u.nom AS citoyen_nom, u.prenom AS citoyen_prenom,
           u.email AS citoyen_email, u.telephone AS citoyen_telephone
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    WHERE d.statut = 'en_attente'
";

$params = [];
if ($type_filtre !== '') {
    $sql_count .= " AND d.type_demande = ?";
    $sql       .= " AND d.type_demande = ?";
    $params[]   = $type_filtre;
}

$sql .= " ORDER BY d.date_demande ASC LIMIT ? OFFSET ?";

// Compter le total
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total  = (int)$stmt_count->fetchColumn();
$pages  = (int)ceil($total / $par_page);

// Récupérer la page
$params[] = $par_page;
$params[] = $offset;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll();

// Types disponibles pour le filtre
$types = $pdo->query("SELECT DISTINCT type_demande FROM demandes WHERE statut='en_attente' ORDER BY type_demande")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes en attente - Agent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .demande-card { transition:transform .3s; border-left:4px solid #ffc107; }
        .demande-card:hover { transform:translateX(5px); box-shadow:0 5px 15px rgba(0,0,0,.1); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Demandes en attente <span class="badge bg-warning text-dark"><?= $total ?></span></h3>

                <!-- Filtre -->
                <form method="GET" class="d-flex gap-2">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les types</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= htmlspecialchars($t, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $type_filtre === $t ? 'selected' : '' ?>>
                                <?= htmlspecialchars(DEMANDE_TYPES[$t] ?? $t, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if (!empty($demandes)): ?>
            <div class="row">
                <?php foreach ($demandes as $demande): ?>
                <div class="col-md-6 mb-4">
                    <div class="card demande-card">
                        <div class="card-header bg-warning text-white d-flex justify-content-between">
                            <strong>N° <?= htmlspecialchars($demande['numero_demande'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></small>
                        </div>
                        <div class="card-body">
                            <h6><?= htmlspecialchars(DEMANDE_TYPES[$demande['type_demande']] ?? $demande['type_demande'], ENT_QUOTES, 'UTF-8') ?></h6>
                            <p>
                                <strong>Citoyen :</strong> <?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom'], ENT_QUOTES, 'UTF-8') ?><br>
                                <strong>N° :</strong> <?= htmlspecialchars($demande['numero_citoyen'], ENT_QUOTES, 'UTF-8') ?><br>
                                <strong>Contact :</strong> <?= htmlspecialchars($demande['citoyen_email'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <?php if ($demande['commentaire']): ?>
                                <p class="text-muted"><small><?= htmlspecialchars(mb_substr($demande['commentaire'], 0, 100), ENT_QUOTES, 'UTF-8') ?>…</small></p>
                            <?php endif; ?>
                            <a href="traiter_demande.php?id=<?= (int)$demande['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-hand-pointer me-1"></i>Prendre en charge
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $p ?>&type=<?= urlencode($type_filtre) ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5>Aucune demande en attente</h5>
                <p>Toutes les demandes ont été traitées.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
