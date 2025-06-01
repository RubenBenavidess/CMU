<?php
namespace Controllers;

use Helpers\Session;
use Services\SubscriptionService;
use Repositories\SubscriptionRepository;

class SubscriptionController {
    private SubscriptionService $subscriptionService;

    /**
     * Constructor de SubscriptionController.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(\mysqli $db) {
        $this->subscriptionService = new SubscriptionService(new SubscriptionRepository($db));
    }

    /**
     * Obtener todas las suscripciones.
     * @return array|null Lista de suscripciones o null si no hay resultados.
     */
    public function getAll(): ?array {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $subscriptions = $this->subscriptionService->getAll();
        if ($subscriptions) {
            echo json_encode(['ok' => true, 'data' => $subscriptions]);
            return $subscriptions;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-subscriptions-found']);
            return null;
        }
    }

    /**
     * Crear una nueva suscripción.
     * @return void
     */
    public function create(): void {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $body = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($body['idUsuario'], $body['idAsignatura']) || empty($body['idUsuario']) || empty($body['idAsignatura'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        $data = [
            'idUsuario' => $body['idUsuario'],
            'idAsignatura' => $body['idAsignatura'],
            'fecha_inicio' => $body['fecha_inicio'] ?? date('Y-m-d H:i:s')
        ];

        $result = $this->subscriptionService->create($data);
        echo json_encode($result);
    }

    /**
     * Eliminar una suscripción por ID.
     * @return void
     */
    public function delete(): void {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $body = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($body['idSuscripcion']) || empty($body['idSuscripcion'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-id']);
            return;
        }

        $result = $this->subscriptionService->delete((int)$body['idSuscripcion']);
        echo json_encode($result);
    }

    /**
     * Obtener una suscripción por ID.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function getById(): ?array {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $parms = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($parms['idSuscripcion']) || empty($parms['idSuscripcion'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-id']);
            return null;
        }

        $subscription = $this->subscriptionService->getById((int)$parms['idSuscripcion']);
        if ($subscription) {
            echo json_encode(['ok' => true, 'data' => $subscription]);
            return $subscription;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'subscription-not-found']);
            return null;
        }
    }
}