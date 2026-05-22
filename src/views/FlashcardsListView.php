<?php
/**
 * Vue de la page Mes fiches.
 */
class FlashcardsListView {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$flashcards = $GLOBALS['flashcards'] ?? [];
		$filters = $GLOBALS['flashcardFilters'] ?? ['q' => '', 'matiere' => '', 'sort' => 'recent'];
		$matiereOptions = $GLOBALS['matiereOptions'] ?? [];
		$loadError = $GLOBALS['flashcardLoadError'] ?? null;
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
					<?php PageHeaderView::render($user, 'Mes fiches', 'Retrouvez ici toutes les fiches que vous avez creees.'); ?>

					<div class="flashcards-create-row">
						<a class="create-flashcard-button" href="?action=createFlashcard">
							<span>+</span>
							<span>Créer une fiche</span>
						</a>
					</div>

					<?php if ($loadError): ?>
						<div class="home-alert">
							<?= $this->e($loadError) ?>
						</div>
					<?php endif; ?>

					<form class="flashcards-toolbar" method="GET" action="">
						<input type="hidden" name="action" value="flashcards">
						<label class="search-control" for="flashcard-search">
							<span>⌕</span>
							<input
								type="search"
								id="flashcard-search"
								name="q"
								value="<?= $this->e($filters['q'] ?? '') ?>"
								placeholder="Rechercher une fiche..."
							>
						</label>

						<div class="table-filters">
							<select name="matiere" aria-label="Filtrer par matière" onchange="this.form.submit()">
								<option value="">Toutes les matières</option>
								<?php foreach ($matiereOptions as $matiereName): ?>
									<option value="<?= $this->e($matiereName) ?>" <?= ($filters['matiere'] ?? '') === $matiereName ? 'selected' : '' ?>>
										<?= $this->e($matiereName) ?>
									</option>
								<?php endforeach; ?>
							</select>

							<select name="sort" aria-label="Trier les fiches" onchange="this.form.submit()">
								<option value="recent" <?= ($filters['sort'] ?? 'recent') === 'recent' ? 'selected' : '' ?>>Les plus récentes</option>
								<option value="oldest" <?= ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Les plus anciennes</option>
								<option value="title" <?= ($filters['sort'] ?? '') === 'title' ? 'selected' : '' ?>>Titre A-Z</option>
							</select>
						</div>

						<button class="visually-hidden" type="submit">Rechercher</button>
					</form>

					<div class="flashcards-table-card">
						<table class="flashcards-table">
							<thead>
								<tr>
									<th>Titre</th>
									<th>Matière</th>
									<th>Créée le</th>
									<th>Partagée avec</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($flashcards)): ?>
									<tr>
										<td colspan="5">
											<div class="empty-flashcards">
												<strong>Aucune fiche trouvée</strong>
												<span>Vos fiches apparaîtront ici dès qu'elles seront créées.</span>
											</div>
										</td>
									</tr>
								<?php endif; ?>

								<?php foreach ($flashcards as $flashcard): ?>
									<?php
									$matiereColor = $this->colorClass($flashcard['matiere_color'] ?? '', $flashcard['matiere_name'] ?? '');
									$subtitle = $this->subtitle($flashcard);
									?>
									<tr data-flashcard-row data-flashcard-title="<?= $this->e($flashcard['title'] ?? '') ?>">
										<td>
											<div class="flashcard-title-cell">
												<span class="flashcard-row-icon <?= $this->e($matiereColor) ?>">
													<?= $this->e($this->rowIcon($flashcard['matiere_name'] ?? '')) ?>
												</span>
												<div>
													<strong><?= $this->e($flashcard['title'] ?? 'Sans titre') ?></strong>
													<?php if ($subtitle !== ''): ?>
														<p><?= $this->e($subtitle) ?></p>
													<?php endif; ?>
												</div>
											</div>
										</td>
										<td>
											<span class="matiere-pill <?= $this->e($matiereColor) ?>">
												<?= $this->e($flashcard['matiere_name'] ?? 'Sans matière') ?>
											</span>
										</td>
										<td class="date-cell"><?= $this->e($this->formatDate($flashcard['created_at'] ?? null)) ?></td>
										<td>
											<?= $this->renderShares($flashcard['shared_with'] ?? []) ?>
										</td>
										<td>
											<div class="action-buttons">
												<a href="?action=viewFlashcard&id=<?= (int)($flashcard['id'] ?? 0) ?>" class="action-button view" aria-label="Voir <?= $this->e($flashcard['title'] ?? 'la fiche') ?>">◉</a>
												<a href="?action=editFlashcard&id=<?= (int)($flashcard['id'] ?? 0) ?>" class="action-button edit" aria-label="Modifier <?= $this->e($flashcard['title'] ?? 'la fiche') ?>">✎</a>
												<form class="inline-delete-form" method="POST" action="?action=deleteFlashcard" onsubmit="return confirm('Supprimer cette fiche de revision ?');">
													<input type="hidden" name="id" value="<?= (int)($flashcard['id'] ?? 0) ?>">
													<button type="submit" class="action-button delete" aria-label="Supprimer <?= $this->e($flashcard['title'] ?? 'la fiche') ?>">⌫</button>
												</form>
											</div>
										</td>
									</tr>
								<?php endforeach; ?>
								<tr class="search-empty-row" data-search-empty hidden>
									<td colspan="5">
										<div class="empty-flashcards">
											<strong>Aucune fiche trouvée</strong>
											<span>Aucune fiche ne correspond à cette recherche.</span>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</section>
			</main>
		</body>
		</html>
		<?php
	}

	private function renderShares(array $shares): string {
		if (empty($shares)) {
			return '<div class="share-stack"><span class="share-add">+</span></div>';
		}

		$visibleShares = array_slice($shares, 0, 3);
		$hiddenCount = count($shares) - count($visibleShares);
		$html = '<div class="share-stack">';

		foreach ($visibleShares as $index => $share) {
			$name = trim(($share['firstname'] ?? '') . ' ' . ($share['lastname'] ?? ''));
			$label = $name !== '' ? $name : ($share['email'] ?? 'Utilisateur partagé');
			$html .= '<span class="share-avatar color-' . (($index % 4) + 1) . '" title="' . $this->e($label) . '">'
				. $this->e($this->initials($label))
				. '</span>';
		}

		if ($hiddenCount > 0) {
			$html .= '<span class="share-avatar more">+' . $hiddenCount . '</span>';
		}

		$html .= '</div>';

		return $html;
	}

	private function subtitle(array $flashcard): string {
		$parts = array_filter([
			trim((string)($flashcard['theme'] ?? '')),
			trim((string)($flashcard['subject'] ?? '')),
		]);

		$text = implode(' - ', array_unique($parts));
		if (mb_strlen($text) > 58) {
			return mb_substr($text, 0, 55) . '...';
		}

		return $text;
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

		$months = [
			1 => 'janv.',
			2 => 'févr.',
			3 => 'mars',
			4 => 'avr.',
			5 => 'mai',
			6 => 'juin',
			7 => 'juil.',
			8 => 'août',
			9 => 'sept.',
			10 => 'oct.',
			11 => 'nov.',
			12 => 'déc.',
		];

		return (int)$value->format('j') . ' ' . $months[(int)$value->format('n')] . ' ' . $value->format('Y');
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
