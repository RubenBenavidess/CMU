<?php
namespace Controllers;

use Helpers\Session;
use Repositories\UserVariantRepository;
use Services\UserVariantService;

class UserVariantController{
    private UserVariantService $userVariantService;

    /**
     * Constructor de UserVariantController.
     * @param \mysqli $db Conexión a la base de datos.
     * @return void
     */
    public function __construct(\mysqli $db) {
        $this->userVariantService = new UserVariantService(new UserVariantRepository($db));
    }

}
