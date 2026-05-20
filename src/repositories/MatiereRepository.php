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

	public function findByIdForUser(int $id, int $ownerId): ?Matiere {
		if ($this->columnExists('flashcards', 'matiere_id')) {
			$stmt = $this->pdo->prepare(
				'SELECT m.*, COUNT(f.id) AS flashcard_count
				FROM matieres m
				LEFT JOIN flashcards f ON f.matiere_id = m.id
				WHERE m.id = :id AND m.owner_id = :owner_id
				GROUP BY m.id
				LIMIT 1'
			);
		} else {
			$stmt = $this->pdo->prepare(
				'SELECT m.*, 0 AS flashcard_count
				FROM matieres m
				WHERE m.id = :id AND m.owner_id = :owner_id
				LIMIT 1'
			);
		}

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
		if ($this->columnExists('flashcards', 'matiere_id')) {
			$stmt = $this->pdo->prepare(
				'SELECT m.*, COUNT(f.id) AS flashcard_count
				FROM matieres m
				LEFT JOIN flashcards f ON f.matiere_id = m.id
				WHERE m.owner_id = :owner_id
				GROUP BY m.id
				ORDER BY m.created_at DESC'
			);
		} else {
			$stmt = $this->pdo->prepare(
				'SELECT m.*, 0 AS flashcard_count
				FROM matieres m
				WHERE m.owner_id = :owner_id
				ORDER BY m.created_at DESC'
			);
		}

		$stmt->execute(['owner_id' => $ownerId]);

		return array_map(
			fn(array $row): Matiere => MatiereFactory::fromDatabaseRow($row),
			$stmt->fetchAll()
		);
	}

	private function columnExists(string $tableName, string $columnName): bool {
		return in_array($columnName, $this->getTableColumns($tableName), true);
	}

	/**
	 * @return string[]
	 */
	private function getTableColumns(string $tableName): array {
		if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
			return [];
		}

		$driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

		try {
			if ($driver === 'pgsql') {
				$stmt = $this->pdo->prepare(
					'SELECT column_name
					FROM information_schema.columns
					WHERE table_schema = current_schema()
					AND table_name = :table_name'
				);
				$stmt->execute(['table_name' => $tableName]);

				return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
			}

			if ($driver === 'sqlite') {
				$stmt = $this->pdo->query('PRAGMA table_info(' . $tableName . ')');

				return array_map(
					fn(array $row): string => (string)$row['name'],
					$stmt ? $stmt->fetchAll() : []
				);
			}

			if ($driver === 'mysql') {
				$stmt = $this->pdo->prepare(
					'SELECT column_name
					FROM information_schema.columns
					WHERE table_schema = DATABASE()
					AND table_name = :table_name'
				);
				$stmt->execute(['table_name' => $tableName]);

				return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
			}
		} catch (\Throwable $e) {
			return [];
		}

		return [];
	}
}
