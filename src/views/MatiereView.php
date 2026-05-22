<?php
/**
 * Vue de gestion des matieres
 */
class MatiereView {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$firstName = $user?->firstName ?? 'Utilisateur';
		$matieres = $GLOBALS['matieres'] ?? [];
		$errors = $GLOBALS['matiereErrors'] ?? [];
		$loadError = $GLOBALS['matiereLoadError'] ?? null;
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Matieres - Gestionnaire de Revision</title>
			<link rel="stylesheet" href="/css/style.css">
		</head>
		<body class="home-body" data-session-timeout="1200">
			<main class="home-shell">
				<?php HomeNavView::render('matieres'); ?>

				<section class="home-content">
					<?php PageHeaderView::render($user, 'Mes matieres', 'Organisez vos matieres et gardez vos fiches bien rangees.'); ?>

					<?php if ($loadError): ?>
						<div class="home-alert">
							<?= htmlspecialchars($loadError) ?>
						</div>
					<?php endif; ?>

					<?php if (!empty($errors)): ?>
						<div class="home-alert">
							<?php foreach ($errors as $error): ?>
								<p><?= htmlspecialchars($error) ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="subjects-grid">
						<?php foreach ($matieres as $matiere): ?>
							<article class="subject-card">
								<div class="subject-card-header">
									<div>
										<h2><?= htmlspecialchars($matiere->name) ?></h2>
										<p><?= (int) $matiere->flashcardCount ?> fiches</p>
									</div>
								</div>
								<span class="subject-color <?= htmlspecialchars($matiere->color) ?>"></span>
								<form class="edit-subject-form" method="POST" action="?action=updateMatiere" data-matiere-form novalidate>
									<input type="hidden" name="id" value="<?= (int) $matiere->id ?>">
									<input type="text" name="name" value="<?= htmlspecialchars($matiere->name) ?>" aria-label="Nom de la matiere" required>
									<select name="color" aria-label="Couleur de la matiere">
										<option value="blue" <?= $matiere->color === 'blue' ? 'selected' : '' ?>>Bleu</option>
										<option value="teal" <?= $matiere->color === 'teal' ? 'selected' : '' ?>>Turquoise</option>
										<option value="green" <?= $matiere->color === 'green' ? 'selected' : '' ?>>Vert</option>
										<option value="orange" <?= $matiere->color === 'orange' ? 'selected' : '' ?>>Orange</option>
										<option value="indigo" <?= $matiere->color === 'indigo' ? 'selected' : '' ?>>Indigo</option>
									</select>
									<span class="subject-field-error" data-matiere-error></span>
									<button type="submit">Modifier</button>
								</form>
								<form method="POST" action="?action=deleteMatiere" onsubmit="return confirm('Supprimer cette matiere ? Les flashcards liees seront conservees sans matiere.');">
									<input type="hidden" name="id" value="<?= (int) $matiere->id ?>">
									<button class="delete-subject-button" type="submit">Supprimer</button>
								</form>
							</article>
						<?php endforeach; ?>

						<form class="add-subject-card" method="POST" action="?action=createMatiere" data-matiere-form novalidate>
							<span class="add-icon">+</span>
							<label for="matiere-name">Ajouter une matiere</label>
							<input type="text" id="matiere-name" name="name" placeholder="Nom de la matiere" required>
							<select name="color" aria-label="Couleur de la matiere">
								<option value="blue">Bleu</option>
								<option value="teal">Turquoise</option>
								<option value="green">Vert</option>
								<option value="orange">Orange</option>
								<option value="indigo">Indigo</option>
							</select>
							<span class="subject-field-error" data-matiere-error></span>
							<button type="submit">Creer</button>
						</form>
					</div>
				</section>
			</main>
			<script src="/js/script.js" defer></script>
			<script src="/js/session-timeout.js"></script>
		</body>
		</html>
		<?php
	}
}
