<?php 
	include 'env.php';

	$connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

	if ($connection->connect_error) {
		die("Fallo en la conexion a la base de datos");
	}
?>