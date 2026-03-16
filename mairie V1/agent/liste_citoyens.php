<?php
// Inclusion de la connexion (on remonte d'un dossier car on est dans 'agent/')
require_once('../db_connect.php');
include('../navbar.php');

// Requête pour récupérer les citoyens (ajustez les noms de colonnes selon votre table)
try {
    $query = "SELECT * FROM citoyens ORDER BY id DESC";
    $stmt = $pdo->query($query);
    $citoyens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erreur = "Erreur lors de la récupération des données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Citoyens - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2 class="mb-4 text-primary"><i class="bi bi-people-fill"></i> Répertoire des Citoyens</h2>
        
        <?php if (isset($erreur)): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>N° Citoyen</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($citoyens) > 0): ?>
                            <?php foreach ($citoyens as $citoyen): ?>
    <tr>
        <td class="fw-bold text-secondary">
            <?= htmlspecialchars($citoyen['id'] ?? 'N/A') ?>
        </td>
        
        <td>
            <?= htmlspecialchars($citoyen['prenom'] ?? '') ?> 
            <?= htmlspecialchars($citoyen['nom'] ?? '') ?>
        </td>
        
        <td><?= htmlspecialchars($citoyen['email'] ?? 'Non renseigné') ?></td>
        
        <td><?= htmlspecialchars($citoyen['telephone'] ?? 'N/A') ?></td>
        
        <td class="text-center">
            <a href="voir_citoyen.php?id=<?= $citoyen['id'] ?>" class="btn btn-info btn-sm text-white shadow-sm">
                <i class="bi bi-eye"></i> Voir
            </a>
        </td>
    </tr>
<?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Aucun citoyen trouvé dans la base de données.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>