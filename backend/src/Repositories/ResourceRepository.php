<?php

namespace Repositories;
use mysqli;

class ResourceRepository {
    private mysqli $db;

    /**
     * Constructor de ResourceRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Crear un nuevo recurso.
     * @param array $resource Array asociativo con los datos del recurso:
     * @return int ID del recurso creado.
     */
    public function create(array $resource): int {
        
        $query = "INSERT INTO recursos (idVariante, tipo, titulo, descripcion, file_path, creado_por) 
                                                VALUES   (?, ?, ?, ?, ?, ?)";
        $st = $this->db->prepare($query);
        $st->bind_param(
            "issssi", 
            $resource['idVariante'], 
            $resource['tipo'], 
            $resource['titulo'], 
            $resource['descripcion'], 
            $resource['file_path'], 
            $resource['creado_por']
        );
        $st->execute();
        return $st->insert_id;

    }

    /**
     * Buscar un recurso por campo.
     * @param string $field Nombre del campo a buscar (ej: 'idRecurso', 'idVariante', 'tipo').
     * @param string $value Valor a buscar en el campo.
     * @return bool Verdadero si el recurso existe, falso en caso contrario.
     */
    public function findBy(string $field, string $value): bool {
        $allowedFields = ['idRecurso', 'idVariante', 'tipo', 'creado_por'];
        if (!in_array($field, $allowedFields)) {
            throw new \InvalidArgumentException("Campo no permitido: $field");
        }

        $query = "SELECT * FROM recursos WHERE $field = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('s', $value);
        $st->execute();
        $result = $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
        return $result ? true : false;
    }

    /**
     * Buscar un recurso por varios campos.
     * @param array $fields Array de nombres de columnas a buscar (ej: 'idRecurso', 'idVariante', 'tipo', 'creado_por').
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null Recurso encontrado o null si no existe.
     */
    public function getBy(array $fields, array $values): ?array {
        $allowedFields = ['idRecurso', 'idVariante', 'tipo', 'creado_por'];
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
        $query = "SELECT * FROM recursos WHERE $whereSql";

        $st = $this->db->prepare($query);
        $types = str_repeat('s', count($values));
        $st->bind_param($types, ...$values);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
    }

    public function delete(int $userId, int $idRecurso): int {
        $query = "DELETE FROM recursos WHERE creado_por = ? AND idRecurso = ?";
        $st = $this->db->prepare($query);
        $st->bind_param("ii", $userId, $idRecurso);
        $st->execute();
        return $st->affected_rows;
    }

}