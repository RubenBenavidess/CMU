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
     * @return bool Verdadero si la suscripción existe, falso en caso contrario.
     */
    public function findBy(string $field, string $value): bool {
        $allowedFields = ['idSuscripcion', 'idUsuario', 'idVariante'];
        if (!in_array($field, $allowedFields)) {
            throw new \InvalidArgumentException("Campo no permitido: $field");
        }

        $query = "SELECT * FROM suscripciones WHERE $field = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('s', $value);
        $st->execute();
        $result = $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
        return $result ? true : false;
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
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: null;
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
     * Actualizar estado de una suscripción.
     * @param int $id ID de la suscripción a actualizar.
     */
    public function updateState(int $userId, int $suscriptionId, string $newState): int {
        $query = "UPDATE suscripciones SET estado = ? WHERE idUsuario = ? AND idSuscripcion = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('sii', $newState, $userId, $suscriptionId);
        $st->execute();
        return $st->affected_rows;
    }

    public function getUserSubsWithRole(int $idUsuario): array {
        $sql = "
            SELECT s.*, v.nombre_variante, uv.rol
            FROM suscripciones s
            JOIN variantes v          ON v.idVariante   = s.idVariante
            JOIN usuarios_variantes uv ON uv.idVariante = s.idVariante
                                    AND uv.idUsuario = s.idUsuario
            WHERE s.idUsuario = ?
        ";
        $st = $this->db->prepare($sql);
        $st->bind_param('i', $idUsuario);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }

}
