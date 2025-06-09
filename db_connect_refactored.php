<?php
declare(strict_types=1);

$config = parse_ini_file(__DIR__ . '/config/config.ini', true);

if (!$config) {
    error_log("Error loading configuration file.");
    throw new RuntimeException("Error loading configuration.");
}

$host = $config['database']['host'] ?? 'localhost';
$user = $config['database']['user'] ?? 'root';
$pass = $config['database']['pass'] ?? '';
$db = $config['database']['name'] ?? 'transporte';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    error_log("Connection failed: " . $mysqli->connect_error);
    throw new RuntimeException("Database connection failed.");
}

if (!$mysqli->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $mysqli->error);
    throw new RuntimeException("Error setting charset.");
}

return $mysqli;
?>
