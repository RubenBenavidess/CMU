<?php
namespace Services;
use Repositories\SubscriptionRepository;

class SubscriptionService {
    private SubscriptionRepository $subscriptionRepository;

    /**
     * Constructor de SubscriptionService.
     * @param SubscriptionRepository $subscriptionRepository
     * @return void
     */
    public function __construct(SubscriptionRepository $subscriptionRepository) {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Obtener todas las suscripciones.
     * @return array|null Lista de suscripciones o null si no hay resultados.
     */
    public function getAll(): ?array {
        return $this->subscriptionRepository->getAll();
    }

    /**
     * Crear una nueva suscripción.
     * @param array $data Datos de la suscripción a crear.
     * @return array Resultado con 'ok' y 'id' de la suscripción creada o mensaje de error.
     */
    public function create(array $data): array {
        if($this->getBy(['idUsuario', 'idVariante'], [$data['idUsuario'], $data['idVariante']])) {
            return ['ok' => false, 'msg' => 'subscription-already-exists'];
        }

        $id = $this->subscriptionRepository->create($data);
        if (!$id) {
            return ['ok' => false, 'msg' => 'subscription-creation-failed'];
        }
        return ['ok' => true, 'id' => $id];
    }

    /**
     * Eliminar una suscripción por ID.
     * @param int $id ID de la suscripción a eliminar.
     * @return array Resultado con 'ok' y mensaje de éxito o error.
     */
    public function deactivate(int $userId, int $suscriptionId): array {
        if (!$this->subscriptionRepository->findBy('idSuscripcion', $suscriptionId)) {
            return ['ok' => false, 'msg' => 'subscription-not-found'];
        }
        $this->subscriptionRepository->deactivate($userId, $suscriptionId);
        return ['ok' => true, 'msg' => 'subscription-updated'];
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
}