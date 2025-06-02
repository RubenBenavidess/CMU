<?php
namespace Repositories;
use mysqli;

class VariantRepository {
    private mysqli $db;

    /**
     * Constructor de VariantRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void 
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Obtener variantes por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null
     */
    public function getBy(array $fields, array $values): ?array {
        $allowedFields = ['idVariante', 'idAsignatura', 'nombre_variante'];
        foreach ($fields as $f) {
            if (!in_array($f, $allowedFields)) {
                throw new \InvalidArgumentException("Campo no permitido: $f");
            }
        }

        $whereClauses = [];
        foreach ($fields as $f) {
            $whereClauses[] = "$f = ?";
        }
        $whereSql = implode(' AND ', $whereClauses);
        $query = "SELECT * FROM variantes WHERE $whereSql";

        $st = $this->db->prepare($query);
        $types = str_repeat('s', count($values));
        $st->bind_param($types, ...$values);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Obtener todas las variantes.
     * @return array|null
     */
    public function getAll(): ?array {
        $query = "SELECT * FROM variantes";
        $st = $this->db->prepare($query);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
    }
}