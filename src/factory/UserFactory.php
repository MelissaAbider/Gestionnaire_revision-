<?php
/**
 * Factory pour créer des utilisateurs
 */

class UserFactory {
	/**
	 * Crée un User à partir d'un tableau (ex: $_POST)
	 */
	public static function fromArray(array $data): User {
		$userData = [
			'firstName' => trim($data['firstName'] ?? ''),
			'lastName' => trim($data['lastName'] ?? ''),
			'email' => strtolower(trim($data['email'] ?? '')),
			'passwordHash' => $data['passwordHash'] ?? '',
			'createdAt' => $data['createdAt'] ?? date('Y-m-d H:i:s'),
		];

		return new User($userData);
	}
}
