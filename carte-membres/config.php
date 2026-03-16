<?php
session_start();

// Configuration base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'carte_membres');
define('DB_USER', 'root');
define('DB_PASS', '');

// Chemins
define('ROOT_PATH', __DIR__);
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOGO_PATH', UPLOAD_PATH . '/logos');
define('PHOTO_PATH', UPLOAD_PATH . '/membres');
define('CARTE_PATH', UPLOAD_PATH . '/cartes');

// Création des dossiers
foreach ([UPLOAD_PATH, LOGO_PATH, PHOTO_PATH, CARTE_PATH] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Connexion BDD
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonctions utilitaires
function generateNumeroMembre($structure_id) {
    return 'MEM-' . str_pad($structure_id, 3, '0', STR_PAD_LEFT) . '-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return array($r, $g, $b);
}

function getLuminance($hex) {
    list($r, $g, $b) = hex2rgb($hex);
    return 0.299*$r + 0.587*$g + 0.114*$b;
}

function getContrastColor($hex) {
    return getLuminance($hex) > 128 ? '#000000' : '#ffffff';
}
?>
<?php
// config.php
session_start();

// ... (votre code existant)

// Fonction hex2rgb (une seule fois)
function hex2rgb($hex) {
    $hex = str_replace('#', '', $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return array($r, $g, $b);
}

// ... (reste de votre code)
?>