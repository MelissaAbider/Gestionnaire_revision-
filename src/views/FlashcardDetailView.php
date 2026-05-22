<?php
/**
 * Vue de visualisation d'une fiche de revision.
 */
class FlashcardDetailView {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$flashcard = $GLOBALS['flashcardDetail'] ?? [];
		$questions = $flashcard['question_responses'] ?? [];
		$firstCard = $questions[0] ?? ['question' => 'Aucune question disponible.', 'response' => ''];
		$cards = !empty($questions) ? $questions : [$firstCard];
		$cardsJson = htmlspecialchars(json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
		$isOwner = !empty($flashcard['is_owner']);
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?= $this->e($flashcard['title'] ?? 'Fiche') ?> - Gestionnaire de Révision</title>
			<link rel="stylesheet" href="/css/style.css">
			<script src="/js/script.js" defer></script>
		</head>
		<body class="home-body">
			<main class="home-shell flashcards-shell">
				<?php HomeNavView::render('flashcards'); ?>

				<section class="home-content flashcards-content">
					<?php
					ob_start();
					if ($isOwner):
					?>
						<div class="detail-owner-actions">
							<a href="?action=flashcardForm&id=<?= (int)($flashcard['id'] ?? 0) ?>" class="secondary-button">Modifier</a>
							<form method="POST" action="?action=deleteFlashcard" onsubmit="return confirm('Supprimer cette fiche de revision ?');">
								<input type="hidden" name="id" value="<?= (int)($flashcard['id'] ?? 0) ?>">
								<button class="danger-button" type="submit">Supprimer</button>
							</form>
						</div>
					<?php
					endif;
					$detailActions = ob_get_clean();
					PageHeaderView::render(
						$user,
						$flashcard['title'] ?? 'Sans titre',
						$flashcard['matiere_name'] ?? 'Sans matiere',
						$detailActions,
						'?action=flashcards',
						'Mes fiches'
					);
					?>

					<div class="flashcard-detail-grid">
						<section class="flashcard-info-panel">
							<h2>Informations</h2>
							<div class="detail-info-grid">
								<div>
									<span>Propriétaire</span>
									<strong><?= $this->e($flashcard['owner_name'] ?? 'Utilisateur') ?></strong>
								</div>
								<div>
									<span>Cartes</span>
									<strong><?= count($questions) ?></strong>
								</div>
								<div>
									<span>Créée le</span>
									<strong><?= $this->e($this->formatDate($flashcard['created_at'] ?? null)) ?></strong>
								</div>
								<div>
									<span>Modifiée le</span>
									<strong><?= $this->e($this->formatModificationDate($flashcard['created_at'] ?? null, $flashcard['updated_at'] ?? null)) ?></strong>
								</div>
							</div>

							<div class="detail-shares">
								<span>Partagée avec</span>
								<?= $this->renderShares($flashcard['shared_with'] ?? []) ?>
							</div>
						</section>

						<section class="study-card-panel">
							<div class="study-card-heading">
								<h2>Première carte</h2>
								<span>Cliquez sur la carte</span>
							</div>

							<button
								class="revision-card"
								type="button"
								data-revision-card
								data-flashcard-id="<?= (int)($flashcard['id'] ?? 0) ?>"
								data-cards="<?= $cardsJson ?>"
								data-question="<?= $this->e($firstCard['question'] ?? '') ?>"
								data-response="<?= $this->e($firstCard['response'] ?? '') ?>"
							>
								<span data-card-side>Question</span>
								<strong data-card-text><?= $this->e($firstCard['question'] ?? 'Aucune question disponible.') ?></strong>
							</button>

							<div class="study-card-controls" aria-label="Navigation entre les cartes">
								<button type="button" data-card-prev <?= count($cards) <= 1 ? 'disabled' : '' ?> aria-label="Carte précédente">‹</button>
								<span data-card-counter>1 / <?= max(1, count($cards)) ?></span>
								<button type="button" data-card-next <?= count($cards) <= 1 ? 'disabled' : '' ?> aria-label="Carte suivante">›</button>
							</div>
						</section>
					</div>
				</section>
			</main>
		</body>
		</html>
		<?php
	}

	private function renderShares(array $shares): string {
		if (empty($shares)) {
			return '<p>Aucun partage pour cette fiche.</p>';
		}

		$html = '<div class="detail-share-list">';
		foreach ($shares as $share) {
			$name = trim(($share['firstname'] ?? '') . ' ' . ($share['lastname'] ?? ''));
			$label = $name !== '' ? $name : ($share['email'] ?? 'Utilisateur');
			$html .= '<span>' . $this->e($label) . '</span>';
		}
		$html .= '</div>';

		return $html;
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

	private function formatModificationDate(?string $createdAt, ?string $updatedAt): string {
		if (!$updatedAt || !$this->hasModificationDate($createdAt, $updatedAt)) {
			return '-';
		}

		return $this->formatDate($updatedAt);
	}

	private function hasModificationDate(?string $createdAt, ?string $updatedAt): bool {
		if (!$updatedAt) {
			return false;
		}

		if (!$createdAt) {
			return true;
		}

		$createdTimestamp = strtotime($createdAt);
		$updatedTimestamp = strtotime($updatedAt);

		if ($createdTimestamp === false || $updatedTimestamp === false) {
			return $updatedAt !== $createdAt;
		}

		return $updatedTimestamp > $createdTimestamp;
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
