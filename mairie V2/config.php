<?php

// Activer l'affichage des erreurs pour le débogage (désactiver en production)
error_reporting(E_ALL);
ini_set("display_errors", "1");

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Configuration MySQL (XAMPP) ──────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'mairie');
define('DB_USER', 'root');
define('DB_PASS', '');          // Mot de passe vide par défaut sur XAMPP
define('DB_CHARSET', 'utf8mb4');

// Chemins importants
define('ROOT_PATH',   __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOG_PATH',    ROOT_PATH . '/logs');

// URL du site
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', $protocol . $host);

// ── Connexion PDO MySQL ──────────────────────────────────────────────────────
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Si la base n'existe pas encore, la créer automatiquement
    if (strpos($e->getMessage(), 'Unknown database') !== false || $e->getCode() == 1049) {
        try {
            $pdoTmp = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET,
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdoTmp->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdoTmp = null;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e2) {
            die("Impossible de créer la base de données : " . $e2->getMessage());
        }
    } else {
        error_log("Erreur de connexion : " . $e->getMessage());
        die("Erreur de connexion à la base de données.<br><small>" . $e->getMessage() . "</small>");
    }
}

// Initialiser les tables si nécessaire
require_once ROOT_PATH . '/db_init.php';

// ── Paramètres ───────────────────────────────────────────────────────────────
$GLOBALS['app_settings'] = null;

function loadSettings() {
    global $app_settings;
    if ($app_settings !== null) return $app_settings;

    $default_settings = [
        'site_name'          => 'Mairie Services',
        'email_contact'      => 'contact@mairie.fr',
        'telephone'          => '01 23 45 67 89',
        'adresse'            => 'Place de la Mairie, 75000 Paris',
        'horaires'           => 'Lundi-Vendredi: 8h-17h, Samedi: 9h-12h',
        'max_file_size'      => 5,
        'allowed_file_types' => 'pdf,jpg,jpeg,png',
        'smtp_host'          => '',
        'smtp_port'          => 587,
        'smtp_user'          => '',
        'smtp_pass'          => '',
        'maintenance_mode'   => false,
        'registration_open'  => true,
        'facebook_url'       => '#',
        'twitter_url'        => '#',
        'linkedin_url'       => '#',
        'instagram_url'      => '#',
        'logo_url'           => '',
        'favicon_url'        => '',
        'google_analytics_id'=> ''
    ];

    $loaded_settings = [];
    $config_file = CONFIG_PATH . '/settings.json';
    if (file_exists($config_file) && is_readable($config_file)) {
        $json_settings = json_decode(file_get_contents($config_file), true);
        if (is_array($json_settings)) $loaded_settings = $json_settings;
    }

    $app_settings = array_merge($default_settings, $loaded_settings);
    return $app_settings;
}

function getSettings()                      { return loadSettings(); }
function getSetting($key, $default = null)  {
    $s = loadSettings();
    if (!isset($s[$key])) return $default;
    $v = $s[$key];
    if (is_string($v)) {
        if ($v === 'true'  || $v === '1') return true;
        if ($v === 'false' || $v === '0') return false;
    }
    return $v;
}
function getSettingBool($key, $default = false) { return filter_var(getSetting($key, $default), FILTER_VALIDATE_BOOLEAN); }
function getSettingInt($key, $default = 0)       { return (int)getSetting($key, $default); }

function saveSettings($new_settings) {
    global $app_settings;
    if (!is_dir(CONFIG_PATH)) mkdir(CONFIG_PATH, 0755, true);
    foreach ($new_settings as $key => $value) {
        if (is_bool($value)) $new_settings[$key] = $value ? '1' : '0';
    }
    $config_file = CONFIG_PATH . '/settings.json';
    if (file_put_contents($config_file, json_encode($new_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) === false) return false;
    $app_settings = $new_settings;
    return true;
}

function reloadSettings()     { $GLOBALS['app_settings'] = null; return loadSettings(); }
function isMaintenanceMode()  { return getSettingBool('maintenance_mode', false); }
function isRegistrationOpen() { return getSettingBool('registration_open', true); }
function isLoggedIn()         { return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']); }
function hasRole($role)       { return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role; }

function generateNumeroDemande() {
    return 'DEM-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

/**
 * Génère le N°CIT : CIT-AAAAMMJJ-NNNNN
 * @param string $date_naissance  Format YYYY-MM-DD
 * @param int    $id_registre     ID auto-incrémenté du citoyen
 */
function generateNumeroCitoyen(string $date_naissance, int $id_registre): string {
    $date_formatee = str_replace('-', '', $date_naissance); // YYYYMMDD
    $numero        = str_pad($id_registre, 5, '0', STR_PAD_LEFT);
    return 'CIT-' . $date_formatee . '-' . $numero;
}

function redirect($url)       { header('Location: ' . $url); exit(); }
function e($data)             { return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8'); }
function sanitize($input)     { return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8')); }
function validateEmail($email){ return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }

function setFlashMessage($message, $type = 'info') { $_SESSION['flash'] = ['message' => $message, 'type' => $type]; }
function getFlashMessage() {
    if (isset($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}

function initializeDirectories() {
    foreach ([CONFIG_PATH, UPLOAD_PATH, LOG_PATH, UPLOAD_PATH . '/documents', UPLOAD_PATH . '/temp'] as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0755, true);
    }
}
function checkDatabaseConnection() {
    global $pdo;
    try { $pdo->query("SELECT 1"); return true; }
    catch (PDOException $e) { return false; }
}
function logAction($action, $details = '') {
    $log_file  = LOG_PATH . '/actions.log';
    $timestamp = date('Y-m-d H:i:s');
    $user_id   = $_SESSION['user_id'] ?? 'anonymous';
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    file_put_contents($log_file, "[$timestamp] User: $user_id - IP: $ip - Action: $action - Details: $details\n", FILE_APPEND | LOCK_EX);
}

// Initialiser les dossiers
initializeDirectories();

// Mode maintenance
if (isMaintenanceMode() &&
    basename($_SERVER['PHP_SELF']) !== 'maintenance.php' &&
    basename(dirname($_SERVER['PHP_SELF'])) !== 'admin') {
    if (!isLoggedIn() || !hasRole('admin')) redirect('maintenance.php');
}

ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/php_errors.log');
date_default_timezone_set('Europe/Paris');

define('DEMANDE_TYPES', [
    'extrait_naissance'    => 'Extrait de naissance',
    'declaration_naissance'=> 'Déclaration de naissance',
    'mariage'              => 'Certificat de mariage',
    'deces'                => 'Certificat de décès',
    'residence'            => 'Certificat de résidence'
]);
define('DEMANDE_STATUTS', [
    'en_attente' => 'En attente',
    'en_cours'   => 'En cours',
    'traite'     => 'Traité',
    'rejete'     => 'Rejeté'
]);
define('STATUT_BADGES', [
    'en_attente' => 'warning',
    'en_cours'   => 'info',
    'traite'     => 'success',
    'rejete'     => 'danger'
]);

loadSettings();
?>
