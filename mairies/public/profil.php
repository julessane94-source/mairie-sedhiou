<?php
require_once '../config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Si c'est un citoyen, récupérer les infos supplémentaires
$citoyen = null;
if ($user['role'] == 'public') {
    $stmt = $pdo->prepare("SELECT * FROM citoyens WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $citoyen = $stmt->fetch();
}

// Mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_info'])) {
        $telephone = $_POST['telephone'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        
        // Mise à jour des informations de base
        $stmt = $pdo->prepare("UPDATE users SET telephone = ? WHERE id = ?");
        $stmt->execute([$telephone, $_SESSION['user_id']]);
        
        // Mise à jour des informations citoyen si applicable
        if ($citoyen) {
            $stmt = $pdo->prepare("UPDATE citoyens SET adresse = ? WHERE user_id = ?");
            $stmt->execute([$adresse, $_SESSION['user_id']]);
        }
        
        $_SESSION['success'] = "Profil mis à jour avec succès";
        header('Location: profil.php');
        exit();
    }
    
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Vérifier l'ancien mot de passe
        if (password_verify($old_password, $user['password'])) {
            if ($new_password === $confirm_password && strlen($new_password) >= 6) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $_SESSION['user_id']]);
                $_SESSION['success'] = "Mot de passe modifié avec succès";
            } else {
                $_SESSION['error'] = "Les mots de passe ne correspondent pas ou sont trop courts";
            }
        } else {
            $_SESSION['error'] = "Ancien mot de passe incorrect";
        }
        header('Location: profil.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 20px;
            display: block;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .profile-avatar i {
            font-size: 50px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3">
                    <h4><i class="fas fa-user me-2"></i>Mon Espace</h4>
                    <hr>
                </div>
                <nav>
                    <a href="./dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                    <a href="profil.php"><i class="fas fa-user-cog"></i>Mon profil</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <div class="profile-header text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
                    <p class="mb-0"><?= ucfirst($user['role']) ?></p>
                    <?php if ($citoyen): ?>
                        <p class="mb-0">N° Citoyen: <?= htmlspecialchars($citoyen['numero_citoyen']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Informations personnelles</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="telephone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="telephone" name="telephone" 
                                               value="<?= htmlspecialchars($user['telephone']) ?>">
                                    </div>
                                    
                                    <?php if ($citoyen): ?>
                                    <div class="mb-3">
                                        <label for="adresse" class="form-label">Adresse</label>
                                        <textarea class="form-control" id="adresse" name="adresse" rows="3"><?= htmlspecialchars($citoyen['adresse']) ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Date de naissance</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($citoyen['date_naissance']) ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Lieu de naissance</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($citoyen['lieu_naissance']) ?>" readonly>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <button type="submit" name="update_info" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Mettre à jour
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Changer le mot de passe</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="old_password" class="form-label">Ancien mot de passe</label>
                                        <input type="password" class="form-control" id="old_password" name="old_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" name="change_password" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Changer le mot de passe
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <?php if ($user['role'] == 'public'): ?>
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5>Mes statistiques</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT 
                                        COUNT(*) as total,
                                        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                                        SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
                                        SUM(CASE WHEN statut = 'traite' THEN 1 ELSE 0 END) as traite
                                    FROM demandes 
                                    WHERE citoyen_id = ?
                                ");
                                $stmt->execute([$citoyen['id']]);
                                $stats = $stmt->fetch();
                                ?>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Total demandes
                                        <span class="badge bg-primary"><?= $stats['total'] ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        En attente
                                        <span class="badge bg-warning"><?= $stats['en_attente'] ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        En cours
                                        <span class="badge bg-info"><?= $stats['en_cours'] ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Traitées
                                        <span class="badge bg-success"><?= $stats['traite'] ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>