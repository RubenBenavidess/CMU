<?php

namespace Repositories;
use mysqli;

class EvaluationRepository {

    /**
     * Constructor de EvaluationRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(private mysqli $db) {}

    /**
     * Buscar una evaluación por campos específicos.
     * @param string $field1 Primer campo a buscar (ej: 'titulo').
     * @param string $value1 Valor del primer campo.
     * @param string $field2 Segundo campo a buscar (ej: 'idAsignatura').
     * @param string $value2 Valor del segundo campo.
     * @return array|null Evaluación encontrada o null si no existe.
     */
    public function findBy(string $field1, string $value1, string $field2, string $value2): ?array {
        $allowedFields = ['idEvaluacion', 'idAsignatura', 'idUsuario', 'titulo'];
        if (!in_array($field1, $allowedFields) || !in_array($field2, $allowedFields)) {
            throw new \InvalidArgumentException("Campo no permitido: $field1 o $field2");
        }

        $query = "SELECT * FROM evaluaciones WHERE $field1 = ? AND $field2 = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('ss', $value1, $value2);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Buscar una evaluación por ID.
     * @param int $id ID de la evaluación.
     * @return array|null Evaluación encontrada o null si no existe.
     */
    public function findById(int $id): ?array {
        $query = "SELECT * FROM evaluaciones WHERE idEvaluacion = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('i', $id);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Crear una nueva evaluación.
     * @param array $data Datos de la evaluación.
     * @return int ID de la evaluación creada.
     */
    public function create(array $data): int {
        $query = "INSERT INTO evaluaciones (idAsignatura, idUsuario, titulo, descripcion, fecha_creacion) VALUES (?, ?, ?, ?, ?)";
        $st = $this->db->prepare($query);
        $fechaCreacion = $data['fecha_creacion'] ?? date('Y-m-d H:i:s');
        $st->bind_param('iisss', $data['idAsignatura'], $data['idUsuario'], $data['titulo'], $data['descripcion'], $fechaCreacion);
        $st->execute();
        return $st->insert_id;
    }

    /**
     * Actualizar la puntuación de una evaluación.
     * @param int $id ID de la evaluación.
     * @param float $puntuacion Nueva puntuación.
     * @return void
     */
    public function updateScore(int $id, float $puntuacion): void {
        $query = "UPDATE evaluaciones SET puntuacion = ? WHERE idEvaluacion = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('di', $puntuacion, $id);
        $st->execute();
    }
}