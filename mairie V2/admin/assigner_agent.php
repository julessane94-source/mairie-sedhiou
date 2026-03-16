<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id_demande = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_demande <= 0) {
    redirect('demandes.php');
    exit();
}

$message      = '';
$message_type = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Requête invalide (jeton CSRF incorrect).";
    } else {
        $id_agent = (int)$_POST['user_id'];
        if ($id_agent > 0) {
            $stmt = $pdo->prepare("UPDATE demandes SET agent_id = ? WHERE id = ?");
            if ($stmt->execute([$id_agent, $id_demande])) {
                $_SESSION['message'] = "Agent assigné avec succès.";
                redirect('demandes.php');
                exit();
            } else {
                $message = "Erreur lors de l'assignation.";
            }
        } else {
            $message = "Veuillez sélectionner un agent valide.";
        }
    }
}

$agents = $pdo->query("SELECT id, nom, prenom FROM users WHERE role = 'agent' ORDER BY nom ASC")->fetchAll();

$stmt = $pdo->prepare("SELECT d.*, u.nom AS citoyen_nom, u.prenom AS citoyen_prenom
                        FROM demandes d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();
if (!$demande) {
    redirect('demandes.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigner un agent | Mairie de DIENDE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="card shadow" style="max-width:450px;width:100%;">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Assigner un agent — Demande #<?= $id_demande ?></h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            <strong>Citoyen :</strong> <?= htmlspecialchars($demande['citoyen_nom'] . ' ' . $demande['citoyen_prenom']) ?><br>
            <strong>Type :</strong> <?= htmlspecialchars($demande['type_demande'] ?? '') ?><br>
            <strong>Statut :</strong> <?= htmlspecialchars($demande['statut'] ?? '') ?>
        </p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="mb-3">
                <label class="form-label fw-bold">Sélectionner l'agent</label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Choisir un agent --</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= (int)$agent['id'] ?>"
                            <?= ($demande['agent_id'] ?? 0) == $agent['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(strtoupper($agent['nom']) . ' ' . $agent['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Confirmer l'assignation
                </button>
                <a href="demandes.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
