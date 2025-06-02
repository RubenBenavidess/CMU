<?php

namespace Controllers;
use Services\SubjectService;
use Repositories\SubjectRepository;

class SubjectController {
    private SubjectService $subjectService;

    /**
     * Constructor de SubjectController.
     * @param \mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(\mysqli $db) {
        $this->subjectService = new SubjectService(new SubjectRepository($db));
    }

    /**
     * Obtener todas las asignaturas.
     * @return array|null
     */
    public function getAll(): ?array {
        header('Content-Type: application/json');

        $subjects = $this->subjectService->getAll();
        if ($subjects) {
            echo json_encode(['ok' => true, 'data' => $subjects]);
            return $subjects;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-subjects-found']);
            return null;
        }
    }
}
