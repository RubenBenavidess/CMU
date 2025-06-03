namespace Controllers;

use Helpers\Session;
use Services\SubscriptionService;
use Services\UserVariantService;
use Repositories\SubscriptionRepository;
use Repositories\UserVariantRepository;

class SubscriptionController {
    private SubscriptionService  $subscriptionService;
    private UserVariantService   $userVariantService;

    /**
     * Constructor de SubscriptionController.
     * @param mysqli $db Conexión a la base de datos.
     */
    public function __construct(\mysqli $db) {
        $this->subscriptionService = new SubscriptionService(new SubscriptionRepository($db));
        $this->userVariantService  = new UserVariantService(new UserVariantRepository($db));
    }

    private function jsonResponse(array $data): void {
        echo json_encode($data);
    }

    /**
     * Crear una nueva suscripción (POST /api/createSub?idVariante=#).
     */
    public function create(): void {
        header('Content-Type: application/json');

        $userID = Session::get('idUsuario');
        if (!$userID) {
            $this->jsonResponse(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $params = $_GET ?: json_decode(file_get_contents(self::PHP_INPUT), true) ?: [];
        if (empty($params['idVariante'])) {
            $this->jsonResponse(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        $newSuscription = [
            'idUsuario'  => $userID,
            'idVariante' => (int)$parms['idVariante']
        ];

        $result = $this->subscriptionService->create($newSuscription);
        if ($result['ok']) {
            // Si se creó la suscripción, también agregamos el rol en usuarios_variantes
            // (de forma automática: rol = 'suscriptor' por defecto).
            $newUserVariant = [
                'idUsuario'  => $userID,
                'idVariante' => (int)$parms['idVariante']
            ];
            $subResult = $this->userVariantService->create($newUserVariant);
            echo json_encode($subResult);
        } else {
            $this->jsonResponse($result);
        }
    }

    /**
     * Desactivar/Activar una suscripción existente.
     * PUT /api/updateSubState?idSuscripcion=#  body: { "state": "inactiva" }
     */
    public function updateState(): void {
        header(self::JSON_HEADER);

        $userID = Session::get('idUsuario');
        if (!$userID) {
            $this->jsonResponse(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        // Obtener idSuscripcion desde GET o body
        $parms = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        $idSuscripcion = isset($parms['idSuscripcion']) ? (int)$parms['idSuscripcion'] : null;
        if (!$idSuscripcion) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        // Obtener estado desde el JSON { "state": "activa" | "inactiva" }
        $body = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($body['state']) || !in_array($body['state'], ['activa', 'inactiva'])) {
            echo json_encode(['ok' => false, 'msg' => 'invalid-state']);
            return;
        }

        $result = $this->subscriptionService->updateState($userID, $idSuscripcion, $body['state']);
        echo json_encode($result);
    }

    /**
     * GET /api/userSubs
     * Devuelve array de suscripciones activas del usuario (con el campo 'rol').
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
        if ($subscriptions !== null) {
            echo json_encode(['ok' => true, 'data' => $subscriptions]);
            return $subscriptions;
        }

        $this->jsonResponse(['ok' => false, 'msg' => 'no-subscription-found']);
        return null;
    }
}
