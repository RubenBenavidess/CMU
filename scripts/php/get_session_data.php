<?php
session_start();
header('Content-Type: application/json');

$response = [
    'loggedin' => false,
    'username' => ''
];

if (isset($_SESSION['username']) && $_SESSION['loggedin']) {
    $response['loggedin'] = true;
    $response['username'] = $_SESSION['username'];
}

echo json_encode($response);
?>