<?php
/**
 * Vue du formulaire de creation/edition de fiche.
 *
 * RESPONSABLE PRINCIPAL : Asma AZRI
 * Perimetre : formulaire de creation/modification des fiches et questions/reponses.
 * Point de contact : Alban COUSIN pour le bloc de partage.
 */
class FlashcardFormView {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$mode = $GLOBALS['flashcardFormMode'] ?? 'create';
		$data = $GLOBALS['flashcardFormData'] ?? [];
		$errors = $GLOBALS['flashcardFormErrors'] ?? [];
		$matieres = $GLOBALS['flashcardFormMatieres'] ?? [];
		$users = $GLOBALS['flashcardFormUsers'] ?? [];
		$isEdit = $mode === 'edit';
		$action = '?action=flashcardForm';
		$selectedUsers = array_map('intval', $data['sharedUserIds'] ?? []);
		$questionResponses = $data['questionResponses'] ?? $this->questionResponsesFromPostArrays($data);
		if (empty($questionResponses)) {
			$questionResponses = [['question' => '', 'response' => '']];
		}
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Fiche de révision - Gestionnaire de Révision</title>
			<link rel="stylesheet" href="/css/style.css">
			<script src="/js/script.js" defer></script>
		</head>
		<body class="home-body">
			<main class="home-shell flashcards-shell">
				<?php HomeNavView::render('flashcards'); ?>

				<section class="home-content flashcards-content">
					<?php
					PageHeaderView::render(
						$user,
						'Fiche de révision',
						'Renseignez le titre, les questions/reponses et les utilisateurs partages.',
						'',
						'?action=flashcards',
						'Mes fiches'
					);
					?>

					<form class="flashcard-editor" method="POST" action="<?= $this->e($action) ?>" data-flashcard-form novalidate>
						<input type="hidden" name="id" value="<?= $isEdit ? (int)($data['id'] ?? 0) : '' ?>">

						<?php if (!empty($errors['_form'])): ?>
							<div class="form-error-banner"><?= $this->e($errors['_form']) ?></div>
						<?php endif; ?>

						<div class="form-panel">
							<div class="form-field <?= isset($errors['title']) ? 'has-error' : '' ?>">
								<label for="flashcard-title">Titre</label>
								<input
									type="text"
									id="flashcard-title"
									name="title"
									maxlength="150"
									value="<?= $this->e($data['title'] ?? '') ?>"
									placeholder="Ex. Le modèle OSI"
									required
								>
								<div class="field-meta">
									<?php if (isset($errors['title'])): ?>
										<span class="field-error"><?= $this->e($errors['title']) ?></span>
									<?php else: ?>
										<span>150 caractères maximum</span>
									<?php endif; ?>
									<span data-title-count>0/150</span>
								</div>
								<span class="field-error" data-error-for="title"></span>
							</div>

							<div class="form-field">
								<label for="flashcard-matiere">Matière</label>
								<select id="flashcard-matiere" name="matiereId">
									<option value="">Sans matière</option>
									<?php foreach ($matieres as $matiere): ?>
										<option value="<?= (int)$matiere->id ?>" <?= (string)($data['matiereId'] ?? '') === (string)$matiere->id ? 'selected' : '' ?>>
											<?= $this->e($matiere->name) ?>
										</option>
									<?php endforeach; ?>
								</select>
								<?php if (isset($errors['matiereId'])): ?>
									<span class="field-error"><?= $this->e($errors['matiereId']) ?></span>
								<?php endif; ?>
							</div>

							<div class="form-field <?= isset($errors['questionResponses']) ? 'has-error' : '' ?>">
								<div class="question-response-heading">
									<label>Questions / réponses</label>
									<button class="secondary-button compact-button" type="button" data-add-qa>Ajouter</button>
								</div>

								<div class="question-response-list" data-qa-list>
									<?php foreach ($questionResponses as $index => $questionResponse): ?>
										<div class="question-response-item" data-qa-item>
											<div class="question-response-item-header">
												<strong>Carte <?= (int)$index + 1 ?></strong>
												<button class="remove-qa-button" type="button" data-remove-qa>Supprimer</button>
											</div>

											<label for="flashcard-question-<?= (int)$index ?>">Question</label>
											<textarea
												id="flashcard-question-<?= (int)$index ?>"
												name="questions[]"
												rows="3"
												placeholder="Ex. À quoi sert le modèle OSI ?"
												required
											><?= $this->e($questionResponse['question'] ?? '') ?></textarea>

											<label for="flashcard-response-<?= (int)$index ?>">Réponse</label>
											<textarea
												id="flashcard-response-<?= (int)$index ?>"
												name="responses[]"
												rows="4"
												placeholder="Saisissez la réponse attendue..."
												required
											><?= $this->e($questionResponse['response'] ?? '') ?></textarea>
										</div>
									<?php endforeach; ?>
								</div>

								<?php if (isset($errors['questionResponses'])): ?>
									<span class="field-error"><?= $this->e($errors['questionResponses']) ?></span>
								<?php endif; ?>
								<span class="field-error" data-error-for="questionResponses"></span>
							</div>
						</div>

						<div class="form-panel share-panel">
							<?php // RESPONSABLE : Alban COUSIN - selection des utilisateurs avec qui partager la fiche. ?>
							<div class="share-panel-header">
								<div>
									<h2>Partage</h2>
									<p>Sélectionnez les utilisateurs qui pourront accéder à cette fiche.</p>
								</div>
								<span data-share-selected-count><?= count($selectedUsers) ?> sélectionné<?= count($selectedUsers) > 1 ? 's' : '' ?></span>
							</div>

							<label class="share-search" for="share-user-search">
								<span>⌕</span>
								<input type="search" id="share-user-search" placeholder="Rechercher un utilisateur...">
							</label>

							<div class="share-user-list">
								<?php if (empty($users)): ?>
									<div class="empty-share-users">Aucun autre utilisateur disponible pour le partage.</div>
								<?php endif; ?>

								<?php foreach ($users as $shareUser): ?>
									<?php
									$name = trim(($shareUser['firstname'] ?? '') . ' ' . ($shareUser['lastname'] ?? ''));
									$label = $name !== '' ? $name : ($shareUser['email'] ?? 'Utilisateur');
									$searchValue = mb_strtolower($label . ' ' . ($shareUser['email'] ?? ''));
									?>
									<label class="share-user-option" data-share-user="<?= $this->e($searchValue) ?>">
										<input
											type="checkbox"
											name="sharedUserIds[]"
											value="<?= (int)$shareUser['id'] ?>"
											<?= in_array((int)$shareUser['id'], $selectedUsers, true) ? 'checked' : '' ?>
										>
										<span class="share-avatar color-1"><?= $this->e($this->initials($label)) ?></span>
										<span>
											<strong><?= $this->e($label) ?></strong>
											<small><?= $this->e($shareUser['email'] ?? '') ?></small>
										</span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="form-actions">
							<a class="secondary-button" href="?action=flashcards">Annuler</a>
							<button class="primary-button" type="submit">
								Enregistrer la fiche
							</button>
						</div>
					</form>
				</section>
			</main>
		</body>
		</html>
		<?php
	}

	private function initials(string $value): string {
		$parts = preg_split('/\s+/', trim($value));
		$initials = '';

		foreach ($parts ?: [] as $part) {
			if ($part !== '') {
				$initials .= mb_substr($part, 0, 1);
			}

			if (mb_strlen($initials) >= 2) {
				break;
			}
		}

		return mb_strtoupper($initials !== '' ? $initials : 'U');
	}

	private function questionResponsesFromPostArrays(array $data): array {
		$questions = $data['questions'] ?? [];
		$responses = $data['responses'] ?? [];

		if (!is_array($questions) || !is_array($responses)) {
			return [['question' => '', 'response' => '']];
		}

		$questionResponses = [];
		$count = max(count($questions), count($responses));

		for ($index = 0; $index < $count; $index++) {
			$questionResponses[] = [
				'question' => (string)($questions[$index] ?? ''),
				'response' => (string)($responses[$index] ?? ''),
			];
		}

		return $questionResponses;
	}

	private function e(mixed $value): string {
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
}
