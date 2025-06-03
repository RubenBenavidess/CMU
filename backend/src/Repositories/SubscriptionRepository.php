<?php
namespace Repositories;
use mysqli;

class SubscriptionRepository {
    private mysqli $db;

    /**
     * Constructor de SubscriptionRepository.
     * @param mysqli $db Conexión a la base de datos.
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
     * Buscar suscripción(es) por varios campos.
     * @param array $fields  Columnas a buscar (ej: ['idUsuario','idVariante']).
     * @param array $values  Valores correspondientes a esas columnas.
     * @return array|null    Array de filas encontradas o null si no hay resultados.
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
     * Obtener todas las suscripciones (sin filtro de estado).
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
     * @param array $data ['idUsuario'=>..., 'idVariante'=>...]
     * @return int ID de la suscripción creada (auto_increment).
     */
    public function create(array $data): int {
        $query = "INSERT INTO suscripciones (idUsuario, idVariante) VALUES (?, ?)";
        $st = $this->db->prepare($query);
        $st->bind_param('ii', $data['idUsuario'], $data['idVariante']);
        $st->execute();
        return $st->insert_id;
    }

    /**
     * Actualizar estado de una suscripción ya existente.
     * @param int $userId         ID del usuario propietario.
     * @param int $suscriptionId  ID de la suscripción a actualizar.
     * @param string $newState    'activa' | 'inactiva'
     * @return int Número de filas afectadas (0 = no actualizó, >0 = OK).
     */
    public function updateState(int $userId, int $suscriptionId, string $newState): int {
        $query = "
            UPDATE suscripciones
               SET estado = ?
             WHERE idUsuario      = ?
               AND idSuscripcion  = ?
        ";
        $st = $this->db->prepare($query);
        $st->bind_param('sii', $newState, $userId, $suscriptionId);
        $st->execute();
        return $st->affected_rows;
    }

    /**
     * Obtener todas las suscripciones **(activas o inactivas)** de un usuario,
     * añadiendo además el campo `rol` que proviene de usuarios_variantes.
     *
     * Cada fila devuelta tendrá:
     *   - idSuscripcion
     *   - idUsuario
     *   - idVariante
     *   - fecha_suscripcion
     *   - estado           (puede ser 'activa' o 'inactiva')
     *   - rol              (puede ser 'admin' o 'suscriptor')
     *
     * @param int $idUsuario
     * @return array Array de filas con todas las suscripciones de ese usuario.
     */
    public function getUserSubsWithRole(int $idUsuario): array {
        $sql = "
            SELECT
                s.idSuscripcion,
                s.idUsuario,
                s.idVariante,
                s.fecha_suscripcion,
                s.estado,
                uv.rol
            FROM suscripciones AS s
            JOIN usuarios_variantes AS uv
              ON uv.idUsuario   = s.idUsuario
             AND uv.idVariante  = s.idVariante
            WHERE s.idUsuario = ?
        ";
        $st = $this->db->prepare($sql);
        $st->bind_param('i', $idUsuario);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC) ?: [];
    }
}
