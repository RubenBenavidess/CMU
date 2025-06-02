<?php

namespace Services;
use Repositories\UserVariantRepository;

class UserVariantService{
    private UserVariantRepository $userVariantRepository;

    /**
     * Constructor de UserVariantService.
     * @param UserVariantRepository $userVariantRepository
     * @return void
     */
    public function __construct(UserVariantRepository $userVariantRepository) {
        $this->userVariantRepository = $userVariantRepository;
    }

    /**
     * Crear una nueva variante de usuario.
     * @param array $data Datos de la variante de usuario a crear.
     * @return int ID de la variante creada o 0 si falla.
     */
    public function create(array $data): array {
        $id = $this->userVariantRepository->create($data);
        if (!$id) {
            return ['ok' => false, 'msg' => 'user-variant-creation-failed']; 
        }
        return ['ok' => true, 'id' => $id];
    }
}