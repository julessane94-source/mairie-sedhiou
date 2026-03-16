<?php

// Dans config.php, ajoutez cette fonction
function formatNumeroRegistre($numero) {
    // Format: REG-AAAA-XXXXX
    $annee = date('Y');
    $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return 'REG-' . $annee . '-' . $random;
}

// Activer l'affichage des erreurs pour le débogage (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'mairie_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Chemins importants
define('ROOT_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');

// URL du site (à modifier selon votre configuration)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', $protocol . $host . dirname($_SERVER['SCRIPT_NAME']));

// Connexion à la base de données
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch(PDOException $e) {
    // Journaliser l'erreur
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Une erreur de connexion à la base de données est survenue. Veuillez réessayer plus tard.");
}

// Variable globale pour les paramètres
$GLOBALS['app_settings'] = null;

/**
 * Charge les paramètres depuis toutes les sources
 * @return array
 */
function loadSettings() {
    global $app_settings;
    
    if ($app_settings !== null) {
        return $app_settings;
    }
    
    // Paramètres par défaut
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
        'registration_open' => true,
        'facebook_url' => '#',
        'twitter_url' => '#',
        'linkedin_url' => '#',
        'instagram_url' => '#',
        'logo_url' => '',
        'favicon_url' => '',
        'google_analytics_id' => ''
    ];
    
    $loaded_settings = [];
    
    // 1. Essayer de charger depuis le fichier JSON
    $config_file = CONFIG_PATH . '/settings.json';
    if (file_exists($config_file) && is_readable($config_file)) {
        $content = file_get_contents($config_file);
        $json_settings = json_decode($content, true);
        if (is_array($json_settings)) {
            $loaded_settings = $json_settings;
        } else {
            error_log("Erreur de décodage JSON : " . json_last_error_msg());
        }
    }
    
    // 2. Si pas de JSON ou JSON vide, essayer la base de données
    if (empty($loaded_settings)) {
        try {
            global $pdo;
            if (isset($pdo)) {
                // Vérifier si la table settings existe
                $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
                    while ($row = $stmt->fetch()) {
                        $loaded_settings[$row['setting_key']] = $row['setting_value'];
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Erreur lors du chargement des paramètres depuis la DB : " . $e->getMessage());
        }
    }
    
    // Fusionner avec les défauts (les paramètres chargés écrasent les défauts)
    $app_settings = array_merge($default_settings, $loaded_settings);
    
    return $app_settings;
}

/**
 * Récupère tous les paramètres
 * @return array
 */
function getSettings() {
    return loadSettings();
}

/**
 * Récupère un paramètre spécifique
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSetting($key, $default = null) {
    $settings = loadSettings();
    
    if (!isset($settings[$key])) {
        return $default;
    }
    
    $value = $settings[$key];
    
    // Conversion automatique des booléens depuis les chaînes
    if (is_string($value)) {
        if ($value === 'true' || $value === '1') return true;
        if ($value === 'false' || $value === '0') return false;
    }
    
    return $value;
}

/**
 * Récupère un paramètre booléen
 * @param string $key
 * @param bool $default
 * @return bool
 */
function getSettingBool($key, $default = false) {
    $value = getSetting($key, $default);
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

/**
 * Récupère un paramètre entier
 * @param string $key
 * @param int $default
 * @return int
 */
function getSettingInt($key, $default = 0) {
    return (int)getSetting($key, $default);
}

/**
 * Sauvegarde les paramètres
 * @param array $new_settings
 * @return bool
 */
function saveSettings($new_settings) {
    global $app_settings;
    
    // Créer le dossier config s'il n'existe pas
    if (!is_dir(CONFIG_PATH)) {
        if (!mkdir(CONFIG_PATH, 0755, true)) {
            error_log("Impossible de créer le dossier config");
            return false;
        }
    }
    
    // Formater les valeurs booléennes pour le JSON
    foreach ($new_settings as $key => $value) {
        if (is_bool($value)) {
            $new_settings[$key] = $value ? '1' : '0';
        }
    }
    
    // Sauvegarder dans le fichier JSON
    $config_file = CONFIG_PATH . '/settings.json';
    $json_data = json_encode($new_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if (file_put_contents($config_file, $json_data, LOCK_EX) === false) {
        error_log("Impossible d'écrire dans le fichier de configuration");
        return false;
    }
    
    // Mettre à jour la variable globale
    $app_settings = $new_settings;
    
    // Sauvegarder aussi dans la base de données (optionnel)
    try {
        global $pdo;
        if (isset($pdo)) {
            // Créer la table settings si elle n'existe pas
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    setting_key VARCHAR(100) UNIQUE NOT NULL,
                    setting_value TEXT,
                    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            
            // Sauvegarder chaque paramètre
            foreach ($new_settings as $key => $value) {
                $db_value = is_array($value) ? json_encode($value) : (string)$value;
                $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_array($value) ? 'json' : 'string'));
                
                $stmt = $pdo->prepare("
                    INSERT INTO settings (setting_key, setting_value, setting_type) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value),
                        setting_type = VALUES(setting_type)
                ");
                $stmt->execute([$key, $db_value, $type]);
            }
        }
    } catch (Exception $e) {
        error_log("Erreur lors de la sauvegarde dans la DB : " . $e->getMessage());
        // Ne pas échouer si la sauvegarde DB échoue
    }
    
    return true;
}

/**
 * Recharge les paramètres (force le rechargement)
 * @return array
 */
function reloadSettings() {
    global $app_settings;
    $app_settings = null;
    return loadSettings();
}

/**
 * Vérifie si le mode maintenance est activé
 * @return bool
 */
function isMaintenanceMode() {
    return getSettingBool('maintenance_mode', false);
}

/**
 * Vérifie si les inscriptions sont ouvertes
 * @return bool
 */
function isRegistrationOpen() {
    return getSettingBool('registration_open', true);
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Génère un numéro de demande unique
 * @return string
 */
function generateNumeroDemande() {
    $date = date('Ymd');
    $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return 'DEM-' . $date . '-' . $random;
}

/**
 * Génère un numéro citoyen unique
 * @return string
 */
function generateNumeroCitoyen() {
    $date = date('Ymd');
    $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return 'CIT-' . $date . '-' . $random;
}

/**
 * Redirige vers une URL
 * @param string $url
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Affiche un message flash
 * @param string $message
 * @param string $type
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Récupère et efface le message flash
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Échappe les données pour HTML
 * @param string $data
 * @return string
 */
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Nettoie une chaîne de caractères
 * @param string $input
 * @return string
 */
function sanitize($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

/**
 * Valide une adresse email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Crée les dossiers nécessaires
 */
function initializeDirectories() {
    $directories = [
        CONFIG_PATH,
        UPLOAD_PATH,
        LOG_PATH,
        UPLOAD_PATH . '/documents',
        UPLOAD_PATH . '/temp'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

/**
 * Vérifie la connexion à la base de données
 * @return bool
 */
function checkDatabaseConnection() {
    global $pdo;
    try {
        $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        error_log("Perte de connexion à la base de données : " . $e->getMessage());
        return false;
    }
}

/**
 * Journalise une action
 * @param string $action
 * @param string $details
 */
function logAction($action, $details = '') {
    $log_file = LOG_PATH . '/actions.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $log_entry = "[$timestamp] User: $user_id - IP: $ip - Action: $action - Details: $details" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Initialiser les dossiers
initializeDirectories();

// Vérifier le mode maintenance (sauf pour les admins)
if (isMaintenanceMode() && 
    basename($_SERVER['PHP_SELF']) != 'maintenance.php' && 
    basename(dirname($_SERVER['PHP_SELF'])) != 'admin') {
    
    if (!isLoggedIn() || !hasRole('admin')) {
        redirect('maintenance.php');
    }
}

// Journalisation des erreurs PHP
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/php_errors.log');

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Constantes pour les types de demandes
define('DEMANDE_TYPES', [
    'extrait_naissance' => 'Extrait de naissance',
    'declaration_naissance' => 'Déclaration de naissance',
    'mariage' => 'Certificat de mariage',
    'deces' => 'Certificat de décès',
    'residence' => 'Certificat de résidence'
]);

// Constantes pour les statuts
define('DEMANDE_STATUTS', [
    'en_attente' => 'En attente',
    'en_cours' => 'En cours',
    'traite' => 'Traité',
    'rejete' => 'Rejeté'
]);

// Classes CSS pour les statuts
define('STATUT_BADGES', [
    'en_attente' => 'warning',
    'en_cours' => 'info',
    'traite' => 'success',
    'rejete' => 'danger'
]);

// Charger les paramètres au démarrage
loadSettings();

// Vérifier la connexion à la base de données
checkDatabaseConnection();
?>