<?php
echo "<h1>🔍 Diagnostic PhpSpreadsheet</h1>";

// 1. Vérifier le dossier vendor
echo "<h2>1. Dossier vendor</h2>";
if (is_dir('vendor')) {
    echo "✅ Dossier vendor trouvé<br>";
    
    // Lister le contenu
    $items = scandir('vendor');
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            echo "   📁 $item<br>";
        }
    }
} else {
    echo "❌ Dossier vendor NON trouvé<br>";
}

// 2. Vérifier le fichier autoload.php
echo "<h2>2. Fichier autoload.php</h2>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php trouvé<br>";
    require_once 'vendor/autoload.php';
    echo "   ✅ Autoloader chargé<br>";
} else {
    echo "❌ vendor/autoload.php NON trouvé<br>";
}

// 3. Vérifier les classes PhpOffice
echo "<h2>3. Classes PhpOffice</h2>";
$classes = [
    'PhpOffice\\PhpSpreadsheet\\Spreadsheet',
    'PhpOffice\\PhpSpreadsheet\\IOFactory',
    'PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ $class : disponible<br>";
    } else {
        echo "❌ $class : NON disponible<br>";
    }
}

// 4. Afficher le contenu de composer.json
echo "<h2>4. Fichier composer.json</h2>";
if (file_exists('composer.json')) {
    $composer = json_decode(file_get_contents('composer.json'), true);
    echo "<pre>";
    print_r($composer);
    echo "</pre>";
} else {
    echo "❌ composer.json NON trouvé<br>";
}

// 5. Afficher le contenu de composer.lock
echo "<h2>5. Fichier composer.lock</h2>";
if (file_exists('composer.lock')) {
    $lock = json_decode(file_get_contents('composer.lock'), true);
    echo "<h3>Paquets installés :</h3>";
    echo "<ul>";
    foreach ($lock['packages'] as $package) {
        echo "<li>" . $package['name'] . " - " . $package['version'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "❌ composer.lock NON trouvé<br>";
}

// 6. Version de PHP
echo "<h2>6. Version PHP</h2>";
echo "PHP Version : " . phpversion() . "<br>";
$required_version = '7.2.0';
if (version_compare(phpversion(), $required_version, '>=')) {
    echo "✅ Version compatible avec PhpSpreadsheet<br>";
} else {
    echo "❌ Version trop ancienne pour PhpSpreadsheet<br>";
}

// 7. Extensions PHP requises
echo "<h2>7. Extensions PHP</h2>";
$required_extensions = ['mbstring', 'dom', 'xml', 'zip', 'gd'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext : installée<br>";
    } else {
        echo "❌ $ext : NON installée<br>";
    }
}

// 8. Proposition de solution
echo "<h2>🔧 Solutions</h2>";

if (!is_dir('vendor') || !file_exists('vendor/autoload.php')) {
    echo "<p style='color:orange'>⚠️ Le dossier vendor n'existe pas ou est incomplet.</p>";
    echo "<h3>Solution 1 : Réinstaller avec Composer</h3>";
    echo "<pre>";
    echo "cd C:\\xampp\\htdocs\\carte-membres\n";
    echo "composer require phpoffice/phpspreadsheet\n";
    echo "</pre>";
}

if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    echo "<h3>Solution 2 : Installer manuellement</h3>";
    echo "<ol>";
    echo "<li>Téléchargez PhpSpreadsheet depuis <a href='https://github.com/PHPOffice/PhpSpreadsheet/archive/refs/heads/master.zip'>https://github.com/PHPOffice/PhpSpreadsheet</a></li>";
    echo "<li>Extrayez dans <code>vendor/phpoffice/phpspreadsheet/</code></li>";
    echo "<li>Créez le fichier <code>vendor/autoload.php</code> avec le contenu ci-dessous</li>";
    echo "</ol>";
}

// 9. Code pour autoload.php manuel
echo "<h3>📄 Code pour vendor/autoload.php (si installation manuelle)</h3>";
echo "<pre style='background:#f4f4f4; padding:10px;'>";
echo htmlspecialchars('<?php
spl_autoload_register(function ($class) {
    $prefix = \'PhpOffice\\\\PhpSpreadsheet\\\\\';
    $base_dir = __DIR__ . \'/phpoffice/phpspreadsheet/src/PhpSpreadsheet/\';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace(\'\\\\\', \'/\', $relative_class) . \'.php\';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Autres classes si nécessaire
?>');
echo "</pre>";
?>