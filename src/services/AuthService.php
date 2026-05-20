<?php
/**
 * Service d'authentification
 */
class AuthService {
	private UserRepository $userRepo;

	public function __construct() {
		$this->userRepo = new UserRepository();
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}

	public function register(array $data): array {
		// validation simple
		$errors = [];
		$first = trim($data['firstName'] ?? '');
		$last = trim($data['lastName'] ?? '');
		$email = strtolower(trim($data['email'] ?? ''));
		$password = $data['password'] ?? '';
		$confirm = $data['confirmPassword'] ?? '';

		if ($first === '') $errors[] = 'Le prénom est requis.';
		if ($last === '') $errors[] = 'Le nom est requis.';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
		if (strlen($password) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
		if ($password !== $confirm) $errors[] = 'Les mots de passe ne correspondent pas.';

		if ($this->userRepo->findByEmail($email)) $errors[] = 'Un utilisateur existe déjà avec cet email.';

		if (!empty($errors)) return ['success' => false, 'errors' => $errors];

		$hash = password_hash($password, PASSWORD_DEFAULT);
		$user = UserFactory::fromArray([
			'firstName' => $first,
			'lastName' => $last,
			'email' => $email,
			'passwordHash' => $hash,
		]);

		$id = $this->userRepo->create($user);
		$user->id = $id;

		// auto-login
		$_SESSION['user_id'] = $user->id;

		return ['success' => true, 'user' => $user];
	}

	public function login(string $email, string $password): array {
		$email = strtolower(trim($email));
		$user = $this->userRepo->findByEmail($email);
		if (!$user) return ['success' => false, 'error' => 'Email ou mot de passe invalide.'];

		if (!password_verify($password, $user->passwordHash)) {
			return ['success' => false, 'error' => 'Email ou mot de passe invalide.'];
		}

		$_SESSION['user_id'] = $user->id;
		return ['success' => true, 'user' => $user];
	}

	public function logout(): void {
		unset($_SESSION['user_id']);
		session_destroy();
	}

	public function getCurrentUser(): ?User {
		if (!empty($_SESSION['user_id'])) {
			return $this->userRepo->findById((int)$_SESSION['user_id']);
		}
		return null;
	}
}
