<?php

include 'db_connection.php';

session_start();

header('Content-Type: application/json');

function get_subjects(&$connection, &$response){

    $sql = "SELECT nombreAsignatura FROM asignaturas";
    $result = mysqli_query($connection, $sql);

    if($result){

        $response['success'] = true;

        if (mysqli_num_rows($result) > 0) {

            $subject = [
                'subject_name' => ''
            ];

            while($registers = $result->fetch_assoc()) {

                $subject['subject_name'] = $registers["nombreAsignatura"];
                $response['resources'][] = $subject; 
            }

        }
    }else{
        $response['error'] = true;
        $response['error_desc'] = 'Ha ocurrido un error.'; 
    }


}

function get_subjects_and_variants(&$connection, $user, &$response){

    $sql = "SELECT usuarios.username, variantes.idVariante, asignaturas.nombreAsignatura, asignaturas.idAsignatura 
    FROM usuarios INNER JOIN suscribe ON usuarios.idUsuario = suscribe.idUsuario 
    INNER JOIN variantes ON suscribe.idVariante = variantes.idVariante 
    INNER JOIN asignaturas ON variantes.idAsignatura = asignaturas.idAsignatura WHERE usuarios.username = '$user'";

    $result = mysqli_query($connection, $sql);

    if($result){

        $response['success'] = true;
        $response['logged_in'] = true;
        $response['username'] = $user;

        if (mysqli_num_rows($result) > 0) {

            $subject = [
                'subject_name' => '',
                'subject_id' => '',
                'subject_id_variant' => ''
            ];

            while($registers = $result->fetch_assoc()) {

                $subject['subject_name'] = $registers["asignaturas.nombreAsignatura"];
                $subject['subject_id'] = $registers["asignaturas.idAsignatura"];
                $subject['subject_id_variant'] = $registers["variantes.idVariante"];

                $response['resources'][] = $subject; 

            }

        }
    }else{
        $response['error'] = true;
        $response['error_desc'] = 'Ha ocurrido un error.'; 
    }

}

$response = [
    'success' => false,
    'error' => false,
    'resources' => [],
    'logged_in' => false,
    'username' => '',
    'error_desc' => ''
];

if($_SERVER['REQUEST_METHOD'] == 'GET'){

    //We verify if we are in main or usermain
    
    if (isset($_SESSION['username']) && isset($_SESSION['loggedin'])) {
        get_subjects_and_variants($connection, $_SESSION['username'], $response);
    } else {
        get_subjects($connection, $response);
    }

}

echo json_encode($response);

exit;

?>