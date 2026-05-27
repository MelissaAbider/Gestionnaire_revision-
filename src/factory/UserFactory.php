<?php
/**
 * Factory pour créer des utilisateurs
 *
 * RESPONSABLE : Melissa ABIDER
 * Perimetre : construction propre des objets User depuis les donnees de formulaire.
 */

class UserFactory {
	
	// Crée une instance de User à partir d'un tableau de données
	public static function fromArray(array $data): User {
		$userData = [
			'firstName' => trim($data['firstName'] ?? ''),
			'lastName' => trim($data['lastName'] ?? ''),
			'email' => strtolower(trim($data['email'] ?? '')),
			'birthDate' => trim($data['birthDate'] ?? ''),
			'passwordHash' => $data['passwordHash'] ?? '',
			'createdAt' => $data['createdAt'] ?? date('Y-m-d H:i:s'),
		];

		return new User($userData);
	}
}
