<?php
/**
 * Routeur de l'application
 */
class Router {
	public function dispatch(): void {
		$action = $_GET['action'] ?? 'home';

		// load core dependencies
		$this->loadFiles();

		$authController = new AuthController();

		switch ($action) {
			case 'home':
				$this->renderHome();
				break;
			case 'register':
				$authController->registerForm();
				break;
			case 'registerSubmit':
				$authController->registerSubmit();
				break;
			case 'login':
				$authController->loginForm();
				break;
			case 'loginSubmit':
				$authController->loginSubmit();
				break;
			case 'logout':
				$authController->logout();
				break;
			case 'createMatiere':
				$this->createMatiere();
				break;
			case 'updateMatiere':
				$this->updateMatiere();
				break;
			case 'deleteMatiere':
				$this->deleteMatiere();
				break;
			case 'dashboard':
				$this->renderHome();
				break;
			default:
				// redirect to login
				header('Location: ?action=login');
				break;
		}
	}

	private function loadFiles(): void {
		// require necessary files (basic autoload)
		$base = __DIR__ . '/';
		$files = [
			$base . 'controllers/AuthController.php',
			$base . 'views/AccueilView.php',
			$base . 'views/RegisterView.php',
			$base . 'views/LoginView.php',
			$base . 'database/DatabaseConnection.php',
			$base . 'models/User.php',
			$base . 'models/Matiere.php',
			$base . 'models/Flashcard.php',
			$base . 'factory/UserFactory.php',
			$base . 'factory/MatiereFactory.php',
			$base . 'factory/FlashcardFactory.php',
			$base . 'repositories/UserRepository.php',
			$base . 'repositories/MatiereRepository.php',
			$base . 'repositories/FlashcardRepository.php',
			$base . 'services/AuthService.php',
			$base . 'services/MatiereService.php',
			$base . 'services/FlashcardService.php',
		];

		foreach ($files as $f) {
			if (file_exists($f)) require_once $f;
		}
	}

	private function renderHome(): void {
		$authService = new AuthService();
		$user = $authService->getCurrentUser();

		if (!$user) {
			header('Location: ?action=login');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		try {
			$matiereService = new MatiereService();
			$GLOBALS['matieres'] = $matiereService->findAllByUser((int)$user->id);
		} catch (\Throwable $e) {
			$GLOBALS['matieres'] = [];
			$GLOBALS['matiereLoadError'] = 'Les matieres ne peuvent pas encore etre chargees. Verifie que la table matieres existe dans la base.';
		}
		$view = new AccueilView();
		$view->render();
	}

	private function createMatiere(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=dashboard');
			exit;
		}

		$authService = new AuthService();
		$user = $authService->getCurrentUser();

		if (!$user) {
			header('Location: ?action=login');
			exit;
		}

		$matiereService = new MatiereService();
		try {
			$result = $matiereService->create($_POST, (int)$user->id);
		} catch (\Throwable $e) {
			$result = [
				'success' => false,
				'errors' => ['Impossible de creer la matiere. Verifie que la table matieres existe et que le nom n existe pas deja.'],
			];
		}

		if ($result['success']) {
			header('Location: ?action=dashboard');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		$GLOBALS['matiereErrors'] = $result['errors'];
		try {
			$GLOBALS['matieres'] = $matiereService->findAllByUser((int)$user->id);
		} catch (\Throwable $e) {
			$GLOBALS['matieres'] = [];
		}
		$view = new AccueilView();
		$view->render();
	}

	private function updateMatiere(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=dashboard');
			exit;
		}

		$this->handleMatiereMutation('update');
	}

	private function deleteMatiere(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=dashboard');
			exit;
		}

		$this->handleMatiereMutation('delete');
	}

	private function handleMatiereMutation(string $mutation): void {
		$authService = new AuthService();
		$user = $authService->getCurrentUser();

		if (!$user) {
			header('Location: ?action=login');
			exit;
		}

		$matiereService = new MatiereService();
		try {
			$result = $mutation === 'delete'
				? $matiereService->delete($_POST, (int)$user->id)
				: $matiereService->update($_POST, (int)$user->id);
		} catch (\Throwable $e) {
			$result = [
				'success' => false,
				'errors' => ['Impossible de modifier la matiere pour le moment.'],
			];
		}

		if ($result['success']) {
			header('Location: ?action=dashboard');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		$GLOBALS['matiereErrors'] = $result['errors'];
		try {
			$GLOBALS['matieres'] = $matiereService->findAllByUser((int)$user->id);
		} catch (\Throwable $e) {
			$GLOBALS['matieres'] = [];
		}

		$view = new AccueilView();
		$view->render();
	}
}
