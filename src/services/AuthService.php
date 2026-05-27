<?php
/**
 * Service d'authentification
 *
 * RESPONSABLE : Melissa ABIDER
 * Perimetre : validation des donnees d'inscription/connexion, hash du mot de passe,
 * gestion de la session et expiration apres inactivite.
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

	// Fonction pour que l'utilisateur s'inscrive
	public function register(array $data): array {
		// validation simple
		$errors = [];
		$first = trim($data['firstName'] ?? '');
		$last = trim($data['lastName'] ?? '');
		$email = strtolower(trim($data['email'] ?? ''));
		$birthDate = trim($data['birthDate'] ?? '');
		$password = $data['password'] ?? '';
		$confirm = $data['confirmPassword'] ?? '';

		if ($first === '') $errors['firstName'] = 'Le prénom est requis.';
		if ($last === '') $errors['lastName'] = 'Le nom est requis.';
		if ($email === '') {
			$errors['email'] = 'Le mail est requis.';
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = 'Le mail doit respecter le format login@domaine.extension.';
		}
		if ($birthDate === '') {
			$errors['birthDate'] = 'La date de naissance est requise.';
		} elseif (!$this->isValidBirthDate($birthDate)) {
			$errors['birthDate'] = 'La date de naissance doit respecter le format AAAAMMJJ.';
		}
		if ($password === '') {
			$errors['password'] = 'Le mot de passe est requis.';
		} elseif (strlen($password) < 6) {
			$errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
		}
		if ($confirm === '') {
			$errors['confirmPassword'] = 'La confirmation du mot de passe est requise.';
		} elseif ($password !== $confirm) {
			$errors['confirmPassword'] = 'Les mots de passe ne correspondent pas.';
		}

		if (!isset($errors['email']) && $this->userRepo->findByEmail($email)) {
			$errors['email'] = 'Un utilisateur existe déjà avec cet email.';
		}

		if (!empty($errors)) return ['success' => false, 'errors' => $errors];

		//Ici on hash le mot de passe pour éviter 
		//qu'il puisse être retrouver dans la base de données tel qu'il est
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$user = UserFactory::fromArray([
			'firstName' => $first,
			'lastName' => $last,
			'email' => $email,
			'birthDate' => $birthDate,
			'passwordHash' => $hash,
		]);

		//Fait appel au repository pour créer l'utilisateur dans la base de données
		$id = $this->userRepo->create($user);
		$user->id = $id;

		return ['success' => true, 'user' => $user];
	}

	private function isValidBirthDate(string $birthDate): bool {
		if (!preg_match('/^\d{8}$/', $birthDate)) {
			return false;
		}

		$year = (int)substr($birthDate, 0, 4);
		$month = (int)substr($birthDate, 4, 2);
		$day = (int)substr($birthDate, 6, 2);

		return checkdate($month, $day, $year);
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

	//Récupère l'utilisateur actuellement connecté en vérifiant la session
	public function getCurrentUser(): ?User {
		if (!empty($_SESSION['user_id'])) {
			$this->markSessionActivity();
			return $this->userRepo->findById((int)$_SESSION['user_id']);
		}
		return null;
	}

	//Si l'utilisateur est inactif alors la session se détruit 
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

	//détruit la session en vidant les données et en supprimant le cookie de session
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
