<?php
require_once 'config.php';

$structure_id = $_GET['structure'] ?? 0;
if (!$structure_id) {
    header('Location: index.php');
    exit();
}

// Récupérer la structure
$stmt = $pdo->prepare("SELECT * FROM structures WHERE id = ?");
$stmt->execute([$structure_id]);
$structure = $stmt->fetch();

if (!$structure) {
    header('Location: index.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
    $file = $_FILES['fichier'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, ['xlsx', 'xls', 'csv'])) {
            // Traitement du fichier
            require_once 'vendor/autoload.php';
            
            try {
                $import_file = UPLOAD_PATH . '/temp_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $import_file);
                
                if ($ext == 'csv') {
                    $data = array_map('str_getcsv', file($import_file));
                    $headers = array_shift($data);
                } else {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($import_file);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $data = $worksheet->toArray();
                    $headers = array_shift($data);
                }
                
                $total = count($data);
                $succes = 0;
                $erreurs = 0;
                
                foreach ($data as $row) {
                    if (empty(array_filter($row))) continue;
                    
                    try {
                        $numero = generateNumeroMembre($structure_id);
                        
                        $stmt = $pdo->prepare("
                            INSERT INTO membres (
                                structure_id, numero_membre, nom, prenom, 
                                date_naissance, lieu_naissance, adresse, 
                                telephone, email, date_adhesion, date_expiration
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $structure_id,
                            $numero,
                            $row[0] ?? '', // nom
                            $row[1] ?? '', // prenom
                            $row[2] ?? null, // date naissance
                            $row[3] ?? '', // lieu naissance
                            $row[4] ?? '', // adresse
                            $row[5] ?? '', // telephone
                            $row[6] ?? '', // email
                            $row[7] ?? date('Y-m-d'), // date adhesion
                            $row[8] ?? date('Y-m-d', strtotime('+1 year')) // date expiration
                        ]);
                        
                        $succes++;
                    } catch (Exception $e) {
                        $erreurs++;
                    }
                }
                
                // Journaliser l'import
                $stmt = $pdo->prepare("
                    INSERT INTO imports (structure_id, fichier, total_lignes, succes, erreurs) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$structure_id, $file['name'], $total, $succes, $erreurs]);
                
                $message = "Import terminé : $succes succès, $erreurs erreurs sur $total lignes";
                
                // Nettoyer
                unlink($import_file);
                
            } catch (Exception $e) {
                $error = "Erreur lors de l'import : " . $e->getMessage();
            }
        } else {
            $error = "Format de fichier non supporté. Utilisez Excel (xlsx, xls) ou CSV.";
        }
    } else {
        $error = "Erreur lors de l'upload du fichier";
    }
}

// Récupérer les derniers imports
$stmt = $pdo->prepare("SELECT * FROM imports WHERE structure_id = ? ORDER BY date_import DESC LIMIT 10");
$stmt->execute([$structure_id]);
$imports = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import des membres - <?= htmlspecialchars($structure['nom']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .header {
            background: <?= $structure['couleur_principale'] ?>;
            color: <?= getContrastColor($structure['couleur_principale']) ?>;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .import-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .template-example {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid <?= $structure['couleur_principale'] ?>;
        }
        
        table {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-upload me-2"></i>Import des membres</h2>
                    <p class="mb-0 opacity-75"><?= htmlspecialchars($structure['nom']) ?></p>
                </div>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Formulaire d'import -->
                <div class="import-card">
                    <h4 class="mb-4"><i class="fas fa-file-excel me-2" style="color: <?= $structure['couleur_principale'] ?>"></i>Importer un fichier</h4>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Fichier Excel ou CSV</label>
                            <input type="file" class="form-control" name="fichier" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Formats acceptés : XLSX, XLS, CSV</small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="preview" checked>
                            <label class="form-check-label" for="preview">Aperçu avant import</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Importer
                        </button>
                    </form>
                </div>

                <!-- Structure du fichier -->
                <div class="import-card">
                    <h5 class="mb-3">Structure du fichier attendue</h5>
                    
                    <div class="template-example">
                        <p class="mb-2"><strong>Ordre des colonnes :</strong></p>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Colonne</th>
                                    <th>Champ</th>
                                    <th>Exemple</th>
                                    <th>Obligatoire</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>A</td><td>Nom</td><td>DUPONT</td><td>✅</td></tr>
                                <tr><td>B</td><td>Prénom</td><td>Jean</td><td>✅</td></tr>
                                <tr><td>C</td><td>Date naissance</td><td>1990-01-15</td><td>❌</td></tr>
                                <tr><td>D</td><td>Lieu naissance</td><td>Paris</td><td>❌</td></tr>
                                <tr><td>E</td><td>Adresse</td><td>10 rue de la Paix</td><td>❌</td></tr>
                                <tr><td>F</td><td>Téléphone</td><td>0123456789</td><td>❌</td></tr>
                                <tr><td>G</td><td>Email</td><td>jean@email.com</td><td>❌</td></tr>
                                <tr><td>H</td><td>Date adhésion</td><td>2024-01-01</td><td>❌</td></tr>
                                <tr><td>I</td><td>Date expiration</td><td>2025-01-01</td><td>❌</td></tr>
                            </tbody>
                        </table>
                        
                        <p class="mb-0 mt-3">
                            <i class="fas fa-download me-2"></i>
                            <a href="template_membres.xlsx" class="btn btn-sm btn-outline-primary">
                                Télécharger le modèle Excel
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Historique des imports -->
                <div class="import-card">
                    <h5 class="mb-3"><i class="fas fa-history me-2"></i>Derniers imports</h5>
                    
                    <?php if (empty($imports)): ?>
                        <p class="text-muted text-center py-3">Aucun import pour le moment</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($imports as $imp): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= basename($imp['fichier']) ?></strong>
                                        <small><?= date('d/m/Y', strtotime($imp['date_import'])) ?></small>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-success"><?= $imp['succes'] ?> succès</span>
                                        <?php if ($imp['erreurs'] > 0): ?>
                                            <span class="badge bg-danger"><?= $imp['erreurs'] ?> erreurs</span>
                                        <?php endif; ?>
                                        <span class="badge bg-secondary">Total: <?= $imp['total_lignes'] ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Statistiques -->
                <div class="import-card">
                    <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Statistiques</h5>
                    
                    <?php
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM membres WHERE structure_id = ?");
                    $stmt->execute([$structure_id]);
                    $total_membres = $stmt->fetchColumn();
                    
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM membres WHERE structure_id = ? AND statut = 'actif'");
                    $stmt->execute([$structure_id]);
                    $actifs = $stmt->fetchColumn();
                    ?>
                    
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            Total membres
                            <span class="badge bg-primary"><?= $total_membres ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Membres actifs
                            <span class="badge bg-success"><?= $actifs ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Taux d'activité
                            <span class="badge bg-info">
                                <?= $total_membres > 0 ? round(($actifs/$total_membres)*100) : 0 ?>%
                            </span>
                        </li>
                    </ul>
                    
                    <div class="mt-3">
                        <a href="generate.php?structure=<?= $structure_id ?>" class="btn btn-success w-100">
                            <i class="fas fa-id-card me-2"></i>Générer les cartes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>