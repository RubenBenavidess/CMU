<?php
namespace Repositories;

use mysqli;

class DatabaseException extends \RuntimeException {}

class EvaluationRepository {
    private mysqli $db;
    private const ERROR_PREPARAR = "Error al preparar la consulta: ";

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function findBy(string $field1, string $value1, string $field2, string $value2): ?array {
        $allowedFields = ['idEvaluacion', 'idAsignatura', 'idUsuario', 'titulo'];
        if (!in_array($field1, $allowedFields) || !in_array($field2, $allowedFields)) {
            throw new \InvalidArgumentException("Campo no permitido: $field1 o $field2");
        }

        $query = "SELECT * FROM evaluaciones WHERE $field1 = ? AND $field2 = ?";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::ERROR_PREPARAR . $this->db->error);
        }

        $st->bind_param('ss', $value1, $value2);
        $st->execute();
        $result = $st->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function findById(int $id): ?array {
        $query = "SELECT * FROM evaluaciones WHERE idEvaluacion = ?";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::ERROR_PREPARAR . $this->db->error);
        }

        $st->bind_param('i', $id);
        $st->execute();
        $result = $st->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    /**
     * Insertar una nueva evaluación.
     * @param array $data Datos de la evaluación.
     * @return int ID de la evaluación insertada.
     */
    public function create(array $data): int {
        $query = "INSERT INTO evaluaciones (idAsignatura, idUsuario, titulo, descripcion, fecha_creacion) VALUES (?, ?, ?, ?, ?)";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::ERROR_PREPARAR . $this->db->error);
        }

        $fechaCreacion = $data['fecha_creacion'] ?? date('Y-m-d H:i:s');
        $st->bind_param(
            'iisss',
            $data['idAsignatura'],
            $data['idUsuario'],
            $data['titulo'],
            $data['descripcion'],
            $fechaCreacion
        );
        $st->execute();
        return $st->insert_id;
    }

    public function updateScore(int $id, float $puntuacion): void {
        $query = "UPDATE evaluaciones SET puntuacion = ? WHERE idEvaluacion = ?";
        $st = $this->db->prepare($query);
        if (!$st) {
            throw new DatabaseException(self::ERROR_PREPARAR . $this->db->error);
        }

        $st->bind_param('di', $puntuacion, $id);
        $st->execute();
    }
}
