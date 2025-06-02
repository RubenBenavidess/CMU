<?php

namespace Repositories;
use mysqli;

class SubjectRepository {

    /**
     * Constructor de SubjectRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(private mysqli $db) {}

    /**
     * Obtener asignaturas por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null
     */
    public function getBy(array $fields, array $values): ?array {
        
        // Validar campos permitidos para evitar inyección SQL
        $allowedFields = ['idAsignatura', 'nombre_asignatura']; // agrega más si lo necesitas

        foreach ($fields as $f) {
            if (!in_array($f, $allowedFields)) {
                throw new \InvalidArgumentException("Campo no permitido: $f");
            }
        }

        // Construir la consulta SQL
        $whereClauses = [];
        foreach ($fields as $f) {
            $whereClauses[] = "$f = ?";
        }
        $whereSql = implode(' AND ', $whereClauses);
        $query = "SELECT * FROM asignaturas WHERE $whereSql";

        // Preparar la consulta
        $st = $this->db->prepare($query);
        $types = str_repeat('s', count($values));
        $st->bind_param($types, ...$values);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;

    }

    /**
     * Obtener todas las asignaturas.
     */
    public function getAll(): ?array {
        $query = "SELECT * FROM asignaturas";
        $st = $this->db->prepare($query);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
    }
}