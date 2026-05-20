<?php
/**
 * Service de gestion des matieres
 */
class MatiereService {
	private MatiereRepository $matiereRepo;

	public function __construct() {
		$this->matiereRepo = new MatiereRepository();
	}

	public function create(array $data, int $ownerId): array {
		$name = trim($data['name'] ?? '');
		$color = $data['color'] ?? 'blue';
		$allowedColors = ['teal', 'blue', 'green', 'orange', 'indigo'];
		$errors = [];

		if ($name === '') {
			$errors[] = 'Le nom de la matiere est requis.';
		}

		if (!in_array($color, $allowedColors, true)) {
			$color = 'blue';
		}

		if (!empty($errors)) {
			return ['success' => false, 'errors' => $errors];
		}

		$matiere = MatiereFactory::fromArray([
			'ownerId' => $ownerId,
			'name' => $name,
			'color' => $color,
		]);
		$matiere->id = $this->matiereRepo->create($matiere);

		return ['success' => true, 'matiere' => $matiere];
	}

	/**
	 * @return Matiere[]
	 */
	public function findAllByUser(int $ownerId): array {
		return $this->matiereRepo->findAllByUser($ownerId);
	}
}
