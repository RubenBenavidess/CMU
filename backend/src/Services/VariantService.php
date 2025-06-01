<?php

namespace Services;
use Repositories\VariantRepository;

class VariantService {

    /**
     * @param VariantRepository $variantRepository
     * @return void 
     */
    public function __construct(private VariantRepository $variantRepository){}

    /**
     * Obtener variantes por varios campos.
     * @param array $fields Array de nombres de columnas a buscar.
     * @param array $values Array de valores correspondientes a los campos.
     * @return array|null
     */
    public function getBy(array $fields, array $values): ?array {
        return $this->variantRepository->getBy($fields, $values);
    }

    /**
     * Obtener todas las variantes.
     * @return array|null
     */
    public function getAll(): ?array {
        return $this->variantRepository->getAll();
    }
}