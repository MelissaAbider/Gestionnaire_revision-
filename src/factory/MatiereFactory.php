<?php
/**
 * Factory pour creer des matieres
 */
class MatiereFactory {
	public static function fromArray(array $data): Matiere {
		return new Matiere($data);
	}

	public static function fromDatabaseRow(array $row): Matiere {
		return new Matiere([
			'id' => $row['id'] ?? null,
			'ownerId' => $row['owner_id'] ?? 0,
			'name' => $row['name'] ?? '',
			'color' => $row['color'] ?? 'blue',
			'createdAt' => $row['created_at'] ?? date('Y-m-d H:i:s'),
			'flashcardCount' => $row['flashcard_count'] ?? 0,
		]);
	}
}
