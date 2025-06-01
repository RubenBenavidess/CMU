<?php
use Dotenv\Dotenv;

/**
 * Carga las variables de entorno y establece la conexión a la base de datos.
 */
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'mi_base_de_datos';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

$connection = new mysqli($host, $user, $pass, $db);
if ($connection->connect_error) {
    http_response_code(500);
    die('DB Connection Error');
}
$connection->set_charset('utf8mb4');