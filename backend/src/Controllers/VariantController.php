<?php
namespace Controllers;

use Helpers\Session;
use Services\VariantService;
use Repositories\VariantRepository;

class VariantController {
    private VariantService $variantService;

    /**
     * Constructor de VariantController.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(\mysqli $db) {
        $this->variantService = new VariantService(new VariantRepository($db));
    }
 
    /**
     * Obtener todas las variantes.
     * @return array|null
     */
    public function getAll(): ?array {
        header('Content-Type: application/json');

        $variants = $this->variantService->getAll();
        if ($variants) {
            echo json_encode(['ok' => true, 'data' => $variants]);
            return $variants;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-variants-found']);
            return null;
        }
    }

    public function getBySubject(): ?array {
        header('Content-Type: application/json');

        $params = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        
        if (!isset($params['idAsignatura']) || empty($params['idAsignatura'])) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return null;
        }
        $fields = ['idAsignatura'];
        $values = [$params['idAsignatura']];
        $variants = $this->variantService->getBy($fields, $values);
        if ($variants) {
            echo json_encode(['ok' => true, 'data' => $variants]);
            return $variants;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-variants-found']);
            return null;
        }
        
    }
}