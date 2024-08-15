<?php 
include 'db_connection.php';

session_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'invalidUser' => false,
    'invalidPassword' => false,
    'error' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    $loginQuery = "SELECT contrasenia FROM usuarios WHERE username = ?";
    if ($statement = $connection->prepare($registerQuery)) {
        $statement->bind_param("s", $username);

        if ($statement->execute()) {
            $result = $statement->get_result();

			if ($result->num_rows === 0) {
				$response['invalidUser'] = true;
				echo json_encode($response);
				exit;
			} else {
				$row = $result->fetch_assoc();
				$storedPassword = $row['contrasenia'];

				if (password_verify($password, $storedPassword)) {
					session_start();
					$_SESSION['username'] = $username;
					$_SESSION['loggedin'] = true;
					$response['success'] = true;
				} else {
					$response['invalidPassword'] = true;
					echo json_encode($response);
					exit;
				}
			}
        } 
		else {
            $response['error'] = 'Error al buscar el usuario.';
        }
        
        $statement->close();
    } else {
        $response['error'] = 'Error en la preparación de la consulta de registro.';
    }
    
    echo json_encode($response);
    exit;
}
?>
