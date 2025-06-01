<?php

namespace Services;
use Repositories\ResourceRepository;

class ResourceService {

    /**
     * @param ResourceRepository $resourceRepository
     * @return void
     */
    public function __construct(private ResourceRepository $resourceRepository) {}

    /**
     * Obtener todos los recursos.
     * @return array|null Lista de recursos o null si no hay resultados.
     */
    public function getAll(): ?array {
        return $this->resourceRepository->getAll();
    }

    /**
     * Obtener recursos por campos específicos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null Recurso encontrado o null si no existe.
     */
    public function getBy(array $fields, array $values): ?array {
        return $this->resourceRepository->getBy($fields, $values);
    }

    /**
     * Subir un nuevo recurso.
     * @param array $data Datos del recurso a crear.
     * @return array Resultado con 'ok' y 'id' del recurso creado o mensaje de error.
     */
    public function upload(array $data): array {
        if ($this->resourceRepository->findBy('nombre_recurso', $data['nombre_recurso'], 'idAsignatura', $data['idAsignatura'])) {
            return ['ok' => false, 'msg' => 'resource-exists'];
        }
        $id = $this->resourceRepository->create($data);
        return ['ok' => true, 'id' => $id];
    }
}