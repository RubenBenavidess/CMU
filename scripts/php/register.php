<?php 
include 'db_connection.php';

session_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'invalidUser' => false,
    'invalidEmail' => false,
    'error' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $bornDate = $_POST["bornDate"];
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Verificar si el nombre de usuario ya existe
    $checkUserQuery = "SELECT COUNT(*) FROM usuarios WHERE username = ?";
    if ($statement = $connection->prepare($checkUserQuery)) {
        $statement->bind_param("s", $username);
        $statement->execute();
        $statement->bind_result($userCount);
        $statement->fetch();
        $statement->close();

        if ($userCount > 0) {
            $response['invalidUser'] = true;
            echo json_encode($response);
            exit;
        }
    } else {
        $response['error'] = 'Error en la verificación del nombre de usuario.';
        echo json_encode($response);
        exit;
    }
    
    // Verificar si el correo electrónico ya existe
    $checkEmailQuery = "SELECT COUNT(*) FROM usuarios WHERE correo = ?";
    if ($statement = $connection->prepare($checkEmailQuery)) {
        $statement->bind_param("s", $email);
        $statement->execute();
        $statement->bind_result($emailCount);
        $statement->fetch();
        $statement->close();

        if ($emailCount > 0) {
            $response['invalidEmail'] = true;
            echo json_encode($response);
            exit;
        }
    } else {
        $response['error'] = 'Error en la verificación del correo electrónico.';
        echo json_encode($response);
        exit;
    }
    
    // Insertar el nuevo usuario
    $registerQuery = "INSERT INTO usuarios (username, contrasenia, correo, fechaNacimiento) VALUES (?, ?, ?, ?)";
    if ($statement = $connection->prepare($registerQuery)) {
        $statement->bind_param("ssss", $username, $hashedPassword, $email, $bornDate);

        if ($statement->execute()) {
            $response['success'] = true;
			$_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true;
        } else {
            $response['error'] = 'Error al registrar el usuario.';
        }
        
        $statement->close();
    } else {
        $response['error'] = 'Error en la preparación de la consulta de registro.';
    }
    
    echo json_encode($response);
    exit;
}
?>
