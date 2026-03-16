<?php
// db_connect.php - Connexion PostgreSQL (utilisé par les scripts qui n'incluent pas config.php)
$db_url = getenv('DATABASE_URL') ?: 'postgresql://postgres:password@helium/heliumdb?sslmode=disable';
$db_parts = parse_url($db_url);

$host   = $db_parts['host'] ?? 'localhost';
$port   = $db_parts['port'] ?? 5432;
$dbname = ltrim($db_parts['path'] ?? '/heliumdb', '/');
$user   = $db_parts['user'] ?? 'postgres';
$pass   = $db_parts['pass'] ?? '';
$ssl    = (strpos($db_url, 'sslmode=disable') !== false) ? 'disable' : 'require';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$ssl", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
