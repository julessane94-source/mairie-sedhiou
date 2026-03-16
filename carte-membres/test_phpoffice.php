<?php
require_once 'vendor/autoload.php';

echo "<h1>🔍 Vérification de l'installation PhpOffice</h1>";

// Vérifier que l'autoloader fonctionne
if (file_exists('vendor/autoload.php')) {
    echo "<p style='color:green'>✅ vendor/autoload.php trouvé</p>";
} else {
    echo "<p style='color:red'>❌ vendor/autoload.php non trouvé</p>";
}

// Vérifier que la classe PhpSpreadsheet existe
if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    echo "<p style='color:green'>✅ PhpSpreadsheet est disponible</p>";
} else {
    echo "<p style='color:red'>❌ PhpSpreadsheet n'est pas disponible</p>";
}

// Vérifier la version
$composer_file = 'composer.lock';
if (file_exists($composer_file)) {
    $composer = json_decode(file_get_contents($composer_file), true);
    foreach ($composer['packages'] as $package) {
        if ($package['name'] === 'phpoffice/phpspreadsheet') {
            echo "<p style='color:green'>📦 Version installée : " . $package['version'] . "</p>";
        }
    }
}

// Lister les dossiers vendor
echo "<h2>Structure du dossier vendor :</h2>";
function listFolder($path, $indent = '') {
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $full = $path . '/' . $item;
            if (is_dir($full)) {
                echo $indent . "📁 " . $item . "/<br>";
                if ($item == 'phpoffice') {
                    listFolder($full, $indent . '&nbsp;&nbsp;&nbsp;');
                }
            } else {
                echo $indent . "📄 " . $item . "<br>";
            }
        }
    }
}
listFolder('vendor');

// Test de création d'un fichier Excel
echo "<h2>Test de création Excel :</h2>";
try {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Test réussi !');
    $sheet->setCellValue('A2', 'PhpOffice fonctionne correctement');
    
    echo "<p style='color:green'>✅ Création de Spreadsheet réussie</p>";
    
    // Sauvegarder un fichier de test
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $test_file = 'test_phpoffice.xlsx';
    $writer->save($test_file);
    
    if (file_exists($test_file)) {
        echo "<p style='color:green'>✅ Fichier Excel créé : <a href='$test_file'>$test_file</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>