<?php
require_once 'config.php';

echo "<h2>Test des paramètres</h2>";

$settings = getSettings();
echo "<pre>";
print_r($settings);
echo "</pre>";

echo "<h3>Test individuel :</h3>";
echo "Nom du site : " . getSetting('site_name') . "<br>";
echo "Email : " . getSetting('email_contact') . "<br>";
echo "Téléphone : " . getSetting('telephone') . "<br>";
echo "Mode maintenance : " . (getSetting('maintenance_mode') ? 'Oui' : 'Non') . "<br>";

// Vérifier le fichier de configuration
$config_file = dirname(__FILE__) . '/config/settings.json';
echo "<h3>Fichier de configuration :</h3>";
if (file_exists($config_file)) {
    echo "Fichier trouvé : " . $config_file . "<br>";
    echo "Dernière modification : " . date('d/m/Y H:i:s', filemtime($config_file)) . "<br>";
    echo "Permissions : " . substr(sprintf('%o', fileperms($config_file)), -4) . "<br>";
    echo "Contenu : <pre>" . htmlspecialchars(file_get_contents($config_file)) . "</pre>";
} else {
    echo "Fichier non trouvé !";
}
?>