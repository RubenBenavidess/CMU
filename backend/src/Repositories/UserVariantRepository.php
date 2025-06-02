<?php

namespace Repositories;
use mysqli;

class UserVariantRepository {
    private mysqli $db;

    /**
     * Constructor de UserVariantRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    /**
     * Crear una nueva variante de usuario.
     * @param array $data Datos de la variante de usuario a crear.
     * @return int ID de la variante creada o 0 si falla.
     */
    public function create(array $data): int{
        $query = !isset($data['rol']) ? 
            "INSERT INTO usuarios_variantes (idUsuario, idVariante) VALUES (?, ?)" : 
            "INSERT INTO usuarios_variantes (idUsuario, idVariante, rol) VALUES (?, ?, ?)";
        $st = $this->db->prepare($query);
        if (!isset($data['rol'])) {
            $st->bind_param('ii', $data['idUsuario'], $data['idVariante']);
        } else {
            $st->bind_param('iii', $data['idUsuario'], $data['idVariante'], $data['rol']);
        }
        return $st->execute();
    }
}

