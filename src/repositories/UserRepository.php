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
			'firstName' => $row['firstname'],
			'lastName' => $row['lastname'],
			'email' => $row['email'],
			'passwordHash' => $row['password_hash'],
			'createdAt' => $row['created_at'],
		]);
	}

	public function findById(int $id): ?User {
		$stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
		$stmt->execute(['id' => $id]);
		$row = $stmt->fetch();
		if (!$row) return null;
		return new User([
			'id' => (int)$row['id'],
			'firstName' => $row['firstname'],
			'lastName' => $row['lastname'],
			'email' => $row['email'],
			'passwordHash' => $row['password_hash'],
			'createdAt' => $row['created_at'],
		]);
	}

	public function create(User $user): int {
		$stmt = $this->pdo->prepare('INSERT INTO users (firstname, lastname, email, password_hash, created_at) VALUES (:firstname, :lastname, :email, :password_hash, :created_at) RETURNING id');
		$stmt->execute([
			'firstname' => $user->firstName,
			'lastname' => $user->lastName,
			'email' => $user->email,
			'password_hash' => $user->passwordHash,
			'created_at' => $user->createdAt,
		]);

		return (int)$stmt->fetchColumn();
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function findShareCandidates(int $currentUserId): array {
		$stmt = $this->pdo->prepare(
			'SELECT id, firstname, lastname, email
			FROM users
			WHERE id <> :current_user_id
			ORDER BY firstname ASC, lastname ASC, email ASC'
		);
		$stmt->execute(['current_user_id' => $currentUserId]);

		return $stmt->fetchAll();
	}
}
