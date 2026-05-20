<?php
/**
 * Repository des matieres
 */
class MatiereRepository {
	private \PDO $pdo;

	public function __construct() {
		$this->pdo = DatabaseConnection::getInstance()->getPdo();
	}

	public function create(Matiere $matiere): int {
		$stmt = $this->pdo->prepare(
			'INSERT INTO matieres (owner_id, name, color, created_at)
			VALUES (:owner_id, :name, :color, :created_at)
			RETURNING id'
		);
		$stmt->execute([
			'owner_id' => $matiere->ownerId,
			'name' => $matiere->name,
			'color' => $matiere->color,
			'created_at' => $matiere->createdAt,
		]);

		return (int)$stmt->fetchColumn();
	}

	public function update(Matiere $matiere): bool {
		$stmt = $this->pdo->prepare(
			'UPDATE matieres
			SET name = :name, color = :color
			WHERE id = :id AND owner_id = :owner_id'
		);
		$stmt->execute([
			'id' => $matiere->id,
			'owner_id' => $matiere->ownerId,
			'name' => $matiere->name,
			'color' => $matiere->color,
		]);

		return $stmt->rowCount() > 0;
	}

	public function deleteForUser(int $id, int $ownerId): bool {
		$stmt = $this->pdo->prepare(
			'DELETE FROM matieres WHERE id = :id AND owner_id = :owner_id'
		);
		$stmt->execute([
			'id' => $id,
			'owner_id' => $ownerId,
		]);

		return $stmt->rowCount() > 0;
	}

	public function findByIdForUser(int $id, int $ownerId): ?Matiere {
		$stmt = $this->pdo->prepare(
			'SELECT m.*, COUNT(f.id) AS flashcard_count
			FROM matieres m
			LEFT JOIN flashcards f ON f.matiere_id = m.id
			WHERE m.id = :id AND m.owner_id = :owner_id
			GROUP BY m.id
			LIMIT 1'
		);
		$stmt->execute([
			'id' => $id,
			'owner_id' => $ownerId,
		]);
		$row = $stmt->fetch();

		return $row ? MatiereFactory::fromDatabaseRow($row) : null;
	}

	/**
	 * @return Matiere[]
	 */
	public function findAllByUser(int $ownerId): array {
		$stmt = $this->pdo->prepare(
			'SELECT m.*, COUNT(f.id) AS flashcard_count
			FROM matieres m
			LEFT JOIN flashcards f ON f.matiere_id = m.id
			WHERE m.owner_id = :owner_id
			GROUP BY m.id
			ORDER BY m.created_at DESC'
		);
		$stmt->execute(['owner_id' => $ownerId]);

		return array_map(
			fn(array $row): Matiere => MatiereFactory::fromDatabaseRow($row),
			$stmt->fetchAll()
		);
	}
}
