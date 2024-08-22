<?php 
	include 'env.php';

	$connection_admin = new mysqli(DB_HOST, DB_ADMIN_USERNAME, DB_ADMIN_PASSWORD, DB_NAME);

	if ($connection->connect_error) {
		die("Fallo en la conexion a la base de datos");
	}
?>