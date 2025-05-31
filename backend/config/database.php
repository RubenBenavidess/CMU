<?php
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'cmu_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

$connection = new mysqli($host, $user, $pass, $db);
if ($connection->connect_error) {
    http_response_code(500);
    die('DB Connection Error');
}
$connection->set_charset('utf8mb4');