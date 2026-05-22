<?php
/**
 * Vue de la page Mes fiches.
 */
class FlashcardsListView {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$flashcards = $GLOBALS['flashcards'] ?? [];
		$sharedFlashcards = $GLOBALS['sharedFlashcards'] ?? [];
		$filters = $GLOBALS['flashcardFilters'] ?? ['q' => '', 'matiere' => '', 'sort' => 'recent'];
		$sharedFilters = $GLOBALS['sharedFlashcardFilters'] ?? ['q' => '', 'matiere' => '', 'sort' => 'recent'];
		$matiereOptions = $GLOBALS['matiereOptions'] ?? [];
		$sharedMatiereOptions = $GLOBALS['sharedMatiereOptions'] ?? [];
		$loadError = $GLOBALS['flashcardLoadError'] ?? null;
		$sharedError = $GLOBALS['sharedFlashcardsError'] ?? null;
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Mes fiches - Gestionnaire de Révision</title>
			<link rel="stylesheet" href="/css/style.css">
			<script src="/js/script.js" defer></script>
		</head>
		<body class="home-body">
			<main class="home-shell flashcards-shell">
				<?php HomeNavView::render('flashcards'); ?>

				<section class="home-content flashcards-content">
					<?php PageHeaderView::render($user, 'Mes fiches', 'Retrouvez et gérez toutes vos fiches de révision.'); ?>

					<?php if ($loadError || $sharedError): ?>
						<div class="home-alert">
							<?= $this->e(trim(($loadError ?? '') . ' ' . ($sharedError ?? ''))) ?>
						</div>
					<?php endif; ?>

					<div class="flashcards-board">
						<section class="revision-panel">
							<div class="revision-panel-header">
								<span class="revision-panel-icon purple">▤</span>
								<div>
									<h2>Mes fiches</h2>
									<p>Fiches que vous avez créées</p>
								</div>
								<a class="revision-add-button" href="?action=flashcardForm" aria-label="Créer une fiche">+</a>
							</div>

							<form class="revision-panel-filters" method="GET" action="">
								<input type="hidden" name="action" value="flashcards">
								<input type="hidden" name="shared_q" value="<?= $this->e($sharedFilters['q'] ?? '') ?>">
								<input type="hidden" name="shared_matiere" value="<?= $this->e($sharedFilters['matiere'] ?? '') ?>">
								<label class="revision-search" for="flashcard-search">
									<input
										type="search"
										id="flashcard-search"
										name="q"
										data-live-search="owned"
										value="<?= $this->e($filters['q'] ?? '') ?>"
										placeholder="Rechercher une fiche..."
									>
									<span>⌕</span>
								</label>
								<select name="matiere" aria-label="Filtrer mes fiches par matière" onchange="this.form.submit()">
									<option value="">Toutes les matières</option>
									<?php foreach ($matiereOptions as $matiereName): ?>
										<option value="<?= $this->e($matiereName) ?>" <?= ($filters['matiere'] ?? '') === $matiereName ? 'selected' : '' ?>>
											<?= $this->e($matiereName) ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button class="visually-hidden" type="submit">Rechercher</button>
							</form>

							<div class="revision-list" data-live-list="owned">
								<div class="revision-empty" data-live-empty="owned" <?= empty($flashcards) ? '' : 'hidden' ?>>Aucune fiche trouvée.</div>

								<?php foreach ($flashcards as $flashcard): ?>
									<?= $this->renderOwnedCard($flashcard) ?>
								<?php endforeach; ?>
							</div>
						</section>

						<section class="revision-panel">
							<div class="revision-panel-header">
								<span class="revision-panel-icon blue">👥</span>
								<div>
									<h2>Partagées avec moi</h2>
									<p>Fiches partagées par d'autres utilisateurs</p>
								</div>
							</div>

							<form class="revision-panel-filters" method="GET" action="">
								<input type="hidden" name="action" value="flashcards">
								<input type="hidden" name="q" value="<?= $this->e($filters['q'] ?? '') ?>">
								<input type="hidden" name="matiere" value="<?= $this->e($filters['matiere'] ?? '') ?>">
								<label class="revision-search" for="shared-flashcard-search">
									<input
										type="search"
										id="shared-flashcard-search"
										name="shared_q"
										data-live-search="shared"
										value="<?= $this->e($sharedFilters['q'] ?? '') ?>"
										placeholder="Rechercher une fiche..."
									>
									<span>⌕</span>
								</label>
								<select name="shared_matiere" aria-label="Filtrer les fiches partagées par matière" onchange="this.form.submit()">
									<option value="">Toutes les matières</option>
									<?php foreach ($sharedMatiereOptions as $matiereName): ?>
										<option value="<?= $this->e($matiereName) ?>" <?= ($sharedFilters['matiere'] ?? '') === $matiereName ? 'selected' : '' ?>>
											<?= $this->e($matiereName) ?>
										</option>
									<?php endforeach; ?>
								</select>
								<button class="visually-hidden" type="submit">Rechercher</button>
							</form>

							<div class="revision-list" data-live-list="shared">
								<div class="revision-empty" data-live-empty="shared" <?= empty($sharedFlashcards) ? '' : 'hidden' ?>>Aucune fiche trouvée.</div>

								<?php foreach ($sharedFlashcards as $flashcard): ?>
									<?= $this->renderSharedCard($flashcard) ?>
								<?php endforeach; ?>
							</div>
						</section>
					</div>
				</section>
			</main>
		</body>
		</html>
		<?php
	}

	private function renderOwnedCard(array $flashcard): string {
		$id = (int)($flashcard['id'] ?? 0);
		$matiereName = (string)($flashcard['matiere_name'] ?? 'Sans matière');
		$matiereColor = $this->colorClass((string)($flashcard['matiere_color'] ?? ''), $matiereName);

		return '
			<article class="revision-row owned-revision-row" data-live-card="owned" data-live-text="' . $this->e(($flashcard['title'] ?? '') . ' ' . $matiereName) . '">
				<a class="revision-row-main" href="?action=viewFlashcard&id=' . $id . '">
					<span class="revision-card-icon ' . $this->e($matiereColor) . '">' . $this->e($this->rowIcon($matiereName)) . '</span>
					<span class="revision-row-copy">
						<strong>' . $this->e($flashcard['title'] ?? 'Sans titre') . '</strong>
						<small><i class="' . $this->e($matiereColor) . '"></i>' . $this->e($matiereName) . '</small>
					</span>
				</a>
				<div class="revision-row-meta">
					<span class="revision-row-date">' . $this->e($this->ownedDateLabel($flashcard)) . '</span>
				</div>
				<div class="revision-row-actions">
					<a class="revision-view-button" href="?action=viewFlashcard&id=' . $id . '">Voir</a>
					<a href="?action=flashcardForm&id=' . $id . '" aria-label="Modifier ' . $this->e($flashcard['title'] ?? 'la fiche') . '">✎</a>
					<form method="POST" action="?action=deleteFlashcard" onsubmit="return confirm(\'Supprimer cette fiche de revision ?\');">
						<input type="hidden" name="id" value="' . $id . '">
						<button type="submit" aria-label="Supprimer ' . $this->e($flashcard['title'] ?? 'la fiche') . '">⌫</button>
					</form>
				</div>
			</article>';
	}

	private function ownedDateLabel(array $flashcard): string {
		$createdAt = $flashcard['created_at'] ?? null;
		$updatedAt = $flashcard['updated_at'] ?? null;

		if ($this->hasModificationDate($createdAt, $updatedAt)) {
			return 'Modifiée ' . $this->relativeDate((string)$updatedAt);
		}

		return 'Créée ' . $this->relativeDate(is_string($createdAt) ? $createdAt : null);
	}

	private function hasModificationDate(mixed $createdAt, mixed $updatedAt): bool {
		if (!is_string($updatedAt) || trim($updatedAt) === '') {
			return false;
		}

		if (!is_string($createdAt) || trim($createdAt) === '') {
			return true;
		}

		$createdTimestamp = strtotime($createdAt);
		$updatedTimestamp = strtotime($updatedAt);

		if ($createdTimestamp === false || $updatedTimestamp === false) {
			return $updatedAt !== $createdAt;
		}

		return $updatedTimestamp > $createdTimestamp;
	}

	private function renderSharedCard(array $flashcard): string {
		$id = (int)($flashcard['flashcard_id'] ?? $flashcard['id'] ?? 0);
		$ownerName = trim((string)($flashcard['owner_firstname'] ?? '') . ' ' . (string)($flashcard['owner_lastname'] ?? ''));
		$ownerLabel = $ownerName !== '' ? $ownerName : (string)($flashcard['owner_email'] ?? 'Utilisateur');
		$matiereName = (string)($flashcard['matiere_name'] ?? 'Sans matière');
		$matiereColor = $this->colorClass((string)($flashcard['matiere_color'] ?? ''), $matiereName);

		return '
			<article class="revision-row shared-revision-row" data-live-card="shared" data-live-text="' . $this->e(($flashcard['title'] ?? '') . ' ' . $ownerLabel . ' ' . $matiereName) . '">
				<a class="revision-row-main" href="?action=viewFlashcard&id=' . $id . '">
					<span class="revision-owner-avatar ' . $this->e($matiereColor) . '">' . $this->e($this->initials($ownerLabel)) . '</span>
					<span class="revision-row-copy">
						<strong>' . $this->e($flashcard['title'] ?? 'Sans titre') . '</strong>
						<small>' . $this->e($ownerLabel) . '</small>
						<small><i class="' . $this->e($matiereColor) . '"></i>' . $this->e($matiereName) . '</small>
					</span>
				</a>
				<span class="revision-row-date">Partagée le ' . $this->e($this->formatDate($flashcard['shared_at'] ?? null)) . '</span>
				<a class="revision-view-button" href="?action=viewFlashcard&id=' . $id . '">Voir</a>
			</article>';
	}

	private function relativeDate(?string $date): string {
		if (!$date) {
			return 'récemment';
		}

		try {
			$value = new DateTime($date);
			$now = new DateTime();
		} catch (\Throwable $e) {
			return 'récemment';
		}

		$days = max(0, (int)$value->diff($now)->format('%a'));
		if ($days === 0) {
			return 'aujourd\'hui';
		}

		if ($days === 1) {
			return 'il y a 1 j';
		}

		if ($days < 7) {
			return 'il y a ' . $days . ' j';
		}

		$weeks = (int)floor($days / 7);
		return 'il y a ' . $weeks . ' sem.';
	}

	private function formatDate(?string $date): string {
		if (!$date) {
			return '-';
		}

		try {
			$value = new DateTime($date);
		} catch (\Throwable $e) {
			return '-';
		}

		return $value->format('d/m/Y');
	}

	private function colorClass(string $color, string $name): string {
		$allowed = ['blue', 'teal', 'green', 'orange', 'indigo'];
		if (in_array($color, $allowed, true)) {
			return $color;
		}

		if ($name === '') {
			return 'blue';
		}

		return $allowed[abs(crc32($name)) % count($allowed)];
	}

	private function rowIcon(string $matiereName): string {
		$lower = mb_strtolower($matiereName);
		if (str_contains($lower, 'math')) return '√x';
		if (str_contains($lower, 'réseau') || str_contains($lower, 'reseau')) return '◎';
		if (str_contains($lower, 'programm')) return '</>';
		if (str_contains($lower, 'base') || str_contains($lower, 'sql')) return '▤';
		if (str_contains($lower, 'système') || str_contains($lower, 'systeme')) return '⚙';

		$firstLetter = mb_substr(trim($matiereName), 0, 1);
		return $firstLetter !== '' ? mb_strtoupper($firstLetter) : 'F';
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
