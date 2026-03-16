<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    header('Location: ../login.php');
    exit();
}

// Chemin du fichier de configuration
$config_file = dirname(__DIR__) . '/config/settings.json';

// Récupérer les paramètres actuels
if (file_exists($config_file)) {
    $settings = json_decode(file_get_contents($config_file), true);
} else {
    // Paramètres par défaut
    $settings = [
        'site_name' => 'Mairie Services',
        'email_contact' => 'contact@mairie.fr',
        'telephone' => '01 23 45 67 89',
        'adresse' => 'Place de la Mairie, 75000 Paris',
        'horaires' => 'Lundi-Vendredi: 8h-17h, Samedi: 9h-12h',
        'max_file_size' => 5,
        'allowed_file_types' => 'pdf,jpg,jpeg,png',
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'maintenance_mode' => false,
        'registration_open' => true
    ];
}

$message = '';
$message_type = '';

// Mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        // Récupérer et valider les données
        $new_settings = [
            'site_name' => trim($_POST['site_name'] ?? $settings['site_name']),
            'email_contact' => trim($_POST['email_contact'] ?? $settings['email_contact']),
            'telephone' => trim($_POST['telephone'] ?? $settings['telephone']),
            'adresse' => trim($_POST['adresse'] ?? $settings['adresse']),
            'horaires' => trim($_POST['horaires'] ?? $settings['horaires']),
            'max_file_size' => intval($_POST['max_file_size'] ?? $settings['max_file_size']),
            'allowed_file_types' => trim($_POST['allowed_file_types'] ?? $settings['allowed_file_types']),
            'smtp_host' => trim($_POST['smtp_host'] ?? ''),
            'smtp_port' => intval($_POST['smtp_port'] ?? 587),
            'smtp_user' => trim($_POST['smtp_user'] ?? ''),
            'smtp_pass' => trim($_POST['smtp_pass'] ?? ''),
            'maintenance_mode' => isset($_POST['maintenance_mode']),
            'registration_open' => isset($_POST['registration_open'])
        ];
        
        // Validation
        $errors = [];
        
        if (empty($new_settings['site_name'])) {
            $errors[] = "Le nom du site est requis";
        }
        
        if (!filter_var($new_settings['email_contact'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email de contact n'est pas valide";
        }
        
        if ($new_settings['max_file_size'] < 1 || $new_settings['max_file_size'] > 50) {
            $errors[] = "La taille maximale des fichiers doit être entre 1 et 50 Mo";
        }
        
        if (empty($errors)) {
            // Sauvegarder dans le fichier JSON
            $json_data = json_encode($new_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if (file_put_contents($config_file, $json_data, LOCK_EX)) {
                $settings = $new_settings;
                $message = "Paramètres enregistrés avec succès !";
                $message_type = 'success';
                
                // Optionnel : Sauvegarder aussi dans la base de données
                try {
                    // Vérifier si la table settings existe
                    $pdo->exec("
                        CREATE TABLE IF NOT EXISTS settings (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            setting_key VARCHAR(100) UNIQUE NOT NULL,
                            setting_value TEXT,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        )
                    ");
                    
                    // Sauvegarder chaque paramètre
                    foreach ($new_settings as $key => $value) {
                        $value = is_bool($value) ? ($value ? '1' : '0') : $value;
                        $stmt = $pdo->prepare("
                            INSERT INTO settings (setting_key, setting_value) 
                            VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE setting_value = ?
                        ");
                        $stmt->execute([$key, $value, $value]);
                    }
                } catch (Exception $e) {
                    // Ignorer les erreurs de base de données
                }
            } else {
                $message = "Erreur lors de l'enregistrement des paramètres. Vérifiez les permissions du dossier config.";
                $message_type = 'danger';
            }
        } else {
            $message = implode("<br>", $errors);
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['test_email'])) {
        // Test de configuration email
        $to = $_POST['test_email_address'] ?? $settings['email_contact'];
        $subject = "Test de configuration email - Mairie";
        $message_email = "Ceci est un email de test pour vérifier la configuration SMTP.";
        $headers = "From: " . $settings['email_contact'];
        
        if (@mail($to, $subject, $message_email, $headers)) {
            $message = "Email de test envoyé avec succès à " . $to;
            $message_type = 'success';
        } else {
            $message = "Erreur lors de l'envoi de l'email de test";
            $message_type = 'danger';
        }
    }
    
    if (isset($_POST['reset_settings'])) {
        // Réinitialiser aux paramètres par défaut
        $default_settings = [
            'site_name' => 'Mairie Services',
            'email_contact' => 'contact@mairie.fr',
            'telephone' => '01 23 45 67 89',
            'adresse' => 'Place de la Mairie, 75000 Paris',
            'horaires' => 'Lundi-Vendredi: 8h-17h, Samedi: 9h-12h',
            'max_file_size' => 5,
            'allowed_file_types' => 'pdf,jpg,jpeg,png',
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_user' => '',
            'smtp_pass' => '',
            'maintenance_mode' => false,
            'registration_open' => true
        ];
        
        file_put_contents($config_file, json_encode($default_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
        $settings = $default_settings;
        $message = "Paramètres réinitialisés aux valeurs par défaut";
        $message_type = 'info';
    }
}

// Récupérer les paramètres depuis la base de données pour les surcharger
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $value = $row['setting_value'];
        if ($value === '1' || $value === '0') {
            $value = $value === '1';
        }
        $settings[$row['setting_key']] = $value;
    }
} catch (Exception $e) {
    // Ignorer si la table n'existe pas
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Administration</title>
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
            transition: all 0.3s;
        }
        
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar a.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid white;
        }
        
        .main-content {
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .settings-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }
        
        .settings-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .settings-body {
            padding: 25px;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box i {
            color: #2196F3;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-4 text-center">
                    <i class="fas fa-user-cog fa-3x mb-3"></i>
                    <h5 class="mb-1"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Admin') ?></h5>
                    <p class="mb-0 small opacity-75">Administrateur</p>
                </div>
                <nav>
                    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
                    <a href="agents.php"><i class="fas fa-users"></i>Gestion Agents</a>
                    <a href="demandes.php"><i class="fas fa-file-alt"></i>Demandes</a>
                    <a href="informations.php"><i class="fas fa-info-circle"></i>Infos Mairie</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i>Messagerie</a>
                    <a href="statistiques.php"><i class="fas fa-chart-bar"></i>Statistiques</a>
                    <a href="parametres.php" class="active"><i class="fas fa-cog"></i>Paramètres</a>
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Déconnexion</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Paramètres de la plateforme</h3>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Info sur les permissions -->
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Fichier de configuration :</strong> 
                    <?php 
                    $config_path = dirname(__DIR__) . '/config/settings.json';
                    if (is_writable($config_path) || !file_exists($config_path)) {
                        echo '<span class="text-success">Le fichier de configuration est accessible en écriture.</span>';
                    } else {
                        echo '<span class="text-danger">Attention : Le fichier de configuration n\'est pas accessible en écriture. Vérifiez les permissions du dossier config/</span>';
                    }
                    ?>
                </div>
                
                <form method="POST" id="settingsForm">
                    <!-- Paramètres généraux -->
                    <div class="settings-card">
                        <div class="settings-header">
                            <h5><i class="fas fa-globe me-2"></i>Paramètres généraux</h5>
                        </div>
                        <div class="settings-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="site_name" class="form-label">Nom du site</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="<?= htmlspecialchars($settings['site_name']) ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email_contact" class="form-label">Email de contact</label>
                                    <input type="email" class="form-control" id="email_contact" name="email_contact" 
                                           value="<?= htmlspecialchars($settings['email_contact']) ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="text" class="form-control" id="telephone" name="telephone" 
                                           value="<?= htmlspecialchars($settings['telephone']) ?>" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" 
                                           value="<?= htmlspecialchars($settings['adresse']) ?>" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="horaires" class="form-label">Horaires d'ouverture</label>
                                    <input type="text" class="form-control" id="horaires" name="horaires" 
                                           value="<?= htmlspecialchars($settings['horaires']) ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paramètres des fichiers -->
                    <div class="settings-card">
                        <div class="settings-header">
                            <h5><i class="fas fa-file me-2"></i>Paramètres des fichiers</h5>
                        </div>
                        <div class="settings-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="max_file_size" class="form-label">Taille maximale des fichiers (Mo)</label>
                                    <input type="number" class="form-control" id="max_file_size" name="max_file_size" 
                                           value="<?= $settings['max_file_size'] ?>" min="1" max="50" required>
                                    <small class="text-muted">De 1 à 50 Mo</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="allowed_file_types" class="form-label">Types de fichiers autorisés</label>
                                    <input type="text" class="form-control" id="allowed_file_types" name="allowed_file_types" 
                                           value="<?= htmlspecialchars($settings['allowed_file_types']) ?>" required>
                                    <small class="text-muted">Extensions séparées par des virgules (ex: pdf,jpg,png)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paramètres email -->
                    <div class="settings-card">
                        <div class="settings-header">
                            <h5><i class="fas fa-envelope me-2"></i>Configuration email (SMTP)</h5>
                        </div>
                        <div class="settings-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_host" class="form-label">Serveur SMTP</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                           value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_port" class="form-label">Port SMTP</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                           value="<?= $settings['smtp_port'] ?? 587 ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_user" class="form-label">Utilisateur SMTP</label>
                                    <input type="text" class="form-control" id="smtp_user" name="smtp_user" 
                                           value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_pass" class="form-label">Mot de passe SMTP</label>
                                    <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" 
                                           value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Options avancées -->
                    <div class="settings-card">
                        <div class="settings-header">
                            <h5><i class="fas fa-cog me-2"></i>Options avancées</h5>
                        </div>
                        <div class="settings-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenance_mode" 
                                               name="maintenance_mode" <?= ($settings['maintenance_mode'] ?? false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="maintenance_mode">
                                            Mode maintenance
                                        </label>
                                        <small class="text-muted d-block">Active pour empêcher l'accès des utilisateurs</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="registration_open" 
                                               name="registration_open" <?= ($settings['registration_open'] ?? true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="registration_open">
                                            Inscriptions ouvertes
                                        </label>
                                        <small class="text-muted d-block">Permet aux nouveaux citoyens de s'inscrire</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <button type="submit" name="save_settings" class="btn btn-save me-2">
                                <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                            </button>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                                <i class="fas fa-envelope me-2"></i>Tester la configuration email
                            </button>
                        </div>
                        <div>
                            <button type="submit" name="reset_settings" class="btn btn-warning" 
                                    onclick="return confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres ?')">
                                <i class="fas fa-undo me-2"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Informations système -->
                <div class="settings-card mt-4">
                    <div class="settings-header">
                        <h5><i class="fas fa-server me-2"></i>Informations système</h5>
                    </div>
                    <div class="settings-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>PHP Version :</strong> <?= phpversion() ?></p>
                                <p><strong>Serveur :</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></p>
                                <p><strong>Base de données :</strong> MySQL <?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Dossier uploads :</strong> 
                                    <?= is_writable('../uploads') ? '<span class="text-success">Accessible</span>' : '<span class="text-danger">Non accessible</span>' ?>
                                </p>
                                <p><strong>Dossier config :</strong> 
                                    <?= is_writable('../config') ? '<span class="text-success">Accessible</span>' : '<span class="text-danger">Non accessible</span>' ?>
                                </p>
                                <p><strong>Memory limit :</strong> <?= ini_get('memory_limit') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Test Email -->
    <div class="modal fade" id="testEmailModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tester la configuration email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="test_email_address" class="form-label">Adresse email de test</label>
                            <input type="email" class="form-control" id="test_email_address" name="test_email_address" 
                                   value="<?= htmlspecialchars($settings['email_contact']) ?>" required>
                        </div>
                        <p>Un email de test sera envoyé pour vérifier la configuration SMTP.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="test_email" class="btn btn-primary">Envoyer le test</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Dans admin/parametres.php, après les paramètres généraux, ajoutez : -->

<!-- Paramètres des réseaux sociaux -->
<div class="settings-card">
    <div class="settings-header">
        <h5><i class="fas fa-share-alt me-2"></i>Réseaux sociaux</h5>
    </div>
    <div class="settings-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="facebook_url" class="form-label">
                    <i class="fab fa-facebook text-primary me-2"></i>Facebook
                </label>
                <input type="url" class="form-control" id="facebook_url" name="facebook_url" 
                       value="<?= htmlspecialchars($settings['facebook_url'] ?? '') ?>" 
                       placeholder="https://facebook.com/votrepage">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="twitter_url" class="form-label">
                    <i class="fab fa-twitter text-info me-2"></i>Twitter
                </label>
                <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                       value="<?= htmlspecialchars($settings['twitter_url'] ?? '') ?>" 
                       placeholder="https://twitter.com/votrecompte">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="instagram_url" class="form-label">
                    <i class="fab fa-instagram text-danger me-2"></i>Instagram
                </label>
                <input type="url" class="form-control" id="instagram_url" name="instagram_url" 
                       value="<?= htmlspecialchars($settings['instagram_url'] ?? '') ?>" 
                       placeholder="https://instagram.com/votrecompte">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="linkedin_url" class="form-label">
                    <i class="fab fa-linkedin text-primary me-2"></i>LinkedIn
                </label>
                <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                       value="<?= htmlspecialchars($settings['linkedin_url'] ?? '') ?>" 
                       placeholder="https://linkedin.com/company/votrepage">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="youtube_url" class="form-label">
                    <i class="fab fa-youtube text-danger me-2"></i>YouTube
                </label>
                <input type="url" class="form-control" id="youtube_url" name="youtube_url" 
                       value="<?= htmlspecialchars($settings['youtube_url'] ?? '') ?>" 
                       placeholder="https://youtube.com/c/votrechaine">
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="tiktok_url" class="form-label">
                    <i class="fab fa-tiktok text-dark me-2"></i>TikTok
                </label>
                <input type="url" class="form-control" id="tiktok_url" name="tiktok_url" 
                       value="<?= htmlspecialchars($settings['tiktok_url'] ?? '') ?>" 
                       placeholder="https://tiktok.com/@votrecompte">
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            Laissez vide pour ne pas afficher le réseau social correspondant.
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Confirmation avant de quitter avec des modifications non sauvegardées
    let formModified = false;
    
    document.querySelectorAll('#settingsForm input, #settingsForm select').forEach(input => {
        input.addEventListener('change', () => {
            formModified = true;
        });
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (formModified) {
            e.preventDefault();
            e.returnValue = 'Vous avez des modifications non sauvegardées. Êtes-vous sûr de vouloir quitter ?';
        }
    });
    
    document.getElementById('settingsForm').addEventListener('submit', () => {
        formModified = false;
    });
    </script>
</body>
</html>