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
     * Obtener una variante por varios campos.
     * @return array|null
     */
    public function getBy(): ?array {
        header('Content-Type: application/json');
        
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $parms = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        unset($parms['path']);
        $keys = array_keys($parms);
        $values = array_values($parms);

        if (empty($keys) || empty($values)) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return null;
        }

        foreach ($values as $value) {
            if (!isset($value) || empty($value)) {
                echo json_encode(['ok' => false, 'msg' => 'invalid-value']);
                return null;
            }
        }

        $variants = $this->variantService->getBy($keys, $values);
        if ($variants) {
            echo json_encode(['ok' => true, 'data' => $variants]);
            return $variants;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-variant-found']);
            return null;
        }
    }

    /**
     * Obtener todas las variantes.
     * @return array|null
     */
    public function getAll(): ?array {
        header('Content-Type: application/json');
        
        if (!Session::get('loggedin')) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return null;
        }

        $variants = $this->variantService->getAll();
        if ($variants) {
            echo json_encode(['ok' => true, 'data' => $variants]);
            return $variants;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'no-variants-found']);
            return null;
        }
    }
}