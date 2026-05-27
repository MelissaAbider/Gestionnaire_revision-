<?php
/**
 * Factory pour creer des matieres
 *
 * RESPONSABLE : Jana CHEHWAN
 * Perimetre : construction des objets Matiere depuis formulaires et lignes SQL.
 */
class MatiereFactory {
	// Crée une instance de Matiere à partir d'un tableau de données
	public static function fromArray(array $data): Matiere {
		return new Matiere($data);
	}

	// Crée une instance de Matiere à partir d'une ligne de la base de données
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
