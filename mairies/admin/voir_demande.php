<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

$id_demande = $_GET['id'] ?? 0;

// Récupérer la demande
$stmt = $pdo->prepare("
    SELECT d.*, 
           c.numero_citoyen, 
           c.adresse,
           c.date_naissance,
           c.lieu_naissance,
           u.nom as citoyen_nom, 
           u.prenom as citoyen_prenom,
           u.email as citoyen_email,
           u.telephone as citoyen_telephone,
           ag.nom as agent_nom,
           ag.prenom as agent_prenom,
           ag.email as agent_email
    FROM demandes d
    JOIN citoyens c ON d.citoyen_id = c.id
    JOIN users u ON c.user_id = u.id
    LEFT JOIN users ag ON d.agent_id = ag.id
    WHERE d.id = ?
");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    header('Location: demandes.php');
    exit();
}

// Assigner un agent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assigner'])) {
    $agent_id = $_POST['agent_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE demandes SET agent_id = ?, statut = 'en_cours' WHERE id = ?");
    $stmt->execute([$agent_id, $id_demande]);
    
    // Notifier l'agent
    $stmt = $pdo->prepare("
        INSERT INTO messages (expediteur_id, destinataire_id, sujet, contenu) 
        VALUES (?, ?, ?, ?)
    ");
    $sujet = "Nouvelle demande assignée";
    $contenu = "Une demande (N° " . $demande['numero_demande'] . ") vous a été assignée.";
    $stmt->execute([$_SESSION['user_id'], $agent_id, $sujet, $contenu]);
    
    $_SESSION['success'] = "Demande assignée avec succès";
    header('Location: voir_demande.php?id=' . $id_demande);
    exit();
}

// Récupérer la liste des agents
$agents = $pdo->query("SELECT id, nom, prenom, email FROM users WHERE role = 'agent' AND actif = TRUE")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la demande - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-label {
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Détail de la demande</h3>
                    <div>
                        <a href="demandes.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                        <a href="export.php?type=demande&id=<?= $id_demande ?>" class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Exporter
                        </a>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <!-- Informations demande -->
                <div class="info-section">
                    <h5 class="mb-3">Informations de la demande</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p><span class="info-label">N° Demande :</span> <?= htmlspecialchars($demande['numero_demande']) ?></p>
                            <p><span class="info-label">Type :</span> <?= htmlspecialchars($demande['type_demande']) ?></p>
                            <p><span class="info-label">Date :</span> <?= date('d/m/Y H:i', strtotime($demande['date_demande'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><span class="info-label">Statut :</span> 
                                <span class="badge bg-<?= $demande['statut'] == 'en_attente' ? 'warning' : ($demande['statut'] == 'en_cours' ? 'info' : 'success') ?>">
                                    <?= $demande['statut'] ?>
                                </span>
                            </p>
                            <p><span class="info-label">Agent assigné :</span> 
                                <?= $demande['agent_nom'] ? htmlspecialchars($demande['agent_prenom'] . ' ' . $demande['agent_nom']) : 'Non assigné' ?>
                            </p>
                            <?php if ($demande['date_traitement']): ?>
                            <p><span class="info-label">Date traitement :</span> <?= date('d/m/Y H:i', strtotime($demande['date_traitement'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Informations citoyen -->
                <div class="info-section">
                    <h5 class="mb-3">Informations du citoyen</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p><span class="info-label">Nom complet :</span> <?= htmlspecialchars($demande['citoyen_prenom'] . ' ' . $demande['citoyen_nom']) ?></p>
                            <p><span class="info-label">N° Citoyen :</span> <?= htmlspecialchars($demande['numero_citoyen']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><span class="info-label">Email :</span> <?= htmlspecialchars($demande['citoyen_email']) ?></p>
                            <p><span class="info-label">Téléphone :</span> <?= htmlspecialchars($demande['citoyen_telephone']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><span class="info-label">Adresse :</span> <?= htmlspecialchars($demande['adresse']) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Commentaire du citoyen -->
                <?php if ($demande['commentaire']): ?>
                <div class="info-section">
                    <h5 class="mb-3">Commentaire du citoyen</h5>
                    <p><?= nl2br(htmlspecialchars($demande['commentaire'])) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Réponse de l'agent -->
                <?php if ($demande['commentaire_reponse']): ?>
                <div class="info-section">
                    <h5 class="mb-3">Réponse de l'agent</h5>
                    <p><?= nl2br(htmlspecialchars($demande['commentaire_reponse'])) ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Fichier joint -->
                <?php if ($demande['fichier_joint']): ?>
                <div class="info-section">
                    <h5 class="mb-3">Pièce jointe</h5>
                    <a href="../uploads/<?= htmlspecialchars($demande['fichier_joint']) ?>" class="btn btn-info" target="_blank">
                        <i class="fas fa-download me-2"></i>Télécharger la pièce jointe
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Actions admin -->
                <?php if (!$demande['agent_id']): ?>
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0">Assigner un agent</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select" name="agent_id" required>
                                        <option value="">Sélectionner un agent...</option>
                                        <?php foreach($agents as $agent): ?>
                                        <option value="<?= $agent['id'] ?>">
                                            <?= htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) ?> (<?= $agent['email'] ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="assigner" class="btn btn-primary">
                                        <i class="fas fa-user-tag me-2"></i>Assigner
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>