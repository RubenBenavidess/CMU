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
     * Buscar una suscripción por campo.
     * @param string $field Nombre del campo a buscar (ej: 'idSuscripcion', 'idUsuario', 'idVariante').
     * @param string $value Valor a buscar en el campo.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function findBy(string $field, string $value): ?array {
        $allowedFields = ['idSuscripcion', 'idUsuario', 'idVariante'];
        if (!in_array($field, $allowedFields)) {
            throw new \InvalidArgumentException("Campo no permitido: $field");
        }

        $query = "SELECT * FROM suscripciones WHERE $field = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('s', $value);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Buscar variantes por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null Suscripción encontrada o null si no existe.
     */
    public function getBy(array $fields, array $values): ?array {
        $allowedFields = ['idSuscripcion', 'idUsuario', 'idVariante', 'estado'];
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
        $query = "SELECT * FROM suscripciones WHERE $whereSql";

        $st = $this->db->prepare($query);
        $types = str_repeat('s', count($values));
        $st->bind_param($types, ...$values);
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
        $query = "INSERT INTO suscripciones(idUsuario, idVariante) VALUES (?, ?)";
        $st = $this->db->prepare($query);
        $st->bind_param('ii', $data['idUsuario'], $data['idVariante']);
        $st->execute();
        return $st->insert_id;
    }

    /**
     * Actualizar una suscripción.
     * @param int $id ID de la suscripción a actualizar.
     */
    public function deactivate(int $userId, int $suscriptionId): void {
        $query = "UPDATE suscripciones SET estado = 'inactiva' WHERE idUsuario = ? AND idSuscripcion = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('ii', $id);
        $st->execute();
    }
}