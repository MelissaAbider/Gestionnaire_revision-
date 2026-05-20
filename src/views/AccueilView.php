<?php
/**
 * Vue d'accueil connectee
 */
class AccueilView {
	public function render(): void {
		$user = $GLOBALS['currentUser'] ?? null;
		$firstName = $user?->firstName ?: 'Utilisateur';
		$lastName = $user?->lastName ?? '';
		$initial = strtoupper(substr($firstName, 0, 1));
		$matieres = $GLOBALS['matieres'] ?? [];
		$loadError = $GLOBALS['matiereLoadError'] ?? null;
		$activities = $GLOBALS['recentActivities'] ?? [];
		$activityError = $GLOBALS['recentActivitiesError'] ?? null;
		$totalMatieres = count($matieres);
		$totalFlashcards = array_reduce(
			$matieres,
			fn(int $total, Matiere $matiere): int => $total + $matiere->flashcardCount,
			0
		);
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Accueil - Gestionnaire de Révision</title>
			<link rel="stylesheet" href="/css/style.css">
		</head>
		<body class="home-body dashboard-home" data-session-timeout="1200">
			<main class="home-shell">
				<aside class="home-sidebar" aria-label="Navigation principale">
					<a class="home-brand" href="?action=dashboard">
						<span class="brand-icon">F</span>
						<span>FlashMind</span>
					</a>

					<nav class="home-nav">
						<a href="?action=dashboard" class="nav-item active">
							<span class="nav-icon">⌂</span>
							<span>Accueil</span>
						</a>
						<a href="?action=flashcards" class="nav-item">
							<span class="nav-icon">□</span>
							<span>Mes fiches</span>
						</a>
						<a href="?action=partagees" class="nav-item">
							<span class="nav-icon">↗</span>
							<span>Partagées avec moi</span>
						</a>
						<a href="?action=matieres" class="nav-item">
							<span class="nav-icon">▣</span>
							<span>Matières</span>
						</a>
					</nav>

					<a href="?action=logout" class="nav-item logout-link">
						<span class="nav-icon">⇥</span>
						<span>Déconnexion</span>
					</a>
				</aside>

				<section class="home-content dashboard-main">
					<header class="dashboard-topbar">
						<div>
							<h1>Bonjour, <?= htmlspecialchars($firstName) ?> !</h1>
							<p>Pret a continuer vos revisions aujourd'hui ?</p>
						</div>
						<div class="dashboard-user">
							<span class="notification-dot" aria-label="Notifications">!</span>
							<span class="user-avatar"><?= htmlspecialchars($initial) ?></span>
							<strong><?= htmlspecialchars(trim($firstName . ' ' . $lastName)) ?></strong>
							<span>⌄</span>
						</div>
					</header>

					<?php if ($loadError): ?>
						<div class="home-alert">
							<?= htmlspecialchars($loadError) ?>
						</div>
					<?php endif; ?>

					<section class="dashboard-stats" aria-label="Resume de votre espace">
						<article class="stat-card">
							<span class="stat-icon purple">□</span>
							<div>
								<p>Mes fiches</p>
								<strong><?= (int) $totalFlashcards ?></strong>
								<small>+<?= min(12, (int) $totalFlashcards) ?> cette semaine</small>
							</div>
						</article>
						<article class="stat-card">
							<span class="stat-icon green">♧</span>
							<div>
								<p>Partagees avec moi</p>
								<strong>0</strong>
								<small>+0 cette semaine</small>
							</div>
						</article>
						<article class="stat-card">
							<span class="stat-icon orange">◎</span>
							<div>
								<p>Matieres</p>
								<strong><?= (int) $totalMatieres ?></strong>
								<small>Toutes vos matieres</small>
							</div>
						</article>
						<article class="stat-card">
							<span class="stat-icon blue">↗</span>
							<div>
								<p>Revisions cette semaine</p>
								<strong><?= (int) $totalFlashcards ?></strong>
								<small>+0% vs la semaine derniere</small>
							</div>
						</article>
					</section>

					<div class="dashboard-panels">
						<section class="activity-panel">
							<div class="panel-title-row">
								<h2>Activite recente</h2>
								<a href="#">Voir tout</a>
							</div>

							<?php if ($activityError): ?>
								<div class="activity-empty"><?= htmlspecialchars($activityError) ?></div>
							<?php elseif (empty($activities)): ?>
								<div class="activity-empty">Aucune activite recente pour le moment.</div>
							<?php else: ?>
								<div class="activity-list">
									<?php foreach ($activities as $activity): ?>
										<article class="activity-item">
											<span class="activity-icon <?= htmlspecialchars($this->colorClass($activity['matiere_color'] ?? 'blue')) ?>">
												<?= htmlspecialchars($this->iconForMatiere($activity['matiere_name'] ?? ($activity['subject'] ?? ''))) ?>
											</span>
											<div>
												<h3><?= htmlspecialchars($activity['title'] ?? 'Sans titre') ?></h3>
												<p><?= htmlspecialchars($this->activityLabel($activity)) ?></p>
											</div>
											<time><?= htmlspecialchars($this->relativeTime($activity['updated_at'] ?? $activity['created_at'] ?? null)) ?></time>
										</article>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<a class="panel-bottom-link" href="#">Voir toute l'activite</a>
						</section>

						<section class="quick-panel">
							<h2>Actions rapides</h2>
							<div class="quick-list">
								<a class="quick-item" href="#">
									<span class="quick-icon purple">+</span>
									<span>
										<strong>Creer une fiche</strong>
										<small>Ajoutez une nouvelle fiche de revision</small>
									</span>
									<em>›</em>
								</a>
								<a class="quick-item" href="?action=matieres">
									<span class="quick-icon green">▰</span>
									<span>
										<strong>Parcourir mes matieres</strong>
										<small>Voir toutes vos matieres</small>
									</span>
									<em>›</em>
								</a>
								<a class="quick-item" href="?action=partagees">
									<span class="quick-icon orange">♧</span>
									<span>
										<strong>Fiches partagees avec moi</strong>
										<small>Decouvrir les fiches partagees</small>
									</span>
									<em>›</em>
								</a>
								<a class="quick-item" href="#">
									<span class="quick-icon blue">▦</span>
									<span>
										<strong>Revisions du jour</strong>
										<small>Commencer vos revisions</small>
									</span>
									<em>›</em>
								</a>
							</div>
						</section>
					</div>

					<p class="dashboard-motto">La regularite est la cle de la reussite. Continuez comme ca.</p>
				</section>
			</main>
			<script src="/js/session-timeout.js"></script>
		</body>
		</html>
		<?php
	}

	private function activityLabel(array $activity): string {
		$createdAt = strtotime($activity['created_at'] ?? '');
		$updatedAt = strtotime($activity['updated_at'] ?? '');
		$matiere = $activity['matiere_name'] ?? '';
		$action = $createdAt && $updatedAt && abs($updatedAt - $createdAt) <= 2 ? 'Fiche creee' : 'Fiche modifiee';

		return trim($matiere) !== '' ? $action . ' - ' . $matiere : $action;
	}

	private function relativeTime(?string $date): string {
		if (!$date) return '';

		$timestamp = strtotime($date);
		if (!$timestamp) return $date;

		$diff = time() - $timestamp;
		if ($diff < 60) return 'A l instant';
		if ($diff < 3600) return 'Il y a ' . max(1, (int)floor($diff / 60)) . ' min';
		if ($diff < 86400) return 'Il y a ' . max(1, (int)floor($diff / 3600)) . ' h';
		if ($diff < 172800) return 'Hier';
		if ($diff < 604800) return 'Il y a ' . max(2, (int)floor($diff / 86400)) . ' jours';

		return date('d/m/Y', $timestamp);
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
}
