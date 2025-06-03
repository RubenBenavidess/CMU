<?php
namespace Repositories;

use mysqli;

class DatabaseException extends \RuntimeException {}

class UserVariantRepository {
    private mysqli $db;

    private const PREPARE_ERROR_MSG = "Error al preparar la consulta: ";
    private const EXECUTE_ERROR_MSG = "Error al ejecutar la consulta: ";

    /**
     * Constructor de UserVariantRepository.
     * @param mysqli $db Conexión a la base de datos.
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Crear una nueva variante de usuario.
     * @param array $data Datos de la variante de usuario a crear.
     * @return int Número de filas afectadas (1 si fue insertado correctamente, 0 si no).
     * @throws DatabaseException Si falla la preparación o ejecución de la consulta.
     */
    public function create(array $data): int {
        if (!isset($data['idUsuario'], $data['idVariante'])) {
            throw new \InvalidArgumentException("Faltan campos requeridos: idUsuario o idVariante");
        }

        $query = !isset($data['rol'])
            ? "INSERT INTO usuarios_variantes (idUsuario, idVariante) VALUES (?, ?)"
            : "INSERT INTO usuarios_variantes (idUsuario, idVariante, rol) VALUES (?, ?, ?)";

        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }

        if (!isset($data['rol'])) {
            $st->bind_param('ii', $data['idUsuario'], $data['idVariante']);
        } else {
            $st->bind_param('iii', $data['idUsuario'], $data['idVariante'], $data['rol']);
        }

        if (!$st->execute()) {
            throw new DatabaseException(self::EXECUTE_ERROR_MSG . $st->error);
        }

        return $st->affected_rows;
    }

    /**
     * Verificar si un usuario es administrador de una variante.
     * @param int $userId ID del usuario.
     * @param int $variantId ID de la variante.
     * @return bool Verdadero si es admin, falso si no.
     * @throws DatabaseException Si falla la preparación de la consulta.
     */
    public function isAdminFromVariant(int $userId, int $variantId): bool {
        return $this->hasRole($userId, $variantId, 'admin');
    }

    /**
     * Verificar si un usuario es suscriptor de una variante.
     * @param int $userId ID del usuario.
     * @param int $variantId ID de la variante.
     * @return bool Verdadero si es suscriptor, falso si no.
     * @throws DatabaseException Si falla la preparación de la consulta.
     */
    public function isSubFromVariant(int $userId, int $variantId): bool {
        return $this->hasRole($userId, $variantId, 'suscriptor');
    }

    /**
     * Método privado reutilizable para verificar un rol específico.
     * @param int $userId
     * @param int $variantId
     * @param string $role
     * @return bool
     * @throws DatabaseException
     */
    private function hasRole(int $userId, int $variantId, string $role): bool {
        $query = "SELECT COUNT(*) as count FROM usuarios_variantes WHERE idUsuario = ? AND idVariante = ? AND rol = ?";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }

        $st->bind_param('iis', $userId, $variantId, $role);
        $st->execute();

        $result = $st->get_result()->fetch_assoc();
        return isset($result['count']) && (int)$result['count'] > 0;
    }
}
