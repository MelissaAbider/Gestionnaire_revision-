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
			case 'matieres':
				$this->renderMatieres();
				break;
			case 'partagees':
				$this->renderSharedFlashcards();
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
			$base . 'views/MatiereView.php',
			$base . 'views/FlashCardPartage.php',
			$base . 'views/RegisterView.php',
			$base . 'views/LoginView.php',
			$base . 'database/DatabaseConnection.php',
			$base . 'models/User.php',
			$base . 'models/Matiere.php',
			$base . 'models/Flashcard.php',
			$base . 'models/Share.php',
			$base . 'factory/UserFactory.php',
			$base . 'factory/MatiereFactory.php',
			$base . 'factory/FlashcardFactory.php',
			$base . 'repositories/UserRepository.php',
			$base . 'repositories/MatiereRepository.php',
			$base . 'repositories/FlashcardRepository.php',
			$base . 'repositories/ShareRepository.php',
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
		$this->loadMatieresForUser((int)$user->id);
		$this->loadRecentActivityForUser((int)$user->id);

		$view = new AccueilView();
		$view->render();
	}

	private function renderMatieres(): void {
		$authService = new AuthService();
		$user = $authService->getCurrentUser();

		if (!$user) {
			header('Location: ?action=login');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		$this->loadMatieresForUser((int)$user->id);

		$view = new MatiereView();
		$view->render();
	}

	private function renderSharedFlashcards(): void {
		$authService = new AuthService();
		$user = $authService->getCurrentUser();

		if (!$user) {
			header('Location: ?action=login');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		try {
			$shareRepository = new ShareRepository();
			$GLOBALS['sharedFlashcards'] = $shareRepository->findSharedWithUser((int)$user->id, $_GET);
			$GLOBALS['sharedMatieres'] = $shareRepository->findSharedMatieresForUser((int)$user->id);
		} catch (\Throwable $e) {
			$GLOBALS['sharedFlashcards'] = [];
			$GLOBALS['sharedMatieres'] = [];
			$GLOBALS['sharedFlashcardsError'] = 'Les fiches partagees ne peuvent pas encore etre chargees. Verifie que les tables shares et flashcards existent.';
		}

		$view = new FlashCardPartage();
		$view->render();
	}

	private function createMatiere(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=matieres');
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
			header('Location: ?action=matieres');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		$GLOBALS['matiereErrors'] = $result['errors'];
		$this->loadMatieresForUser((int)$user->id);
		$view = new MatiereView();
		$view->render();
	}

	private function updateMatiere(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=matieres');
			exit;
		}

		$this->handleMatiereMutation('update');
	}

	private function deleteMatiere(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=matieres');
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
			header('Location: ?action=matieres');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		$GLOBALS['matiereErrors'] = $result['errors'];
		$this->loadMatieresForUser((int)$user->id);

		$view = new MatiereView();
		$view->render();
	}

	private function loadMatieresForUser(int $userId): void {
		try {
			$matiereService = new MatiereService();
			$GLOBALS['matieres'] = $matiereService->findAllByUser($userId);
		} catch (\Throwable $e) {
			$GLOBALS['matieres'] = [];
			$GLOBALS['matiereLoadError'] = 'Les matieres ne peuvent pas encore etre chargees. Verifie que la table matieres existe dans la base.';
		}
	}

	private function loadRecentActivityForUser(int $userId): void {
		try {
			$flashcardService = new FlashcardService();
			$GLOBALS['recentActivities'] = $flashcardService->findRecentActivityForUser($userId, 5);
		} catch (\Throwable $e) {
			$GLOBALS['recentActivities'] = [];
			$GLOBALS['recentActivitiesError'] = 'Les activites recentes ne peuvent pas encore etre chargees.';
		}
	}
}
