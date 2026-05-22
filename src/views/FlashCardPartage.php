<?php
/**
 * Vue des flashcards partagees avec l'utilisateur connecte
 */
class FlashCardPartage {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$sharedFlashcards = $GLOBALS['sharedFlashcards'] ?? [];
		$sharedMatieres = $GLOBALS['sharedMatieres'] ?? [];
		$error = $GLOBALS['sharedFlashcardsError'] ?? null;
		$q = $_GET['q'] ?? '';
		$currentMatiere = $_GET['matiere'] ?? '';
		$currentSort = $_GET['sort'] ?? 'recent';
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Partagees avec moi - Gestionnaire de Revision</title>
			<link rel="stylesheet" href="/css/style.css">
		</head>
		<body class="home-body" data-session-timeout="1200">
			<main class="home-shell">
				<?php HomeNavView::render('partagees'); ?>

				<section class="home-content shared-page">
					<?php PageHeaderView::render($user, 'Partagees avec moi', "Retrouvez ici toutes les fiches de revision partagees par d'autres utilisateurs."); ?>

					<?php if ($error): ?>
						<div class="home-alert">
							<?= htmlspecialchars($error) ?>
						</div>
					<?php endif; ?>

					<form class="shared-filters" method="GET">
						<input type="hidden" name="action" value="partagees">
						<label class="shared-search">
							<span>⌕</span>
							<input type="search" name="q" placeholder="Rechercher une fiche..." value="<?= htmlspecialchars($q) ?>">
						</label>
						<select name="matiere" onchange="this.form.submit()">
							<option value="">Toutes les matieres</option>
							<?php foreach ($sharedMatieres as $matiere): ?>
								<option value="<?= htmlspecialchars($matiere) ?>" <?= $currentMatiere === $matiere ? 'selected' : '' ?>>
									<?= htmlspecialchars($matiere) ?>
								</option>
							<?php endforeach; ?>
						</select>
						<select name="sort" onchange="this.form.submit()">
							<option value="recent" <?= $currentSort === 'recent' ? 'selected' : '' ?>>Les plus recentes</option>
							<option value="oldest" <?= $currentSort === 'oldest' ? 'selected' : '' ?>>Les plus anciennes</option>
						</select>
					</form>

					<section class="shared-table-card">
						<div class="shared-table shared-table-head">
							<span>Titre</span>
							<span>Matiere</span>
							<span>Proprietaire</span>
							<span>Partage le</span>
							<span></span>
						</div>

						<?php if (empty($sharedFlashcards)): ?>
							<div class="shared-empty">
								<p>Aucune fiche partagee pour le moment.</p>
							</div>
						<?php else: ?>
							<?php foreach ($sharedFlashcards as $flashcard): ?>
								<?php $flashcardId = (int)($flashcard['flashcard_id'] ?? $flashcard['id'] ?? 0); ?>
								<a class="shared-table shared-row" href="?action=viewFlashcard&id=<?= $flashcardId ?>">
									<span class="shared-title-cell">
										<span class="shared-card-icon <?= htmlspecialchars($this->colorClass($flashcard['matiere_color'] ?? 'blue')) ?>">
											<?= htmlspecialchars($this->iconForMatiere($flashcard['matiere_name'] ?? '')) ?>
										</span>
										<span>
											<strong><?= htmlspecialchars($flashcard['title'] ?? 'Sans titre') ?></strong>
											<small><?= htmlspecialchars($this->excerpt($flashcard['subject'] ?? '')) ?></small>
										</span>
									</span>
									<span>
										<mark class="<?= htmlspecialchars($this->colorClass($flashcard['matiere_color'] ?? 'blue')) ?>">
											<?= htmlspecialchars($flashcard['matiere_name'] ?? 'Sans matiere') ?>
										</mark>
									</span>
									<span class="shared-owner-cell">
										<span class="owner-avatar <?= htmlspecialchars($this->colorClass($flashcard['matiere_color'] ?? 'blue')) ?>">
											<?= htmlspecialchars(strtoupper(substr($flashcard['owner_firstname'] ?? '?', 0, 1))) ?>
										</span>
										<span>
											<strong><?= htmlspecialchars(trim(($flashcard['owner_firstname'] ?? '') . ' ' . ($flashcard['owner_lastname'] ?? ''))) ?></strong>
											<small>@<?= htmlspecialchars($this->usernameFromEmail($flashcard['owner_email'] ?? '')) ?></small>
										</span>
									</span>
									<span><?= htmlspecialchars($this->formatDate($flashcard['shared_at'] ?? null)) ?></span>
									<span class="shared-view-link">Voir <span>›</span></span>
								</a>
							<?php endforeach; ?>
						<?php endif; ?>
					</section>
				</section>
			</main>
			<script src="/js/session-timeout.js"></script>
		</body>
		</html>
		<?php
	}

	private function excerpt(string $value): string {
		$value = trim($value);
		if ($value === '') return 'Aucun apercu disponible';
		return strlen($value) > 34 ? substr($value, 0, 34) . '...' : $value;
	}

	private function usernameFromEmail(string $email): string {
		return $email !== '' ? explode('@', $email)[0] : 'utilisateur';
	}

	private function colorClass(string $color): string {
		return in_array($color, ['teal', 'blue', 'green', 'orange', 'indigo'], true) ? $color : 'blue';
	}

	private function iconForMatiere(string $matiere): string {
		$lower = strtolower($matiere);
		if (str_contains($lower, 'python') || str_contains($lower, 'programmation')) return '</>';
		if (str_contains($lower, 'base') || str_contains($lower, 'sql')) return '▤';
		if (str_contains($lower, 'system')) return '⚙';
		if (str_contains($lower, 'math')) return 'fx';
		return '⌘';
	}

	private function formatDate(?string $date): string {
		if (!$date) return '';
		$timestamp = strtotime($date);
		if (!$timestamp) return $date;
		$months = ['janv.', 'fevr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'aout', 'sept.', 'oct.', 'nov.', 'dec.'];
		return (int)date('j', $timestamp) . ' ' . $months[(int)date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
	}
}
