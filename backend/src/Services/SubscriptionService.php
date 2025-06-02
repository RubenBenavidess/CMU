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
        if ($this->subscriptionRepository->findBy('idUsuario', $data['idUsuario'], 'idAsignatura', $data['idAsignatura'])) {
            return ['ok' => false, 'msg' => 'subscription-exists'];
        }
        $id = $this->subscriptionRepository->create($data);
        return ['ok' => true, 'id' => $id];
    }

    /**
     * Eliminar una suscripción por ID.
     * @param int $id ID de la suscripción a eliminar.
     * @return array Resultado con 'ok' y mensaje de éxito o error.
     */
    public function delete(int $id): array {
        if (!$this->subscriptionRepository->findById($id)) {
            return ['ok' => false, 'msg' => 'subscription-not-found'];
        }
        $this->subscriptionRepository->delete($id);
        return ['ok' => true, 'msg' => 'subscription-deleted'];
    }

    /**
     * Obtener una suscripción por ID.
     * @param int $id ID de la suscripción a buscar.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function getById(int $id): ?array {
        return $this->subscriptionRepository->findById($id);
    }
}