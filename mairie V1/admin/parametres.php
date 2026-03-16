<?php
require_once '../config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../login.php');
}

// Générer CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$config_file  = dirname(__DIR__) . '/config/settings.json';
$message      = '';
$message_type = 'success';

// Charger les paramètres existants
$settings = [];
if (file_exists($config_file) && is_readable($config_file)) {
    $settings = json_decode(file_get_contents($config_file), true) ?: [];
}

$defaults = [
    'site_name'         => 'Mairie Services',
    'email_contact'     => 'contact@mairie.fr',
    'telephone'         => '01 23 45 67 89',
    'adresse'           => 'Place de la Mairie, 75000 Paris',
    'horaires'          => 'Lundi-Vendredi: 8h-17h, Samedi: 9h-12h',
    'max_file_size'     => 5,
    'allowed_file_types'=> 'pdf,jpg,jpeg,png',
    'smtp_host'         => '',
    'smtp_port'         => 587,
    'smtp_user'         => '',
    'smtp_pass'         => '',
    'maintenance_mode'  => false,
    'registration_open' => true,
];
$settings = array_merge($defaults, $settings);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message      = "Requête invalide.";
        $message_type = 'danger';
    } else {
        $errors = [];

        $site_name = trim($_POST['site_name'] ?? '');
        $email     = trim($_POST['email_contact'] ?? '');
        $max_size  = (int) ($_POST['max_file_size'] ?? 5);
        $smtp_port = (int) ($_POST['smtp_port'] ?? 587);

        if (empty($site_name)) {
            $errors[] = "Le nom du site est requis.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email de contact est invalide.";
        }
        if ($max_size < 1 || $max_size > 50) {
            $errors[] = "La taille maximale des fichiers doit être entre 1 et 50 Mo.";
        }

        if (empty($errors)) {
            $new_settings = [
                'site_name'         => $site_name,
                'email_contact'     => $email,
                'telephone'         => trim($_POST['telephone'] ?? ''),
                'adresse'           => trim($_POST['adresse'] ?? ''),
                'horaires'          => trim($_POST['horaires'] ?? ''),
                'max_file_size'     => $max_size,
                'allowed_file_types'=> trim($_POST['allowed_file_types'] ?? 'pdf,jpg,jpeg,png'),
                'smtp_host'         => trim($_POST['smtp_host'] ?? ''),
                'smtp_port'         => $smtp_port,
                'smtp_user'         => trim($_POST['smtp_user'] ?? ''),
                'smtp_pass'         => trim($_POST['smtp_pass'] ?? ''),
                'maintenance_mode'  => isset($_POST['maintenance_mode']),
                'registration_open' => isset($_POST['registration_open']),
            ];

            $dir = dirname($config_file);
            if (!is_dir($dir)) mkdir($dir, 0750, true);

            if (file_put_contents($config_file, json_encode($new_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX)) {
                $settings     = $new_settings;
                $message      = "Paramètres enregistrés avec succès.";
                $message_type = 'success';

                // Synchroniser avec la BDD
                try {
                    $upsert = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                    foreach ($new_settings as $k => $v) {
                        $upsert->execute([$k, is_bool($v) ? ($v ? '1' : '0') : $v]);
                    }
                } catch (Exception $e) {
                    error_log("Sync settings BDD : " . $e->getMessage());
                }
            } else {
                $message      = "Erreur lors de la sauvegarde du fichier.";
                $message_type = 'danger';
            }
        } else {
            $message      = implode('<br>', array_map('htmlspecialchars', $errors));
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Mairie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height:100vh; background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; }
        .sidebar a { color:white; text-decoration:none; padding:15px 20px; display:block; transition:background .3s; }
        .sidebar a:hover { background:rgba(255,255,255,.1); }
        .main-content { padding:20px; background:#f8f9fa; min-height:100vh; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-10 main-content">
            <h3 class="mb-4">Paramètres du site</h3>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST" id="settingsForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                <!-- Informations générales -->
                <div class="card mb-4">
                    <div class="card-header"><h5>Informations générales</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom du site <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="site_name"
                                    value="<?= htmlspecialchars($settings['site_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email de contact <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email_contact"
                                    value="<?= htmlspecialchars($settings['email_contact'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control" name="telephone"
                                    value="<?= htmlspecialchars($settings['telephone'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" class="form-control" name="adresse"
                                    value="<?= htmlspecialchars($settings['adresse'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Horaires d'ouverture</label>
                                <input type="text" class="form-control" name="horaires"
                                    value="<?= htmlspecialchars($settings['horaires'], ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paramètres des fichiers -->
                <div class="card mb-4">
                    <div class="card-header"><h5>Gestion des fichiers</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Taille max (Mo) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="max_file_size"
                                    value="<?= (int)$settings['max_file_size'] ?>" min="1" max="50" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Types autorisés</label>
                                <input type="text" class="form-control" name="allowed_file_types"
                                    value="<?= htmlspecialchars($settings['allowed_file_types'], ENT_QUOTES, 'UTF-8') ?>">
                                <div class="form-text">Séparés par des virgules ex: pdf,jpg,png</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="card mb-4">
                    <div class="card-header"><h5>Options</h5></div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode"
                                id="maintenance_mode" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="maintenance_mode">
                                Mode maintenance (seuls les admins peuvent accéder au site)
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="registration_open"
                                id="registration_open" <?= !empty($settings['registration_open']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="registration_open">
                                Inscription ouverte aux citoyens
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="save_settings" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                </button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let formModified = false;
document.querySelectorAll('#settingsForm input, #settingsForm select, #settingsForm textarea').forEach(el => {
    el.addEventListener('change', () => { formModified = true; });
});
window.addEventListener('beforeunload', (e) => {
    if (formModified) {
        e.preventDefault();
        e.returnValue = '';
    }
});
document.getElementById('settingsForm').addEventListener('submit', () => { formModified = false; });
</script>
</body>
</html>
