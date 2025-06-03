<?php
namespace Controllers;

use Helpers\Session;
use Services\SubscriptionService;
use Services\UserVariantService;
use Repositories\SubscriptionRepository;
use Repositories\UserVariantRepository;


class SubscriptionController {
    private SubscriptionService $subscriptionService;
    private UserVariantService $userVariantService;

    /**
     * Constructor de SubscriptionController.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(\mysqli $db) {
        $this->subscriptionService = new SubscriptionService(new SubscriptionRepository($db));
        $this->userVariantService = new UserVariantService(new UserVariantRepository($db));
    }

    /**
     * Crear una nueva suscripción.
     */
    public function create(){

        header('Content-Type: application/json');

        $userID = Session::get('idUsuario');
        if (!$userID) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $parms = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($parms['idVariante']) || empty($parms['idVariante'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        $newSuscription = [
            'idUsuario' => $userID,
            'idVariante' => $parms['idVariante']
        ];

        $result = $this->subscriptionService->create($newSuscription);

        if($result['ok']) {
        
            $newUserVariant = [
                'idUsuario' => $userID,
                'idVariante' => $parms['idVariante']
            ];

            $subResult = $this->userVariantService->create($newUserVariant);
            echo json_encode($subResult);

        } else {
            echo json_encode($result);
        }
    }

    /**
     * Desactivar una suscripción por ID.
     * @return void
     */
    public function updateState(): void {
        header('Content-Type: application/json');

        $userID = Session::get('idUsuario');
        if (!$userID) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $parms = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($parms['idSuscripcion']) || empty($parms['idSuscripcion'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        $body = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($body['state']) && empty($body['state'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        if(!in_array($body['state'], ['activa', 'inactiva'])) {
            echo json_encode(['ok' => false, 'msg' => 'invalid-state']);
            return;
        }

        $result = $this->subscriptionService->updateState((int)$userID, (int)$parms['idSuscripcion'], $body['state']);
        echo json_encode($result);
    }

    /**
     * Obtener suscripciones por ID de usuario.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function getUserSubs(): ?array {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $userID = Session::get('idUsuario');
        if (!$userID) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }
        

        $subscriptions = $this->subscriptionService->getUserSubs((int)$userID);
        if ($subscriptions) {
            echo json_encode(['ok' => true, 'data' => $subscriptions]);
            return $subscriptions;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-subscription-found']);
            return null;
        }
    }
}