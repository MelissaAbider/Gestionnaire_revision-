<?php
/**
 * Repository des utilisateurs
 */
class UserRepository {
	private \PDO $pdo;

	public function __construct() {
		$this->pdo = DatabaseConnection::getInstance()->getPdo();
	}

	public function findByEmail(string $email): ?User {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
		$stmt->execute(['email' => $email]);
		$row = $stmt->fetch();
		if (!$row) return null;
		return new User([
			'id' => (int)$row['id'],
			'firstName' => $row['firstName'],
			'lastName' => $row['lastName'],
			'email' => $row['email'],
			'passwordHash' => $row['passwordHash'],
			'createdAt' => $row['createdAt'],
		]);
	}

	public function findById(int $id): ?User {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
		$stmt->execute(['id' => $id]);
		$row = $stmt->fetch();
		if (!$row) return null;
		return new User([
			'id' => (int)$row['id'],
			'firstName' => $row['firstName'],
			'lastName' => $row['lastName'],
			'email' => $row['email'],
			'passwordHash' => $row['passwordHash'],
			'createdAt' => $row['createdAt'],
		]);
	}

	public function create(User $user): int {
		$stmt = $this->pdo->prepare('INSERT INTO users (firstName, lastName, email, passwordHash, createdAt) VALUES (:firstName, :lastName, :email, :passwordHash, :createdAt)');
		$stmt->execute([
			'firstName' => $user->firstName,
			'lastName' => $user->lastName,
			'email' => $user->email,
			'passwordHash' => $user->passwordHash,
			'createdAt' => $user->createdAt,
		]);

		return (int)$this->pdo->lastInsertId();
	}
}
