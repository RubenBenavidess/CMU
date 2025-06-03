<?php
namespace Repositories;
use mysqli;

class DatabaseException extends \RuntimeException {}

class VariantRepository {
    private mysqli $db;
    private const PREPARE_ERROR_MSG = "Error al preparar la consulta: ";
    private const EXECUTE_ERROR_MSG = "Error al ejecutar la consulta: ";

    /**
     * Constructor de VariantRepository.
     * @param mysqli $db Conexión a la base de datos.
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Obtener variantes por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null
     * @throws DatabaseException
     */
    public function getBy(array $fields, array $values): ?array {
        $allowedFields = ['idVariante', 'idAsignatura', 'nombre_variante'];

        foreach ($fields as $f) {
            if (!in_array($f, $allowedFields, true)) {
                throw new \InvalidArgumentException("Campo no permitido: $f");
            }
        }

        $whereClauses = array_map(fn($f) => "$f = ?", $fields);
        $whereSql = implode(' AND ', $whereClauses);

        $query = "SELECT * FROM variantes WHERE $whereSql";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }

        $types = str_repeat('s', count($values));
        $st->bind_param($types, ...$values);

        if (!$st->execute()) {
            throw new DatabaseException(self::EXECUTE_ERROR_MSG . $st->error);
        }

        $result = $st->get_result()->fetch_all(MYSQLI_ASSOC);

        return $result ?: null;
    }

    /**
     * Obtener todas las variantes.
     * @return array|null
     * @throws DatabaseException
     */
    public function getAll(): ?array {
        $query = "SELECT * FROM variantes";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::PREPARE_ERROR_MSG . $this->db->error);
        }

        if (!$st->execute()) {
            throw new DatabaseException(self::EXECUTE_ERROR_MSG . $st->error);
        }

        $result = $st->get_result()->fetch_all(MYSQLI_ASSOC);

        return $result ?: null;
    }
}
