<?php
namespace Services;
use Repositories\EvaluationRepository;

class EvaluationService {
    private EvaluationRepository $evaluationRepository;

    /**
     * Constructor de EvaluationService.
     * @param EvaluationRepository $evaluationRepository
     * @return void
     */
    public function __construct(EvaluationRepository $evaluationRepository) {
        $this->evaluationRepository = $evaluationRepository;
    }

    /**
     * Crear una nueva evaluación.
     * @param array $data Datos de la evaluación a crear.
     * @return array Resultado con 'ok' y 'id' de la evaluación creada o mensaje de error.
     */
    public function create(array $data): array {
        if ($this->evaluationRepository->findBy('titulo', $data['titulo'], 'idAsignatura', $data['idAsignatura'])) {
            return ['ok' => false, 'msg' => 'evaluation-exists'];
        }
        $id = $this->evaluationRepository->create($data);
        return ['ok' => true, 'id' => $id];
    }

    /**
     * Obtener una evaluación por ID.
     * @param int $id ID de la evaluación a buscar.
     * @return array|null Evaluación encontrada o null si no existe.
     */
    public function get(int $id): ?array {
        return $this->evaluationRepository->findById($id);
    }

    /**
     * Enviar una evaluación (actualizar puntuación).
     * @param int $id ID de la evaluación.
     * @param array $data Datos de la evaluación enviada (puntuación).
     * @return array Resultado con 'ok' y mensaje de éxito o error.
     */
    public function submit(int $id, array $data): array {
        if (!$this->evaluationRepository->findById($id)) {
            return ['ok' => false, 'msg' => 'evaluation-not-found'];
        }
        $this->evaluationRepository->updateScore($id, $data['puntuacion']);
        return ['ok' => true, 'msg' => 'evaluation-submitted'];
    }
}