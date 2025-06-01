<?php
namespace Repositories;
use mysqli;

class SubscriptionRepository {
    private mysqli $db;

    /**
     * Constructor de SubscriptionRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Buscar una suscripción por campos específicos.
     * @param string $field1 Primer campo a buscar (ej: 'idUsuario').
     * @param string $value1 Valor del primer campo.
     * @param string $field2 Segundo campo a buscar (ej: 'idAsignatura').
     * @param string $value2 Valor del segundo campo.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function findBy(string $field1, string $value1, string $field2, string $value2): ?array {
        $allowedFields = ['idUsuario', 'idAsignatura'];
        if (!in_array($field1, $allowedFields) || !in_array($field2, $allowedFields)) {
            throw new \InvalidArgumentException("Campo no permitido: $field1 o $field2");
        }

        $query = "SELECT * FROM suscripciones WHERE $field1 = ? AND $field2 = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('ss', $value1, $value2);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Buscar una suscripción por ID.
     * @param int $id ID de la suscripción.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function findById(int $id): ?array {
        $query = "SELECT * FROM suscripciones WHERE idSuscripcion = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('i', $id);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Obtener todas las suscripciones.
     * @return array|null Lista de suscripciones o null si no hay resultados.
     */
    public function getAll(): ?array {
        $query = "SELECT * FROM suscripciones";
        $st = $this->db->prepare($query);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
    }

    /**
     * Crear una nueva suscripción.
     * @param array $data Datos de la suscripción.
     * @return int ID de la suscripción creada.
     */
    public function create(array $data): int {
        $query = "INSERT INTO suscripciones (idUsuario, idAsignatura, fecha_inicio) VALUES (?, ?, ?)";
        $st = $this->db->prepare($query);
        $fechaInicio = $data['fecha_inicio'] ?? date('Y-m-d H:i:s');
        $st->bind_param('iis', $data['idUsuario'], $data['idAsignatura'], $fechaInicio);
        $st->execute();
        return $st->insert_id;
    }

    /**
     * Eliminar una suscripción por ID.
     * @param int $id ID de la suscripción a eliminar.
     * @return void
     */
    public function delete(int $id): void {
        $query = "DELETE FROM suscripciones WHERE idSuscripcion = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('i', $id);
        $st->execute();
    }
}