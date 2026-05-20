<?php
/**
 * Contrôleur d'authentification
 */
class AuthController {
	private AuthService $authService;

	public function __construct() {
		$this->authService = new AuthService();
	}

	public function registerForm(): void {
		$view = new RegisterView();
		$view->render();
	}

	public function registerSubmit(): void {
		$result = $this->authService->register($_POST);
		if ($result['success']) {
			header('Location: ?action=dashboard');
			exit;
		} else {
			// ré-afficher le formulaire avec erreurs simples
			$errors = $result['errors'];
			$view = new RegisterView();
			// if view needs errors, pass via global variable (simple approach)
			$GLOBALS['registerErrors'] = $errors;
			$view->render();
		}
	}

	public function loginForm(): void {
		$view = new LoginView();
		$view->render();
	}

	public function loginSubmit(): void {
		$email = $_POST['email'] ?? '';
		$password = $_POST['password'] ?? '';
		$result = $this->authService->login($email, $password);
		if ($result['success']) {
			header('Location: ?action=dashboard');
			exit;
		} else {
			$GLOBALS['loginError'] = $result['error'];
			$view = new LoginView();
			$view->render();
		}
	}

	public function logout(): void {
		$this->authService->logout();
		header('Location: ?action=login');
		exit;
	}
}
