<?php
// db_connect.php — Connexion MySQL/XAMPP (fallback pour les scripts sans config.php)

$host    = 'localhost';
$port    = 3306;
$dbname  = 'mairie';
$user    = 'root';
$pass    = '';       // Mot de passe vide par défaut sur XAMPP
$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset",
        $user, $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
