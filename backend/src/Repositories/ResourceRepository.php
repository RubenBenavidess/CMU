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

    public function create(array $resource): int {
        
    }

}