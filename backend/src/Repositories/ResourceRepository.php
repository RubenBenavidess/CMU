<?php

namespace Repositories;
use mysqli;

class ResourceRepository {

    /**
     * Constructor de ResourceRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(private mysqli $db) {}

    /**
     * Buscar un recurso por campos específicos.
     * @param string $field1 Primer campo a buscar (ej: 'nombre_recurso').
     * @param string $value1 Valor del primer campo.
     * @param string $field2 Segundo campo a buscar (ej: 'idAsignatura').
     * @param string $value2 Valor del segundo campo.
     * @return array|null Recurso encontrado o null si no existe.
     */
    public function findBy(string $field1, string $value1, string $field2, string $value2): ?array {
        $allowedFields = ['idRecurso', 'idAsignatura', 'idUsuario', 'nombre_recurso', 'tipo_recurso'];
        if (!in_array($field1, $allowedFields) || !in_array($field2, $allowedFields)) {
            throw new \InvalidArgumentException("Campo no permitido: $field1 o $field2");
        }

        $query = "SELECT * FROM recursos WHERE $field1 = ? AND $field2 = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('ss', $value1, $value2);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Obtener recursos por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null Recurso encontrado o null si no existe.
     */
    public function getBy(array $fields, array $values): ?array {
        $allowedFields = ['idRecurso', 'idAsignatura', 'idUsuario', 'nombre_recurso', 'tipo_recurso'];
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
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Obtener todos los recursos.
     * @return array|null Lista de recursos o null si no hay resultados.
     */
    public function getAll(): ?array {
        $query = "SELECT * FROM recursos";
        $st = $this->db->prepare($query);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
    }

    /**
     * Crear un nuevo recurso.
     * @param array $data Datos del recurso.
     * @return int ID del recurso creado.
     */
    public function create(array $data): int {
        $query = "INSERT INTO recursos (idAsignatura, idUsuario, nombre_recurso, tipo_recurso, contenido, fecha_subida) VALUES (?, ?, ?, ?, ?, ?)";
        $st = $this->db->prepare($query);
        $fechaSubida = $data['fecha_subida'] ?? date('Y-m-d H:i:s');
        $st->bind_param('iissss', $data['idAsignatura'], $data['idUsuario'], $data['nombre_recurso'], $data['tipo_recurso'], $data['contenido'], $fechaSubida);
        $st->execute();
        return $st->insert_id;
    }

    /**
     * Eliminar un recurso por ID.
     * @param int $id ID del recurso a eliminar.
     * @return void
     */
    public function delete(int $id): void {
        $query = "DELETE FROM recursos WHERE idRecurso = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('i', $id);
        $st->execute();
    }
}