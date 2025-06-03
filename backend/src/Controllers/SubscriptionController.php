namespace Controllers;

use Helpers\Session;
use Services\SubscriptionService;
use Services\UserVariantService;
use Repositories\SubscriptionRepository;
use Repositories\UserVariantRepository;

class SubscriptionController {
    private SubscriptionService $subscriptionService;
    private UserVariantService $userVariantService;

    private const JSON_HEADER = 'Content-Type: application/json';
    private const PHP_INPUT = 'php://input';

    public function __construct(\mysqli $db) {
        $this->subscriptionService = new SubscriptionService(new SubscriptionRepository($db));
        $this->userVariantService = new UserVariantService(new UserVariantRepository($db));
    }

    private function jsonResponse(array $data): void {
        echo json_encode($data);
    }

    /**
     * Crear una nueva suscripción.
     */
    public function create(): void {
        header(self::JSON_HEADER);

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

        $newSubscription = [
            'idUsuario' => $userID,
            'idVariante' => $params['idVariante']
        ];

        $result = $this->subscriptionService->create($newSubscription);
        if ($result['ok']) {
            $newUserVariant = $newSubscription;
            $this->jsonResponse($this->userVariantService->create($newUserVariant));
        } else {
            $this->jsonResponse($result);
        }
    }

    /**
     * Desactivar una suscripción por ID.
     */
    public function updateState(): void {
        header(self::JSON_HEADER);

        $userID = Session::get('idUsuario');
        if (!$userID) {
            $this->jsonResponse(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $params = $_GET ?: json_decode(file_get_contents(self::PHP_INPUT), true) ?: [];
        if (empty($params['idSuscripcion'])) {
            $this->jsonResponse(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        $body = $_POST ?: json_decode(file_get_contents(self::PHP_INPUT), true) ?: [];
        $state = $body['state'] ?? null;

        if (empty($state)) {
            $this->jsonResponse(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        if (!in_array($state, ['activa', 'inactiva'])) {
            $this->jsonResponse(['ok' => false, 'msg' => 'invalid-state']);
            return;
        }

        $result = $this->subscriptionService->updateState((int)$userID, (int)$params['idSuscripcion'], $state);
        $this->jsonResponse($result);
    }

    /**
     * Obtener suscripciones por ID de usuario.
     */
    public function getUserSubs(): ?array {
        header(self::JSON_HEADER);

        if (!Session::get('loggedin') || !Session::get('idUsuario')) {
            $this->jsonResponse(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $userID = (int)Session::get('idUsuario');
        $subscriptions = $this->subscriptionService->getUserSubs($userID);

        if ($subscriptions) {
            $this->jsonResponse(['ok' => true, 'data' => $subscriptions]);
            return $subscriptions;
        }

        $this->jsonResponse(['ok' => false, 'msg' => 'no-subscription-found']);
        return null;
    }
}
