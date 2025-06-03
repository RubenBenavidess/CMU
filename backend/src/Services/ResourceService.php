<?php
namespace Services;
use Repositories\ResourceRepository;

class ResourceService {
    private ResourceRepository $resourceRepository;

    /**
     * Constructor de ResourceService.
     * @param ResourceRepository $resourceRepository Repositorio de recursos.
     */
    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /**
     * Crear un nuevo recurso.
     * @param array $data Datos del recurso a crear.
     * @return array Resultado con 'ok' y 'id' del recurso creado o mensaje de error.
     */
    public function create(array $data): array {

        $id = $this->resourceRepository->create($data);
        if (!$id) {
            return ['ok' => false, 'msg' => 'resource-creation-failed'];
        }
        return ['ok' => true, 'id' => $id];

    }

    public function getBy(array $fields, array $values): ?array {
        return $this->resourceRepository->getBy($fields, $values);
    }

    public function delete(int $userId, int $resourceId): array {
        if (!$this->resourceRepository->findBy('idRecurso', $resourceId)) {
            return ['ok' => false, 'msg' => 'resource-not-found'];
        }
        if ($this->resourceRepository->delete($userId, $resourceId)) {
            return ['ok' => true, 'msg' => 'resource-deleted'];
        } else {
            return ['ok' => false, 'msg' => 'resource-deletion-failed'];
        }
    }

<<<<<<< HEAD
}
=======
    public function getByVariant(int $idVariante): ?array {
        return $this->resourceRepository->getByVariant($idVariante);
    }


}
>>>>>>> refs/remotes/origin/main
