<?php
// vendor/autoload.php - Auto-loader PSR-4

// Fonction d'autoloading
spl_autoload_register(function ($class) {
    // Préfixes des namespaces et leurs chemins correspondants
    $prefixes = [
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/',
        'PhpOffice\\PhpSpreadsheet\\Writer\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Writer/',
        'PhpOffice\\PhpSpreadsheet\\Reader\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Reader/',
        'PhpOffice\\PhpSpreadsheet\\Shared\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Shared/',
        'PhpOffice\\PhpSpreadsheet\\Style\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Style/',
        'PhpOffice\\PhpSpreadsheet\\Calculation\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Calculation/',
        'PhpOffice\\PhpSpreadsheet\\Cell\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Cell/',
        'PhpOffice\\PhpSpreadsheet\\Worksheet\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Worksheet/',
        'PhpOffice\\PhpSpreadsheet\\Chart\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Chart/',
        'PhpOffice\\PhpSpreadsheet\\Collection\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Collection/',
        'PhpOffice\\PhpSpreadsheet\\Helper\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Helper/',
        'PhpOffice\\PhpSpreadsheet\\Settings\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Settings/',
        'PhpOffice\\PhpSpreadsheet\\NamedRange\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/NamedRange/',
        'PhpOffice\\PhpSpreadsheet\\ReferenceHelper\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/ReferenceHelper/',
        'PhpOffice\\PhpSpreadsheet\\RichText\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/RichText/',
        'PhpOffice\\PhpSpreadsheet\\IOFactory\\' => __DIR__ . '/phpoffice/phpspreadsheet/src/PhpSpreadsheet/IOFactory/',
        
        'PHPMailer\\PHPMailer\\' => __DIR__ . '/phpmailer/phpmailer/src/',
        
        'TCPDF' => __DIR__ . '/tecnickcom/tcpdf/tcpdf.php',
    ];
    
    // Parcourir les préfixes
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        // Chemin relatif
        $relative_class = substr($class, $len);
        
        // Remplacer les séparateurs de namespace par des séparateurs de dossier
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // Si le fichier existe, le charger
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    // Cas spéciaux pour les classes sans namespace
    $special_classes = [
        'FPDF' => __DIR__ . '/fpdf/fpdf.php',
        'QRcode' => __DIR__ . '/phpqrcode/qrlib.php',
    ];
    
    if (isset($special_classes[$class])) {
        require $special_classes[$class];
        return;
    }
});

// Fonction de chargement pour les fichiers simples
function loadClass($className) {
    $paths = [
        __DIR__ . '/../classes/',
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../includes/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require $file;
            return true;
        }
    }
    return false;
}

// Enregistrer la fonction pour les classes sans namespace
spl_autoload_register('loadClass');

// Version simplifiée sans les bibliothèques externes
// Décommentez cette section si vous n'avez pas installé les bibliothèques
/*
spl_autoload_register(function ($class) {
    echo "Tentative de chargement de la classe: $class\n";
    
    // Classes de l'application
    $app_classes = [
        'Database' => __DIR__ . '/../classes/Database.php',
        'Membre' => __DIR__ . '/../models/Membre.php',
        'Structure' => __DIR__ . '/../models/Structure.php',
        'Carte' => __DIR__ . '/../classes/Carte.php',
        'Import' => __DIR__ . '/../classes/Import.php',
        'Export' => __DIR__ . '/../classes/Export.php',
        'PDF' => __DIR__ . '/../fpdf/fpdf.php',
    ];
    
    if (isset($app_classes[$class])) {
        require $app_classes[$class];
        return;
    }
});
*/

// Initialisation
date_default_timezone_set('Europe/Paris');

// Gestion des erreurs
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $log_file = __DIR__ . '/../logs/error.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    
    $message = date('Y-m-d H:i:s') . " - [$errno] $errstr in $errfile line $errline\n";
    file_put_contents($log_file, $message, FILE_APPEND);
    
    return true;
});

// Fonctions utilitaires globales
if (!function_exists('dd')) {
    function dd($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        die();
    }
}

if (!function_exists('dump')) {
    function dump($var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}

if (!function_exists('env')) {
    function env($key, $default = null) {
        static $env = null;
        
        if ($env === null) {
            $env_file = __DIR__ . '/../.env';
            if (file_exists($env_file)) {
                $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                        list($k, $v) = explode('=', $line, 2);
                        $env[trim($k)] = trim($v);
                    }
                }
            }
        }
        
        return isset($env[$key]) ? $env[$key] : $default;
    }
}

// Version simplifiée pour les tests
echo "✅ Auto-loader chargé avec succès\n";
?>