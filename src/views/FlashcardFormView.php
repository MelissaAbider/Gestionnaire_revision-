<?php
/**
 * Vue du formulaire de creation/edition de fiche.
 */
class FlashcardFormView {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$firstName = $user?->firstName ?? 'Utilisateur';
		$mode = $GLOBALS['flashcardFormMode'] ?? 'create';
		$data = $GLOBALS['flashcardFormData'] ?? [];
		$errors = $GLOBALS['flashcardFormErrors'] ?? [];
		$matieres = $GLOBALS['flashcardFormMatieres'] ?? [];
		$users = $GLOBALS['flashcardFormUsers'] ?? [];
		$isEdit = $mode === 'edit';
		$action = $isEdit ? '?action=updateFlashcard' : '?action=storeFlashcard';
		$selectedUsers = array_map('intval', $data['sharedUserIds'] ?? []);
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?= $isEdit ? 'Modifier' : 'Créer' ?> une fiche - Gestionnaire de Révision</title>
			<link rel="stylesheet" href="/css/style.css">
			<script src="/js/script.js" defer></script>
		</head>
		<body class="home-body">
			<main class="home-shell flashcards-shell">
				<aside class="home-sidebar" aria-label="Navigation principale">
					<a class="home-brand flashmind-brand" href="?action=dashboard">
						<span class="brand-icon">F</span>
						<span class="brand-copy">
							<strong>FlashMind</strong>
							<small>Fiches de révision</small>
						</span>
					</a>

					<nav class="home-nav">
						<a href="?action=dashboard" class="nav-item">
							<span class="nav-icon">⌂</span>
							<span>Accueil</span>
						</a>
						<a href="?action=flashcards" class="nav-item active">
							<span class="nav-icon">□</span>
							<span>Mes fiches</span>
						</a>
						<a href="#" class="nav-item">
							<span class="nav-icon">↗</span>
							<span>Partagées avec moi</span>
						</a>
						<a href="?action=dashboard" class="nav-item">
							<span class="nav-icon">▣</span>
							<span>Matières</span>
						</a>
					</nav>

					<a href="?action=logout" class="nav-item logout-link">
						<span class="nav-icon">⇥</span>
						<span>Déconnexion</span>
					</a>
				</aside>

				<section class="home-content flashcards-content">
					<div class="page-user-menu" aria-label="Utilisateur connecté">
						<span class="user-avatar"><?= $this->e($this->initials($firstName)) ?></span>
						<span><?= $this->e($firstName) ?></span>
						<span class="user-chevron">⌄</span>
					</div>

					<header class="flashcard-form-header">
						<a class="back-link" href="?action=flashcards">‹ Mes fiches</a>
						<h1><?= $isEdit ? 'Modifier la fiche' : 'Créer une fiche' ?></h1>
						<p><?= $isEdit ? 'Mettez à jour le titre, le contenu et les utilisateurs partagés.' : 'Ajoutez une nouvelle fiche et choisissez avec qui la partager.' ?></p>
					</header>

					<form class="flashcard-editor" method="POST" action="<?= $this->e($action) ?>" novalidate>
						<?php if ($isEdit): ?>
							<input type="hidden" name="id" value="<?= (int)($data['id'] ?? 0) ?>">
						<?php endif; ?>

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

							<div class="form-field <?= isset($errors['content']) ? 'has-error' : '' ?>">
								<label for="flashcard-content">Contenu</label>
								<textarea
									id="flashcard-content"
									name="content"
									rows="9"
									placeholder="Saisissez le contenu de la fiche..."
									required
								><?= $this->e($data['content'] ?? '') ?></textarea>
								<?php if (isset($errors['content'])): ?>
									<span class="field-error"><?= $this->e($errors['content']) ?></span>
								<?php endif; ?>
							</div>
						</div>

						<div class="form-panel share-panel">
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
								<?= $isEdit ? 'Enregistrer' : 'Créer la fiche' ?>
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

	private function e(mixed $value): string {
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
}
