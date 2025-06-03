<?php
// @codeCoverageIgnoreStart
require_once __DIR__.'/../vendor/autoload.php';
// @codeCoverageIgnoreEnd
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
        'api/subjects/getAll' => fn() => $subjectController->getAll(),
        'api/variants/getAll' => fn() => $variantController->getAll(),
        'api/variants/getBySubject' => fn() => $variantController->getBySubject(),
        'api/Subs' => fn() => $subscriptionController->getAll(),
        'api/createSub' => fn() => $subscriptionController->create(),
        'api/updateSubState' => fn() => $subscriptionController->updateState(),
        'api/userSubs' => fn() => $subscriptionController->getUserSubs(),
        'api/resources/create' => fn() => $resourceController->create(),
        'api/resources/getByUser' => fn() => $resourceController->getByUserId(),
        'api/resources/delete' => fn() => $resourceController->delete(),
        'api/resources/download' => fn() => $resourceController->download(),
        'api/resources/getByVariant' => fn() => $resourceController->getByVariant(),
        default => function() {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'msg' => 'Not found']);
        }
    }
)();
