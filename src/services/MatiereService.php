<?php
/**
 * Service de gestion des matieres
 */
class MatiereService {
	private MatiereRepository $matiereRepo;
	private array $allowedColors = ['teal', 'blue', 'green', 'orange', 'indigo'];

	public function __construct() {
		$this->matiereRepo = new MatiereRepository();
	}

	public function create(array $data, int $ownerId): array {
		$name = trim($data['name'] ?? '');
		$color = $data['color'] ?? 'blue';
		$errors = [];

		if ($name === '') {
			$errors[] = 'Le nom de la matiere est requis.';
		}

		if (!in_array($color, $this->allowedColors, true)) {
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

	public function update(array $data, int $ownerId): array {
		$id = isset($data['id']) ? (int)$data['id'] : 0;
		$name = trim($data['name'] ?? '');
		$color = $data['color'] ?? 'blue';
		$errors = [];

		if ($id <= 0) {
			$errors[] = 'La matiere selectionnee est invalide.';
		}

		if ($name === '') {
			$errors[] = 'Le nom de la matiere est requis.';
		}

		if (!in_array($color, $this->allowedColors, true)) {
			$color = 'blue';
		}

		if (!empty($errors)) {
			return ['success' => false, 'errors' => $errors];
		}

		$matiere = $this->matiereRepo->findByIdForUser($id, $ownerId);
		if (!$matiere) {
			return ['success' => false, 'errors' => ['La matiere selectionnee est introuvable.']];
		}

		$matiere->name = $name;
		$matiere->color = $color;

		return [
			'success' => $this->matiereRepo->update($matiere),
			'matiere' => $matiere,
			'errors' => [],
		];
	}

	public function delete(array $data, int $ownerId): array {
		$id = isset($data['id']) ? (int)$data['id'] : 0;

		if ($id <= 0) {
			return ['success' => false, 'errors' => ['La matiere selectionnee est invalide.']];
		}

		if (!$this->matiereRepo->findByIdForUser($id, $ownerId)) {
			return ['success' => false, 'errors' => ['La matiere selectionnee est introuvable.']];
		}

		return [
			'success' => $this->matiereRepo->deleteForUser($id, $ownerId),
			'errors' => [],
		];
	}

	/**
	 * @return Matiere[]
	 */
	public function findAllByUser(int $ownerId): array {
		return $this->matiereRepo->findAllByUser($ownerId);
	}
}
