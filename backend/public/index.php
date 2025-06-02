<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config/database.php';

use Helpers\Session;
use Controllers\AuthController;
use Controllers\VariantController;
use Controllers\ResourceController;
use Controllers\EvaluationController;
use Controllers\SubscriptionController;
use Controllers\SubjectController;

/**
 * Inicia la sesión para manejar la autenticación de usuarios.
 */
Session::start();

/**
 * Obtiene el endpoint solicitado desde el parámetro 'path' en la URL.
 * @var string $path El endpoint solicitado (ej: 'api/register').
 */
$path = $_GET['path'] ?? '';

/**
 * Instancia los controladores con la conexión a la base de datos.
 */
$authController = new AuthController($connection);
$variantController = new VariantController($connection);
$resourceController = new ResourceController($connection);
$subscriptionController = new SubscriptionController($connection);
$subjectController = new SubjectController($connection);

/**
 * Enruta la solicitud al método correspondiente del controlador basado en el path.
 * Si no se encuentra el endpoint, devuelve un error 404.
 */
(
    match($path) {
        'api/register' => fn() => $authController->register(),
        'api/login' => fn() => $authController->login(),
        'api/logout' => fn() => $authController->logout(),
        'api/isLoggedIn' => fn() => $authController->checkLoggedIn(),
        'api/variants/getAll' => fn() => $variantController->getAll(),
        'api/variants/getBySubject' => fn() => $variantController->getBySubject(),
        'api/Subs' => fn() => $subscriptionController->getAll(),
        'api/createSub' => fn() => $subscriptionController->create(),
        'api/deactivateSub' => fn() => $subscriptionController->deactivate(),
        'api/userSubs' => fn() => $subscriptionController->getUserSubs(),
        'api/resources/getAll' => fn() => $resourceController->list(),
        'api/resources/create' => fn() => $resourceController->upload(),
        'api/resources/delete' => fn() => $resourceController->upload(),
        'api/subjects/getAll' => fn() => $subjectController->getAll(),
        default => function() {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'msg' => 'Not found']);
        }
    }
)();