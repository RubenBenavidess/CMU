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
    private const JSON_CONTENT_TYPE = 'Content-Type: application/json';
    private function jsonResponse(array $data): void
    {
        echo json_encode($data);
    }

    private function deletePhysicalFile(string $relativePath): void
    {
        $absPath = __DIR__ . '/../../public/' . $relativePath;
        if (is_file($absPath)) {
            unlink($absPath);
        }
    }
    private function validateBody(array $body): bool
    {
        $required = ['tipo', 'titulo', 'descripcion'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                return false;
            }
        }
        return true;
    }

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
    header(self::JSON_CONTENT_TYPE);

    $userId = Session::get('idUsuario');
    if (!$userId) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'not-authenticated']);
    }

    $idVariante = $_GET['idVariante'] ?? null;
    if (!$idVariante || !$this->userVariantService->isAdminFromVariant($userId, (int)$idVariante)) {
        return $this->jsonResponse(['ok' => false, 'msg' => $idVariante ? 'not-authorized' : 'missing-fields']);
    }

    $body = $_POST ?? [];
    if (!$this->validateBody($body)) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'missing-fields']);
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'file-' . (!isset($_FILES['file']) ? 'required' : 'upload-error')]);
    }

    $file = $_FILES['file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('r_', true) . ($ext ? '.' . $ext : '');
    $relPath = "uploads/{$fileName}";
    $uploadDir = __DIR__ . '/../../public/uploads';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'server-storage-failed']);
    }

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
        return $this->jsonResponse(['ok' => false, 'msg' => $result['msg']]);
    }

    $dest = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $this->resourceService->delete($userId, (int)$result['id']);
        return $this->jsonResponse(['ok' => false, 'msg' => 'file-save-failed']);
    }

    $this->jsonResponse(['ok' => true, 'id' => $result['id']]);
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
    header(self::JSON_CONTENT_TYPE);

    $userId = Session::get('idUsuario');
    if (!$userId) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'not-authenticated']);
    }

    $params = $_GET ?: json_decode(file_get_contents('php://input'), true) ?: [];
    $idRecurso = $params['idRecurso'] ?? null;

    if (!$idRecurso) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'missing-fields']);
    }

    $resource = $this->resourceService->getBy(['idRecurso'], [(int)$idRecurso])[0] ?? null;
    if (!$resource) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'resource-not-found']);
    }a

    $variantId = (int)$resource['idVariante'];
    if (!$this->userVariantService->isAdminFromVariant($userId, $variantId)) {
        return $this->jsonResponse(['ok' => false, 'msg' => 'not-authorized']);
    }

    $deleteRes = $this->resourceService->delete($userId, (int)$idRecurso);
    if (!$deleteRes['ok']) {
        return $this->jsonResponse($deleteRes);
    }

    $this->deletePhysicalFile($resource['file_path']);
    $this->jsonResponse(['ok' => true, 'msg' => 'resource-deleted']);
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

    public function getByVariant(): void {
        header('Content-Type: application/json');
        $userId = Session::get('idUsuario');
        if (!$userId) {
            echo json_encode(['ok' => false, 'msg' => 'not-authenticated']);
            return;
        }
        $idVariante = isset($_GET['idVariante']) ? (int)$_GET['idVariante'] : null;
        if (!$idVariante) {
            echo json_encode(['ok' => false, 'msg' => 'missing-fields']);
            return;
        }
        // Solo si es suscriptor o admin de esa variante
        if (!$this->userVariantService->isSubFromVariant($userId, $idVariante)
            && !$this->userVariantService->isAdminFromVariant($userId, $idVariante)) {
            echo json_encode(['ok' => false, 'msg' => 'not-authorized']);
            return;
        }
        $recursos = $this->resourceService->getByVariant($idVariante) ?: [];
        echo json_encode(['ok' => true, 'resources' => $recursos]);
    }


}
