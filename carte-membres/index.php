<?php
require_once 'config.php';

// Récupérer les structures
$structures = $pdo->query("SELECT * FROM structures ORDER BY date_creation DESC")->fetchAll();

// Traitement création structure
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_structure'])) {
    $nom = $_POST['nom'] ?? '';
    $couleur = $_POST['couleur'] ?? '#3498db';
    $couleur_secondaire = $_POST['couleur_secondaire'] ?? '#2980b9';
    
    // Upload logo
    $logo = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], LOGO_PATH . '/' . $logo);
    }
    
    $stmt = $pdo->prepare("INSERT INTO structures (nom, logo, couleur_principale, couleur_secondaire) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nom, $logo, $couleur, $couleur_secondaire]);
    
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Cartes Membres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .structure-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border: 1px solid #e9ecef;
        }
        
        .structure-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .structure-logo {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e9ecef;
        }
        
        .modal-content {
            border-radius: 15px;
        }
        
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 5px;
            display: inline-block;
            margin-right: 5px;
            border: 1px solid #dee2e6;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header text-center">
        <div class="container">
            <h1 class="display-4"><i class="fas fa-id-card me-3"></i>Générateur de Cartes Membres</h1>
            <p class="lead">Créez des cartes membres professionnelles en quelques clics</p>
        </div>
    </div>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2><i class="fas fa-building me-2"></i>Mes structures</h2>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newStructureModal">
                    <i class="fas fa-plus-circle me-2"></i>Nouvelle structure
                </button>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?= count($structures) ?></div>
                    <div class="text-muted">Structures</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">
                        <?php
                        $total = $pdo->query("SELECT COUNT(*) FROM membres")->fetchColumn();
                        echo $total;
                        ?>
                    </div>
                    <div class="text-muted">Membres</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number">
                        <?php
                        $cartes = glob(CARTE_PATH . '/*.pdf');
                        echo count($cartes);
                        ?>
                    </div>
                    <div class="text-muted">Cartes générées</div>
                </div>
            </div>
        </div>

        <!-- Liste des structures -->
        <div class="row">
            <?php if (empty($structures)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-building fa-4x text-muted mb-3"></i>
                    <h4>Aucune structure</h4>
                    <p class="text-muted">Créez votre première structure pour commencer</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newStructureModal">
                        <i class="fas fa-plus-circle me-2"></i>Créer une structure
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($structures as $s): ?>
                    <div class="col-md-4">
                        <div class="structure-card">
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($s['logo'] && file_exists(LOGO_PATH . '/' . $s['logo'])): ?>
                                    <img src="uploads/logos/<?= $s['logo'] ?>" class="structure-logo me-3">
                                <?php else: ?>
                                    <div class="structure-logo me-3 d-flex align-items-center justify-content-center" 
                                         style="background: <?= $s['couleur_principale'] ?>; color: white;">
                                        <i class="fas fa-building fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($s['nom']) ?></h5>
                                    <small class="text-muted">
                                        <i class="fas fa-users me-1"></i>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM membres WHERE structure_id = ?");
                                        $stmt->execute([$s['id']]);
                                        echo $stmt->fetchColumn() . ' membres';
                                        ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-3">
                                <span class="color-preview" style="background: <?= $s['couleur_principale'] ?>"></span>
                                <span class="color-preview" style="background: <?= $s['couleur_secondaire'] ?>"></span>
                                <small class="text-muted ms-2">Couleurs de la carte</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="import.php?structure=<?= $s['id'] ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-upload me-2"></i>Importer des membres
                                </a>
                                <a href="generate.php?structure=<?= $s['id'] ?>" class="btn btn-success">
                                    <i class="fas fa-id-card me-2"></i>Générer les cartes
                                </a>
                                <button class="btn btn-outline-info" onclick="voirMembres(<?= $s['id'] ?>)">
                                    <i class="fas fa-list me-2"></i>Voir les membres
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Nouvelle Structure -->
    <div class="modal fade" id="newStructureModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle structure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom de la structure</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <small class="text-muted">Format recommandé : PNG avec fond transparent</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Couleur principale</label>
                                <input type="color" class="form-control form-control-color" name="couleur" value="#3498db">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Couleur secondaire</label>
                                <input type="color" class="form-control form-control-color" name="couleur_secondaire" value="#2980b9">
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Les couleurs seront automatiquement adaptées pour un meilleur contraste.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="create_structure" class="btn btn-primary">Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Liste Membres -->
    <div class="modal fade" id="membresModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Liste des membres</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="membresList">
                    <!-- Chargé dynamiquement -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function voirMembres(structureId) {
            fetch('get_membres.php?structure=' + structureId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('membresList').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('membresModal')).show();
                });
        }
    </script>
</body>
</html>