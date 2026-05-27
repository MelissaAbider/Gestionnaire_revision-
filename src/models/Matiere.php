<?php
/**
 * Modele Matiere
 *
 * RESPONSABLE : Jana CHEHWAN
 * Perimetre : structure des donnees de matiere et nombre de fiches associees.
 */
class Matiere {
	public ?int $id;
	public int $ownerId;
	public string $name;
	public string $color;
	public string $createdAt;
	public int $flashcardCount;

	public function __construct(array $data = []) {
		$this->id = isset($data['id']) ? (int)$data['id'] : null;
		$this->ownerId = isset($data['ownerId']) ? (int)$data['ownerId'] : 0;
		$this->name = $data['name'] ?? '';
		$this->color = $data['color'] ?? 'blue';
		$this->createdAt = $data['createdAt'] ?? date('Y-m-d H:i:s');
		$this->flashcardCount = isset($data['flashcardCount']) ? (int)$data['flashcardCount'] : 0;
	}

	public function toArray(): array {
		return [
			'id' => $this->id,
			'ownerId' => $this->ownerId,
			'name' => $this->name,
			'color' => $this->color,
			'createdAt' => $this->createdAt,
			'flashcardCount' => $this->flashcardCount,
		];
	}
}
