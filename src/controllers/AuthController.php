<?php
/**
 * Contrôleur d'authentification
 */
class AuthController {
	private AuthService $authService;

	public function __construct() {
		$this->authService = new AuthService();
	}

	//Permet d'afficher le formulaire d'inscription
	public function registerForm(): void {
		$view = new RegisterView();
		$view->render();
	}

	//Lorsque l'utilisateur clique sur le bouton d'inscription, cette fonction est appelée pour traiter les données du formulaire
	public function registerSubmit(): void {
		$result = $this->authService->register($_POST);
		if ($result['success']) {
			//Redirige vers le tableau de bord après une inscription réussi
			header('Location: ?action=dashboard');
			exit;
		} else {
			$errors = $result['errors'];
			$view = new RegisterView();
			// if view needs errors, pass via global variable (simple approach)
			$GLOBALS['registerErrors'] = $errors;
			$view->render();
		}
	}

	//Affiche la page de connexion avec le formulaire 
	public function loginForm(): void {
		$view = new LoginView();
		$view->render();
	}

	//Lorsque l'utilisateur clique sur se connecter, cette fonction est appelée pour traiter les données du formulaire
	public function loginSubmit(): void {
		//Récupère les données dans les champs du formulaire
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

	//Fonction qui permet de se déconnecter en détruisant la session et redirige vers la page de connexion
	public function logout(): void {
		$this->authService->logout();
		header('Location: ?action=login');
		exit;
	}
}
