<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('public')) {
    header('Location: ../login.php');
    exit();
}

// Récupérer le citoyen
$stmt = $pdo->prepare("SELECT * FROM citoyens WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$citoyen = $stmt->fetch();

$type = $_GET['type'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_demande = $_POST['type_demande'] ?? '';
    $commentaire = $_POST['commentaire'] ?? '';
    $numero_demande = generateNumeroDemande();
    
    // Gestion du fichier
    $fichier_joint = '';
    if (isset($_FILES['piece_jointe']) && $_FILES['piece_jointe']['error'] === 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $extension = pathinfo($_FILES['piece_jointe']['name'], PATHINFO_EXTENSION);
        $fichier_joint = $numero_demande . '.' . $extension;
        $upload_file = $upload_dir . $fichier_joint;
        
        if (!move_uploaded_file($_FILES['piece_jointe']['tmp_name'], $upload_file)) {
            $error = "Erreur lors de l'upload du fichier";
        }
    }
    
    if (!$error) {
        $stmt = $pdo->prepare("
            INSERT INTO demandes (numero_demande, citoyen_id, type_demande, commentaire, fichier_joint) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$numero_demande, $citoyen['id'], $type_demande, $commentaire, $fichier_joint])) {
            $success = "Votre demande a été enregistrée avec succès. Numéro de demande : " . $numero_demande;
        } else {
            $error = "Erreur lors de l'enregistrement de la demande";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header i {
            font-size: 50px;
            color: #667eea;
        }
        
        .back-link {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
        }
        
        .back-link:hover {
            color: #f8f9fa;
            text-decoration: underline;
        }
        
        .info-citoyen {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="mb-3">
            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
            </a>
        </div>
        
        <div class="form-card">
            <div class="form-header">
                <i class="fas fa-file-alt mb-3"></i>
                <h2>Nouvelle demande</h2>
                <p class="text-muted">Remplissez le formulaire ci-dessous</p>
            </div>
            
            <div class="info-citoyen">
                <div class="row">
                    <div class="col-md-6">
                        <strong><i class="fas fa-id-card me-2"></i>N° Citoyen:</strong>
                        <?= htmlspecialchars($citoyen['numero_citoyen']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="fas fa-user me-2"></i>Identifiant:</strong>
                        <?= htmlspecialchars($_SESSION['user_nom']) ?>
                    </div>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <div class="text-center mt-3">
                    <a href="mes_demandes.php" class="btn btn-primary">Voir mes demandes</a>
                    <a href="nouvelle_demande.php" class="btn btn-success">Nouvelle demande</a>
                </div>
            <?php else: ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="type_demande" class="form-label">Type de demande</label>
                    <select class="form-select" id="type_demande" name="type_demande" required>
                        <option value="">Sélectionnez un type</option>
                        <option value="extrait_naissance" <?= $type == 'extrait_naissance' ? 'selected' : '' ?>>Extrait de naissance</option>
                        <option value="declaration_naissance" <?= $type == 'declaration_naissance' ? 'selected' : '' ?>>Déclaration de naissance</option>
                        <option value="mariage" <?= $type == 'mariage' ? 'selected' : '' ?>>Certificat de mariage</option>
                        <option value="deces" <?= $type == 'deces' ? 'selected' : '' ?>>Certificat de décès</option>
                        <option value="residence" <?= $type == 'residence' ? 'selected' : '' ?>>Certificat de résidence</option>
                    </select>
                </div>
                
                <!-- Champs dynamiques selon le type -->
                <div id="champs_specifiques">
                    <!-- Les champs seront ajoutés via JavaScript -->
                </div>
                
                <div class="mb-3">
                    <label for="piece_jointe" class="form-label">Pièce jointe (si nécessaire)</label>
                    <input type="file" class="form-control" id="piece_jointe" name="piece_jointe" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Formats acceptés : PDF, JPG, PNG (max 5 Mo)</small>
                </div>
                
                <div class="mb-3">
                    <label for="commentaire" class="form-label">Commentaire / Informations complémentaires</label>
                    <textarea class="form-control" id="commentaire" name="commentaire" rows="4" placeholder="Précisez votre demande..."></textarea>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="confirmation" required>
                    <label class="form-check-label" for="confirmation">
                        Je certifie l'exactitude des informations fournies
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane me-2"></i>Soumettre la demande
                </button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.getElementById('type_demande').addEventListener('change', function() {
        const type = this.value;
        const container = document.getElementById('champs_specifiques');
        
        let html = '';
        
        if (type === 'extrait_naissance') {
            html = `
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-baby me-2"></i>Informations naissance
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nom de l'enfant</label>
                                <input type="text" class="form-control" name="enfant_nom">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Prénom de l'enfant</label>
                                <input type="text" class="form-control" name="enfant_prenom">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date de naissance</label>
                                <input type="date" class="form-control" name="enfant_date_naissance">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Lieu de naissance</label>
                                <input type="text" class="form-control" name="enfant_lieu_naissance">
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else if (type === 'residence') {
            html = `
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <i class="fas fa-home me-2"></i>Informations résidence
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nom du père</label>
                                <input type="text" class="form-control" name="pere_nom">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Prénom du père</label>
                                <input type="text" class="form-control" name="pere_prenom">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Nom de la mère</label>
                                <input type="text" class="form-control" name="mere_nom">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Prénom de la mère</label>
                                <input type="text" class="form-control" name="mere_prenom">
                            </div>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            N'oubliez pas de joindre les pièces d'identité (recto/verso)
                        </div>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
    });
    
    // Déclencher le changement si un type est déjà sélectionné
    window.onload = function() {
        const event = new Event('change');
        document.getElementById('type_demande').dispatchEvent(event);
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>