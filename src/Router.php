<?php
/**
 * Routeur de l'application
 */
class Router {
	public function dispatch(): void {
		$action = $_GET['action'] ?? 'home';

		$this->loadFiles();

		$authController = new AuthController();

		switch ($action) {
			case 'home':
			case 'dashboard':
				$this->renderHome();
				break;
			case 'matieres':
				$this->renderMatieres();
				break;
			case 'partagees':
				$this->renderSharedFlashcards();
				break;
			case 'flashcards':
				$this->renderFlashcards();
				break;
			case 'viewFlashcard':
				$this->renderFlashcardDetail((int)($_GET['id'] ?? 0));
				break;
			case 'createFlashcard':
				$this->renderFlashcardForm();
				break;
			case 'storeFlashcard':
				$this->storeFlashcard();
				break;
			case 'editFlashcard':
				$this->renderFlashcardForm((int)($_GET['id'] ?? 0));
				break;
			case 'updateFlashcard':
				$this->updateFlashcard();
				break;
			case 'deleteFlashcard':
				$this->deleteFlashcard();
				break;
			case 'recordRevision':
				$this->recordRevision();
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
			default:
				header('Location: ?action=login');
				break;
		}
	}

	private function loadFiles(): void {
		$base = __DIR__ . '/';
		$files = [
			$base . 'controllers/AuthController.php',
			$base . 'views/HomeNavView.php',
			$base . 'views/PageHeaderView.php',
			$base . 'views/AccueilView.php',
			$base . 'views/MatiereView.php',
			$base . 'views/FlashCardPartage.php',
			$base . 'views/FlashcardFormView.php',
			$base . 'views/FlashcardDetailView.php',
			$base . 'views/FlashcardsListView.php',
			$base . 'views/RegisterView.php',
			$base . 'views/LoginView.php',
			$base . 'database/DatabaseConnection.php',
			$base . 'models/User.php',
			$base . 'models/Matiere.php',
			$base . 'models/Flashcard.php',
			$base . 'models/Share.php',
			$base . 'models/QuestionResponse.php',
			$base . 'factory/UserFactory.php',
			$base . 'factory/MatiereFactory.php',
			$base . 'factory/FlashcardFactory.php',
			$base . 'factory/QuestionResponseFactory.php',
			$base . 'repositories/UserRepository.php',
			$base . 'repositories/MatiereRepository.php',
			$base . 'repositories/QuestionResponseRepository.php',
			$base . 'repositories/FlashcardRepository.php',
			$base . 'repositories/ShareRepository.php',
			$base . 'services/AuthService.php',
			$base . 'services/MatiereService.php',
			$base . 'services/FlashcardService.php',
			$base . 'services/StatsService.php',
		];

		foreach ($files as $f) {
			if (file_exists($f)) require_once $f;
		}
	}

	private function renderHome(): void {
		$user = $this->requireUser();
		$GLOBALS['currentUser'] = $user;
		$this->loadMatieresForUser((int)$user->id);
		$this->loadRecentActivityForUser((int)$user->id);
		$this->loadSharedStatsForUser((int)$user->id);
		$this->loadRevisionStatsForUser((int)$user->id);

		$view = new AccueilView();
		$view->render();
	}

	private function renderMatieres(): void {
		$user = $this->requireUser();
		$GLOBALS['currentUser'] = $user;
		$this->loadMatieresForUser((int)$user->id);

		$view = new MatiereView();
		$view->render();
	}

	private function renderSharedFlashcards(): void {
		$user = $this->requireUser();
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

	private function renderFlashcards(): void {
		$user = $this->requireUser();
		$filters = [
			'q' => trim($_GET['q'] ?? ''),
			'matiere' => trim($_GET['matiere'] ?? ''),
			'sort' => trim($_GET['sort'] ?? 'recent'),
		];

		$GLOBALS['currentUser'] = $user;
		$GLOBALS['flashcardFilters'] = $filters;

		try {
			$flashcardService = new FlashcardService();
			$allFlashcards = $flashcardService->findListForUser((int)$user->id);
			$GLOBALS['matiereOptions'] = $flashcardService->getMatiereOptions($allFlashcards);
			$GLOBALS['flashcards'] = $flashcardService->filterList($allFlashcards, $filters);
		} catch (\Throwable $e) {
			$GLOBALS['flashcards'] = [];
			$GLOBALS['matiereOptions'] = [];
			$GLOBALS['flashcardLoadError'] = 'Les fiches ne peuvent pas encore etre chargees. Verifie que les tables flashcards, shares et users existent dans la base.';
		}

		$view = new FlashcardsListView();
		$view->render();
	}

	private function renderFlashcardDetail(int $id): void {
		$user = $this->requireUser();

		if ($id <= 0) {
			header('Location: ?action=flashcards');
			exit;
		}

		try {
			$flashcardService = new FlashcardService();
			$flashcard = $flashcardService->findViewForUser($id, (int)$user->id);
		} catch (\Throwable $e) {
			$flashcard = null;
			$GLOBALS['flashcardDetailError'] = 'La fiche ne peut pas etre chargee pour le moment.';
		}

		if (!$flashcard) {
			header('Location: ?action=flashcards');
			exit;
		}

		$GLOBALS['currentUser'] = $user;
		$GLOBALS['flashcardDetail'] = $flashcard;

		$view = new FlashcardDetailView();
		$view->render();
	}

	private function renderFlashcardForm(?int $id = null, ?array $formData = null, array $errors = []): void {
		$user = $this->requireUser();
		$flashcardService = new FlashcardService();
		$isEdit = $id !== null && $id > 0;

		if ($formData === null) {
			if ($isEdit) {
				$formData = $flashcardService->findFormDataForUser((int)$id, (int)$user->id);
				if (!$formData) {
					header('Location: ?action=flashcards');
					exit;
				}
			} else {
				$formData = $flashcardService->emptyFormData();
			}
		}

		try {
			$options = $flashcardService->getFormOptions((int)$user->id);
		} catch (\Throwable $e) {
			$options = ['matieres' => [], 'users' => []];
			$errors['_form'] = 'Les options du formulaire ne peuvent pas etre chargees. Verifie les tables matieres et users.';
		}

		$GLOBALS['currentUser'] = $user;
		$GLOBALS['flashcardFormMode'] = $isEdit ? 'edit' : 'create';
		$GLOBALS['flashcardFormData'] = $formData;
		$GLOBALS['flashcardFormErrors'] = $errors;
		$GLOBALS['flashcardFormMatieres'] = $options['matieres'];
		$GLOBALS['flashcardFormUsers'] = $options['users'];

		$view = new FlashcardFormView();
		$view->render();
	}

	private function storeFlashcard(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=createFlashcard');
			exit;
		}

		$user = $this->requireUser();
		$flashcardService = new FlashcardService();

		try {
			$result = $flashcardService->createFromForm($_POST, (int)$user->id);
		} catch (\Throwable $e) {
			$result = [
				'success' => false,
				'errors' => ['_form' => 'Impossible de creer la fiche. Verifie que les tables flashcards, question_responses et shares existent.'],
				'data' => $_POST,
			];
		}

		if ($result['success']) {
			header('Location: ?action=flashcards');
			exit;
		}

		$this->renderFlashcardForm(null, $result['data'], $result['errors']);
	}

	private function updateFlashcard(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=flashcards');
			exit;
		}

		$user = $this->requireUser();
		$id = (int)($_POST['id'] ?? 0);
		if ($id <= 0) {
			header('Location: ?action=flashcards');
			exit;
		}

		$flashcardService = new FlashcardService();

		try {
			$result = $flashcardService->updateFromForm($id, $_POST, (int)$user->id);
		} catch (\Throwable $e) {
			$result = [
				'success' => false,
				'errors' => ['_form' => 'Impossible de modifier la fiche. Verifie que la fiche existe encore.'],
				'data' => $_POST,
			];
		}

		if ($result['success']) {
			header('Location: ?action=flashcards');
			exit;
		}

		$this->renderFlashcardForm($id, $result['data'], $result['errors']);
	}

	private function deleteFlashcard(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=flashcards');
			exit;
		}

		$user = $this->requireUser();
		$id = (int)($_POST['id'] ?? 0);

		if ($id <= 0) {
			header('Location: ?action=flashcards');
			exit;
		}

		$flashcardService = new FlashcardService();
		try {
			$flashcardService->deleteForUser($id, (int)$user->id);
		} catch (\Throwable $e) {
			$GLOBALS['flashcardLoadError'] = 'Impossible de supprimer cette fiche.';
		}

		header('Location: ?action=flashcards');
		exit;
	}

	private function recordRevision(): void {
		header('Content-Type: application/json');

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			http_response_code(405);
			echo json_encode(['success' => false]);
			return;
		}

		$user = $this->requireUser();
		$flashcardId = (int)($_POST['flashcard_id'] ?? 0);

		if ($flashcardId <= 0) {
			http_response_code(422);
			echo json_encode(['success' => false]);
			return;
		}

		try {
			$flashcardService = new FlashcardService();
			if (!$flashcardService->findViewForUser($flashcardId, (int)$user->id)) {
				http_response_code(403);
				echo json_encode(['success' => false]);
				return;
			}

			$statsService = new StatsService();
			$statsService->recordRevision((int)$user->id, $flashcardId);
			$weekCount = $statsService->countRevisionsSince((int)$user->id, new DateTimeImmutable('-7 days'));

			echo json_encode(['success' => true, 'weekCount' => $weekCount]);
		} catch (\Throwable $e) {
			http_response_code(500);
			echo json_encode(['success' => false]);
		}
	}

	private function createMatiere(): void {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			header('Location: ?action=matieres');
			exit;
		}

		$user = $this->requireUser();
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
		$user = $this->requireUser();
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

	private function loadSharedStatsForUser(int $userId): void {
		try {
			$shareRepository = new ShareRepository();
			$weekStart = new DateTimeImmutable('-7 days');
			$GLOBALS['sharedFlashcardsCount'] = $shareRepository->countSharedWithUser($userId);
			$GLOBALS['sharedFlashcardsWeekCount'] = $shareRepository->countSharedWithUserSince($userId, $weekStart);
		} catch (\Throwable $e) {
			$GLOBALS['sharedFlashcardsCount'] = 0;
			$GLOBALS['sharedFlashcardsWeekCount'] = 0;
			$GLOBALS['sharedFlashcardsStatsError'] = 'Les statistiques de partage ne peuvent pas encore etre chargees.';
		}
	}

	private function loadRevisionStatsForUser(int $userId): void {
		try {
			$statsService = new StatsService();
			$GLOBALS['weeklyRevisionCount'] = $statsService->countRevisionsSince($userId, new DateTimeImmutable('-7 days'));
		} catch (\Throwable $e) {
			$GLOBALS['weeklyRevisionCount'] = 0;
			$GLOBALS['revisionStatsError'] = 'Les statistiques de revision ne peuvent pas encore etre chargees.';
		}
	}

	private function requireUser(): User {
		$authService = new AuthService();
		$user = $authService->getCurrentUser();

		if (!$user) {
			header('Location: ?action=login');
			exit;
		}

		return $user;
	}
}
