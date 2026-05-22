<?php
/**
 * Service d'authentification
 */
class AuthService {
	private UserRepository $userRepo;
	//Si le temps ci-dessous est dépassé sans activité, la session se détruit
	private const SESSION_TIMEOUT_SECONDS = 1200;

	public function __construct() {
		$this->userRepo = new UserRepository();
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		$this->expireInactiveSession();
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

		//Ici on hash le mot de passe pour éviter 
		//qu'il puisse être retrouver dans la base de données tel qu'il est
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$user = UserFactory::fromArray([
			'firstName' => $first,
			'lastName' => $last,
			'email' => $email,
			'passwordHash' => $hash,
		]);

		//Fait appel au repository pour créer l'utilisateur dans la base de données
		$id = $this->userRepo->create($user);
		$user->id = $id;

		// auto-login
		$_SESSION['user_id'] = $user->id;
		$this->markSessionActivity();

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
		$this->markSessionActivity();
		return ['success' => true, 'user' => $user];
	}

	public function logout(): void {
		$this->destroySession();
	}

	public function getCurrentUser(): ?User {
		if (!empty($_SESSION['user_id'])) {
			$this->markSessionActivity();
			return $this->userRepo->findById((int)$_SESSION['user_id']);
		}
		return null;
	}

	private function expireInactiveSession(): void {
		if (empty($_SESSION['user_id'])) {
			return;
		}

		$lastActivity = $_SESSION['last_activity'] ?? null;
		if ($lastActivity !== null && time() - (int)$lastActivity > self::SESSION_TIMEOUT_SECONDS) {
			$this->destroySession();
			return;
		}

		$this->markSessionActivity();
	}

	private function markSessionActivity(): void {
		if (!empty($_SESSION['user_id'])) {
			$_SESSION['last_activity'] = time();
		}
	}

	private function destroySession(): void {
		$_SESSION = [];

		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(),
				'',
				time() - 42000,
				$params['path'],
				$params['domain'],
				$params['secure'],
				$params['httponly']
			);
		}

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_destroy();
		}
	}
}
