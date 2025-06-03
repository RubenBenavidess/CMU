<?php
namespace Services;
use Repositories\SubscriptionRepository;

class SubscriptionService {
    private SubscriptionRepository $subscriptionRepository;

    /**
     * Constructor de SubscriptionService.
     * @param SubscriptionRepository $subscriptionRepository
     */
    public function __construct(SubscriptionRepository $subscriptionRepository) {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Crear una nueva suscripción.
     * @param array $data Datos de la suscripción a crear.
     * @return array Resultado con 'ok' y 'id' de la suscripción creada o mensaje de error.
     */
    public function create(array $data): array {
        // Si ya existe (sea activa o inactiva), devolvemos error
        $existing = $this->subscriptionRepository->getBy(
            ['idUsuario', 'idVariante'],
            [$data['idUsuario'], $data['idVariante']]
        );
        if ($existing) {
            return ['ok' => false, 'msg' => 'subscription-already-exists'];
        }

        $id = $this->subscriptionRepository->create($data);
        if (!$id) {
            return ['ok' => false, 'msg' => 'subscription-creation-failed'];
        }
        return ['ok' => true, 'id' => $id];
    }

    /**
     * Actualizar estado de una suscripción.
     * @param int $userId         ID del usuario propietario.
     * @param int $suscriptionId  ID de la suscripción.
     * @param string $state       'activa' o 'inactiva'
     * @return array Resultado con 'ok' y mensaje.
     */
    public function updateState(int $userId, int $suscriptionId, string $state): array {
        // Verificar que exista esa suscripción
        if (!$this->subscriptionRepository->findBy('idSuscripcion', (string)$suscriptionId)) {
            return ['ok' => false, 'msg' => 'subscription-not-found'];
        }
        $aff = $this->subscriptionRepository->updateState($userId, $suscriptionId, $state);
        if ($aff) {
            return ['ok' => true, 'msg' => 'subscription-updated'];
        } else {
            return ['ok' => false, 'msg' => 'subscription-update-failed'];
        }
    }

    /**
     * Obtener suscripciones activas (con rol) de un usuario.
     * @param int $idUsuario
     * @return array Lista de suscripciones activas con campo 'rol'.
     */
    public function getUserSubs(int $idUsuario): array {
        return $this->subscriptionRepository->getUserSubsWithRole($idUsuario);
    }

    /**
     * Obtener suscripción por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function getBy(array $fields, array $values): ?array {
        return $this->subscriptionRepository->getBy($fields, $values);
    }

    /**
     * Obtener todas las suscripciones (sin filtro).
     * @return array|null
     */
    public function getAll(): ?array {
        return $this->subscriptionRepository->getAll();
    }
}
