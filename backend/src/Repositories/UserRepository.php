<?php

namespace Repositories;
use mysqli;

class UserRepository {

    /**
     * Constructor de UserRepository.
     * @param mysqli $db Conexión a la base de datos.
     * @return void 
     */
    public function __construct(private mysqli $db) {}

    /**
     * Buscar un usuario por cualquier campo.
     * @param string $field El nombre de la columna (ej: 'username', 'correo').
     * @param string $value El valor a buscar.
     * @return array|null
     */
    public function findBy(string $field, string $value): ?array {
        // Validar campos permitidos para evitar inyección SQL
        $allowedFields = ['idUsuario', 'username', 'correo']; // agrega más si lo necesitas

        if (!in_array($field, $allowedFields)) {
            throw new InvalidArgumentException("Campo no permitido: $field");
        }

        $query = "SELECT * FROM usuarios WHERE $field = ?";
        $st = $this->db->prepare($query);
        $st->bind_param('s', $value);
        $st->execute();
        return $st->get_result()->fetch_assoc() ?: null;
    }


    /**
     * Crear un nuevo usuario.
     * @param array $data
     * @return int
     */
    public function create(array $data): int {

        $st=$this->db->prepare('INSERT INTO usuarios(username,contrasenia,correo,fecha_nacimiento) VALUES (?,?,?,?)');
        $st->bind_param('ssss',$data['username'],password_hash($data['password'],PASSWORD_DEFAULT),$data['email'],$data['born_date']);
        $st->execute(); 
        return $st->insert_id;
    }

    
}