<?php
/**
 * Navigation principale des pages connectees.
 */
class HomeNavView {
	public static function render(string $active = ''): void {
		$items = [
			'dashboard' => ['href' => '?action=dashboard', 'icon' => '⌂', 'label' => 'Accueil'],
			'flashcards' => ['href' => '?action=flashcards', 'icon' => '□', 'label' => 'Mes fiches'],
			'matieres' => ['href' => '?action=matieres', 'icon' => '▣', 'label' => 'Matières'],
		];
		?>
		<aside class="home-sidebar" aria-label="Navigation principale">
			<a class="home-brand flashmind-brand" href="?action=dashboard">
				<span class="brand-icon">F</span>
				<span class="brand-copy">
					<strong>FlashMind</strong>
					<small>Fiches de révision</small>
				</span>
			</a>

			<nav class="home-nav">
				<?php foreach ($items as $key => $item): ?>
					<a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>" class="nav-item <?= $active === $key ? 'active' : '' ?>">
						<span class="nav-icon"><?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?></span>
						<span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
					</a>
				<?php endforeach; ?>
			</nav>

			<a href="?action=logout" class="nav-item logout-link">
				<span class="nav-icon">⇥</span>
				<span>Déconnexion</span>
			</a>
		</aside>
		<?php
	}
}
