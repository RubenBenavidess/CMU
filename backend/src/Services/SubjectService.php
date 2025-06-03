<?php
namespace Services;

use Repositories\SubjectRepository;

class SubjectService {
    private SubjectRepository $subjectRepository;

    /**
     * Constructor de SubjectService.
     * @param SubjectRepository $subjectRepository
     */
    public function __construct(SubjectRepository $subjectRepository) {
        $this->subjectRepository = $subjectRepository;
    }

    /**
     * Obtener asignaturas por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null
     */
    public function getBy(array $fields, array $values): ?array {
        return $this->subjectRepository->getBy($fields, $values);
    }

    /**
     * Obtener todas las asignaturas.
     * @return array|null
     */
    public function getAll(): ?array {
        return $this->subjectRepository->getAll();
    }
}
