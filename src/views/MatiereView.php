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
				<aside class="home-sidebar" aria-label="Navigation principale">
					<a class="home-brand" href="?action=dashboard">
						<span class="brand-icon">F</span>
						<span>FlashMind</span>
					</a>

					<nav class="home-nav">
						<a href="?action=dashboard" class="nav-item">
							<span class="nav-icon">⌂</span>
							<span>Accueil</span>
						</a>
						<a href="#" class="nav-item">
							<span class="nav-icon">□</span>
							<span>Mes fiches</span>
						</a>
						<a href="?action=partagees" class="nav-item">
							<span class="nav-icon">↗</span>
							<span>Partagees avec moi</span>
						</a>
						<a href="?action=matieres" class="nav-item active">
							<span class="nav-icon">▣</span>
							<span>Matieres</span>
						</a>
					</nav>

					<a href="?action=logout" class="nav-item logout-link">
						<span class="nav-icon">⇥</span>
						<span>Deconnexion</span>
					</a>
				</aside>

				<section class="home-content">
					<header class="home-header">
						<div>
							<p class="home-kicker">Bonjour <?= htmlspecialchars($firstName) ?></p>
							<h1>Mes matieres</h1>
						</div>
					</header>

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
								<form class="edit-subject-form" method="POST" action="?action=updateMatiere">
									<input type="hidden" name="id" value="<?= (int) $matiere->id ?>">
									<input type="text" name="name" value="<?= htmlspecialchars($matiere->name) ?>" aria-label="Nom de la matiere" required>
									<select name="color" aria-label="Couleur de la matiere">
										<option value="blue" <?= $matiere->color === 'blue' ? 'selected' : '' ?>>Bleu</option>
										<option value="teal" <?= $matiere->color === 'teal' ? 'selected' : '' ?>>Turquoise</option>
										<option value="green" <?= $matiere->color === 'green' ? 'selected' : '' ?>>Vert</option>
										<option value="orange" <?= $matiere->color === 'orange' ? 'selected' : '' ?>>Orange</option>
										<option value="indigo" <?= $matiere->color === 'indigo' ? 'selected' : '' ?>>Indigo</option>
									</select>
									<button type="submit">Modifier</button>
								</form>
								<form method="POST" action="?action=deleteMatiere" onsubmit="return confirm('Supprimer cette matiere ? Les flashcards liees seront conservees sans matiere.');">
									<input type="hidden" name="id" value="<?= (int) $matiere->id ?>">
									<button class="delete-subject-button" type="submit">Supprimer</button>
								</form>
							</article>
						<?php endforeach; ?>

						<form class="add-subject-card" method="POST" action="?action=createMatiere">
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
							<button type="submit">Creer</button>
						</form>
					</div>
				</section>
			</main>
			<script src="/js/session-timeout.js"></script>
		</body>
		</html>
		<?php
	}
}
