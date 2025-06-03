<?php
namespace Controllers;

use Helpers\Session;
use Services\ResourceService;
use Services\UserVariantService;
use Repositories\ResourceRepository;
use Repositories\UserVariantRepository;

class ResourceController {
    private ResourceService     $resourceService;
    private UserVariantService  $userVariantService;

    public function __construct(\mysqli $db)
    {
        $this->resourceService   = new ResourceService(new ResourceRepository($db));
        $this->userVariantService = new UserVariantService(new UserVariantRepository($db));
    }

    /**
     * POST /recursos?idVariante=#
     * Crea un recurso y sube el archivo a /public/uploads.
     */
    public function create(): void
    {
        header('Content-Type: application/json');

        /* ---------- Autenticación ---------- */
        $userId = Session::get('idUsuario');
        if (!$userId) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        /* ---------- Parámetros GET ---------- */
        $params     = $_GET ?? [];
        $idVariante = $params['idVariante'] ?? null;

        if (!$idVariante) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        /* ---------- Autorización (admin de la variante) ---------- */
        if (!$this->userVariantService->isAdminFromVariant($userId, (int)$idVariante)) {
            echo json_encode(['ok' => false, 'msg' => 'not-authorized']);
            return;
        }

        /* ---------- Cuerpo (multipart/form-data) ---------- */
        $body = $_POST ?? [];
        $required = ['tipo', 'titulo', 'descripcion'];

        foreach ($required as $f) {
            if (empty($body[$f])) {
                echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
                return;
            }
        }

        /* ---------- Validación de archivo ---------- */
        if (!isset($_FILES['file'])) {
            echo json_encode(['ok' => false, 'msg' => 'file-required']);
            return;
        }
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['ok' => false, 'msg' => 'file-upload-error']);
            return;
        }

        /* ---------- Generar nombre y ruta segura ---------- */
        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName  = uniqid('r_', true) . ($ext ? '.' . $ext : '');
        $relPath   = "uploads/{$fileName}";                    // se guarda en BD
        $uploadDir = __DIR__ . '/../../public/uploads';        // carpeta física

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            echo json_encode(['ok' => false, 'msg' => 'server-storage-failed']);
            return;
        }

        /* ---------- Inserción en BD ---------- */
        $newResource = [
            'idVariante'  => (int)$idVariante,
            'tipo'        => $body['tipo'],
            'titulo'      => $body['titulo'],
            'descripcion' => $body['descripcion'],
            'file_path'   => $relPath,
            'creado_por'  => $userId
        ];

        $result = $this->resourceService->create($newResource);
        if (!$result['ok']) {
            echo json_encode(['ok' => false, 'msg' => $result['msg']]);
            return;
        }

        /* ---------- Mover archivo solo si el INSERT fue correcto ---------- */
        $dest = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            // rollback simple
            $this->resourceService->delete($userId, (int)$result['id']);
            echo json_encode(['ok' => false, 'msg' => 'file-save-failed']);
            return;
        }

        echo json_encode(['ok' => true, 'id' => $result['id']]);
    }

    /**
     * GET /recursos?userId=#
     * Devuelve todos los recursos creados por el usuario indicado
     * (o por el usuario autenticado si no se envía userId).
     */
    public function getByUserId(): void
    {
        header('Content-Type: application/json');

        $sessionUser = Session::get('idUsuario');
        if (!$sessionUser) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $params  = $_GET ?? [];
        $userId  = isset($params['userId']) ? (int)$params['userId'] : $sessionUser;

        // Solo se permiten: ver los propios recursos o, si coincide, los de otra persona
        // bajo alguna regla extra tuya. Aquí se restringe a los propios:
        if ($userId !== $sessionUser) {
            echo json_encode(['ok' => false, 'msg' => 'not-authorized']);
            return;
        }

        $resources = $this->resourceService->getBy(['creado_por'], [$userId]) ?? [];
        echo json_encode(['ok' => true, 'resources' => $resources]);
    }

    /**
     * DELETE /recursos?idRecurso=#
     */
    public function delete(): void
    {
        header('Content-Type: application/json');

        $userId = Session::get('idUsuario');
        if (!$userId) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }

        $params    = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
        $idRecurso = $params['idRecurso'] ?? null;
        if (!$idRecurso) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }

        /* ── Recuperamos el recurso para verificar propiedad y variante ── */
        $resource = $this->resourceService
                        ->getBy(['idRecurso'], [(int)$idRecurso]);

        if (!$resource) {
            echo json_encode(['ok' => false, 'msg' => 'resource-not-found']);
            return;
        }

        $resource      = $resource[0];
        $resourceOwner = (int)$resource['creado_por'];
        $variantId     = (int)$resource['idVariante'];

        /* ── Nueva verificación: ¿es admin de la variante? ── */
        if (!$this->userVariantService->isAdminFromVariant($userId, $variantId)) {
            echo json_encode(['ok' => false, 'msg' => 'not-authorized']);
            return;
        }

        /* ── (Opcional) ¿quieres además exigir que sea el creador? ── */
        // if ($userId !== $resourceOwner) {
        //     echo json_encode(['ok' => false, 'msg' => 'not-authorized']);
        //     return;
        // }

        /* ── Eliminamos ── */
        $deleteRes = $this->resourceService->delete($userId, $idRecurso);
        if (!$deleteRes['ok']) {
            echo json_encode($deleteRes);
            return;
        }

        /* ── Borrado físico del archivo si aún existe ── */
        $absPath = __DIR__ . '/../../public/' . $resource['file_path'];
        if (is_file($absPath)) {
            unlink($absPath);
        }

        echo json_encode(['ok' => true, 'msg' => 'resource-deleted']);
    }

    /**
     * GET /recursos/download?idRecurso=#
     * Sirve el PDF protegido: verifica sesión y rol admin sobre la variante.
     */
    public function download(): void
    {
        // 1) Autenticación
        $userId = Session::get('idUsuario');
        if (!$userId) {
            http_response_code(401);          // Unauthorized
            exit('not-authenticated');
        }

        // 2) Parametro
        $idRecurso = $_GET['idRecurso'] ?? null;
        if (!$idRecurso) {
            http_response_code(400);          // Bad Request
            exit('missing-fields');
        }

        // 3) Obtener el recurso
        $resource = $this->resourceService->getBy(['idRecurso'], [(int)$idRecurso])[0] ?? null;
        if (!$resource) {
            http_response_code(404);          // Not Found
            exit('resource-not-found');
        }

        // 4) Autorización (suscriptor de la variante)
        $variantId = (int)$resource['idVariante'];
        if (!$this->userVariantService->isSubFromVariant($userId, $variantId)) {
            http_response_code(403);          // Forbidden
            exit('not-authorized');
        }

        /* (Opcional) — descomenta para exigir además que sea el creador
        if ($userId !== (int)$resource['creado_por']) {
            http_response_code(403);
            exit('not-authorized');
        }
        */

        // 5) Ruta física (se mantiene en /public/uploads/)
        $absPath = __DIR__ . '/../../public/' . $resource['file_path'];
        if (!is_file($absPath)) {
            http_response_code(410);          // Gone
            exit('file-missing');
        }

        // 6) Cabeceras y envío
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($absPath) . '"');
        header('Content-Length: ' . filesize($absPath));

        // IMPORTANTE: limpiar buffers para evitar corrupción
        if (ob_get_level()) { ob_end_clean(); }
        readfile($absPath);
        exit;
    }


}
