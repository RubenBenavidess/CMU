<?php
namespace Repositories;
use mysqli;

class DatabaseException extends \RuntimeException {}

class UserRepository {
    private mysqli $db;
    private const PREPARE_ERROR_MSG = "Error al preparar la consulta: ";
    private const EXECUTE_ERROR_MSG = "Error al ejecutar la consulta: ";

    /**
     * Constructor de UserRepository.
     * @param mysqli $db Conexión a la base de datos.
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Buscar un usuario por cualquier campo.
     * @param string $field El nombre de la columna (ej: 'username', 'correo').
     * @param string $value El valor a buscar.
     * @return array|null Usuario encontrado o null.
     * @throws DatabaseException Si falla la preparación o ejecución de la consulta.
     */
    public function findBy(string $field, string $value): ?array {
        $allowedFields = ['idUsuario', 'username', 'correo'];
        if (!in_array($field, $allowedFields, true)) {
            throw new \InvalidArgumentException("Campo no permitido: $field");
        }

        $query = "SELECT * FROM usuarios WHERE $field = ?";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }

        $st->bind_param('s', $value);
        if (!$st->execute()) {
            throw new DatabaseException(self::EXECUTE_ERROR_MSG . $st->error);
        }

        $result = $st->get_result();
        return $result->fetch_assoc() ?: null;
    }

    /**
     * Crear un nuevo usuario.
     * @param array $data Datos del usuario.
     * @return int ID del usuario creado.
     * @throws DatabaseException Si falla la preparación o ejecución de la consulta.
     */
    public function create(array $data): int {
        $query = "INSERT INTO usuarios (username, contrasenia, correo, fecha_nacimiento) VALUES (?, ?, ?, ?)";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $st->bind_param('ssss', $data['username'], $hashedPassword, $data['correo'], $data['fecha_nacimiento']);

        if (!$st->execute()) {
            throw new DatabaseException(self::EXECUTE_ERROR_MSG . $st->error);
        }

        return $st->insert_id;
    }
}
