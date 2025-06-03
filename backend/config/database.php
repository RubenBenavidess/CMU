<?php
use Dotenv\Dotenv;

/**
 * Carga las variables de entorno y establece la conexión a la base de datos.
 */
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$host = 'localhost';
$db   = 'mi_base_de_datos';
$user = 'root';
$pass = 'miPasswordSeguro';

$connection = new mysqli($host, $user, $pass, $db, 3307);
if ($connection->connect_error) {
    http_response_code(500);
    die('DB Connection Error');
}
$connection->set_charset('utf8mb4');