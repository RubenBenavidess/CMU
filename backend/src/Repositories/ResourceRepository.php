<?php
namespace Repositories;
use mysqli;

class DatabaseException extends \RuntimeException {}

class ResourceRepository {
    private mysqli $db;
    private const PREPARE_ERROR_MSG = "Error al preparar la consulta: ";

    /**
     * Constructor de ResourceRepository.
     * @param mysqli $db Conexión a la base de datos.
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Crear un nuevo recurso.
     * @param array $resource Array asociativo con los datos del recurso.
     * @return int ID del recurso creado.
     * @throws DatabaseException Si falla la preparación o ejecución de la consulta.
     */
    public function create(array $resource): int {
        $query = "INSERT INTO recursos (idVariante, tipo, titulo, descripcion, file_path, creado_por) VALUES (?, ?, ?, ?, ?, ?)";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }
        $st->bind_param(
            "issssi",
            $resource['idVariante'],
            $resource['tipo'],
            $resource['titulo'],
            $resource['descripcion'],
            $resource['file_path'],
            $resource['creado_por']
        );
        if (!$st->execute()) {
            throw new DatabaseException("Error al ejecutar la consulta: " . $st->error);
        }
        return $st->insert_id;
    }

    /**
     * Buscar un recurso por campo.
     * @param string $field Nombre del campo a buscar (ej: 'idRecurso', 'idVariante', 'tipo').
     * @param string $value Valor a buscar en el campo.
     * @return bool Verdadero si el recurso existe, falso en caso contrario.
     * @throws DatabaseException Si falla la preparación de la consulta.
     */
    public function findBy(string $field, string $value): bool {
        $allowedFields = ['idRecurso', 'idVariante', 'tipo', 'creado_por'];
        if (!in_array($field, $allowedFields, true)) {
            throw new \InvalidArgumentException("Campo no permitido: $field");
        }
        $query = "SELECT 1 FROM recursos WHERE $field = ? LIMIT 1";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }
        $st->bind_param('s', $value);
        $st->execute();
        $result = $st->get_result();
        return $result && $result->num_rows > 0;
    }

    /**
     * Buscar un recurso por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null Recurso encontrado o null si no existe.
     * @throws DatabaseException Si falla la preparación de la consulta.
     */
    public function getBy(array $fields, array $values): ?array {
        $allowedFields = ['idRecurso', 'idVariante', 'tipo', 'creado_por'];
        foreach ($fields as $f) {
            if (!in_array($f, $allowedFields, true)) {
                throw new \InvalidArgumentException("Campo no permitido: $f");
            }
        }
        if (count($fields) !== count($values)) {
            throw new \InvalidArgumentException("El número de campos y valores no coincide.");
        }
        $whereClauses = array_map(fn($f) => "$f = ?", $fields);
        $whereSql = implode(' AND ', $whereClauses);

        $query = "SELECT * FROM recursos WHERE $whereSql";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }

        $types = str_repeat('s', count($values));
        $st->bind_param($types, ...$values);
        $st->execute();
        $result = $st->get_result();

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : null;
    }

    /**
     * Eliminar un recurso por ID y usuario creador.
     * @param int $userId ID del usuario creador.
     * @param int $idRecurso ID del recurso.
     * @return int Número de filas afectadas.
     * @throws DatabaseException Si falla la preparación de la consulta.
     */
    public function delete(int $userId, int $idRecurso): int {
        $query = "DELETE FROM recursos WHERE creado_por = ? AND idRecurso = ?";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }
        $st->bind_param("ii", $userId, $idRecurso);
        $st->execute();
        return $st->affected_rows;
    }
}
