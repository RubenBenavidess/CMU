<?php

namespace Controllers;

use Helpers\Session;
use Services\ResourceService;
use Repositories\ResourceRepository;

class ResourceController {

    private ResourceService $resourceService;

    /**
     * Constructor de ResourceController.
     * @param \mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(\mysqli $db) {
        $this->resourceService = new ResourceService(new ResourceRepository($db));
    }

    /**
     * Obtener todos los recursos.
     * @return array|null Lista de recursos o null si no hay resultados.
     */
    public function list(): ?array {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $resources = $this->resourceService->getAll();
        if ($resources) {
            echo json_encode(['ok' => true, 'data' => $resources]);
            return $resources;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-resources-found']);
            return null;
        }
    }

    /**
     * Subir un nuevo recurso.
     * @return void
     */
    public function upload(): void {
        header('Content-Type: application/json');
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $body = $_POST ?: json_decode(file_get_contents('php://input'), true) ?: [];
        if (!isset($body['idAsignatura'], $body['idUsuario'], $body['nombre_recurso'], $body['tipo_recurso'], $body['contenido']) ||
            empty($body['idAsignatura']) || empty($body['idUsuario']) || empty($body['nombre_recurso']) || empty($body['tipo_recurso']) || empty($body['contenido'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        $data = [
            'idAsignatura' => $body['idAsignatura'],
            'idUsuario' => $body['idUsuario'],
            'nombre_recurso' => $body['nombre_recurso'],
            'tipo_recurso' => $body['tipo_recurso'],
            'contenido' => $body['contenido'],
            'fecha_subida' => $body['fecha_subida'] ?? date('Y-m-d H:i:s')
        ];

        $result = $this->resourceService->upload($data);
        echo json_encode($result);
    }
}