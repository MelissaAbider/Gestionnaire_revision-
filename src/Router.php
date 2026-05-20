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
			case 'dashboard':
				echo '<h2 style="text-align:center;">Bienvenue — Vous êtes connecté</h2>';
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
			$base . 'views/RegisterView.php',
			$base . 'views/LoginView.php',
			$base . 'services/AuthService.php',
			$base . 'repositories/UserRepository.php',
			$base . 'models/User.php',
			$base . 'factory/UserFactory.php',
			$base . 'database/DatabaseConnection.php',
		];

		foreach ($files as $f) {
			if (file_exists($f)) require_once $f;
		}
	}
}
