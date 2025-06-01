<?php

namespace Controllers;

use Helpers\Session;
use Services\EvaluationService;
use Repositories\EvaluationRepository;

class EvaluationController {

    private EvaluationService $evaluationService;

    /**
     * Constructor de EvaluationController.
     * @param \mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(\mysqli $db) {
        $this->evaluationService = new EvaluationService(new EvaluationRepository($db));
    }

    /**
     * Crear una nueva evaluación.
     * @return void
     */
    public function create(): void {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $body = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($body['idAsignatura'], $body['idUsuario'], $body['titulo'], $body['descripcion']) ||
            empty($body['idAsignatura']) || empty($body['idUsuario']) || empty($body['titulo']) || empty($body['descripcion'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        $data = [
            'idAsignatura' => $body['idAsignatura'],
            'idUsuario' => $body['idUsuario'],
            'titulo' => $body['titulo'],
            'descripcion' => $body['descripcion'],
            'fecha_creacion' => $body['fecha_creacion'] ?? date('Y-m-d H:i:s')
        ];

        $result = $this->evaluationService->create($data);
        echo json_encode($result);
    }

    /**
     * Obtener una evaluación por ID.
     * @return array|null Evaluación encontrada o null si no existe.
     */
    public function get(): ?array {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $parms = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($parms['idEvaluacion']) || empty($parms['idEvaluacion'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-id']);
            return null;
        }

        $evaluation = $this->evaluationService->get((int)$parms['idEvaluacion']);
        if ($evaluation) {
            echo json_encode(['ok' => true, 'data' => $evaluation]);
            return $evaluation;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'evaluation-not-found']);
            return null;
        }
    }

    /**
     * Enviar una evaluación (actualizar puntuación).
     * @return void
     */
    public function submit(): void {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $body = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($body['idEvaluacion'], $body['puntuacion']) || empty($body['idEvaluacion']) || !is_numeric($body['puntuacion'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields-or-invalid-score']);
            return;
        }

        $result = $this->evaluationService->submit((int)$body['idEvaluacion'], ['puntuacion' => (float)$body['puntuacion']]);
        echo json_encode($result);
    }
}