<?php
/**
 * Modèle User
 */

class User {
	public ?int $id;
	public string $firstName;
	public string $lastName;
	public string $email;
	public string $birthDate;
	public string $passwordHash;
	public string $createdAt;

	public function __construct(array $data = []) {
		$this->id = $data['id'] ?? null;
		$this->firstName = $data['firstName'] ?? '';
		$this->lastName = $data['lastName'] ?? '';
		$this->email = $data['email'] ?? '';
		$this->birthDate = $data['birthDate'] ?? '';
		$this->passwordHash = $data['passwordHash'] ?? '';
		$this->createdAt = $data['createdAt'] ?? date('Y-m-d H:i:s');
	}

	public function toArray(): array {
		return [
			'id' => $this->id,
			'firstName' => $this->firstName,
			'lastName' => $this->lastName,
			'email' => $this->email,
			'birthDate' => $this->birthDate,
			'passwordHash' => $this->passwordHash,
			'createdAt' => $this->createdAt,
		];
	}
}
