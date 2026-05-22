<?php
/**
 * En-tete commun des pages connectees.
 */
class PageHeaderView {
	public static function render(
		?User $user,
		string $title,
		string $subtitle = '',
		string $actionsHtml = '',
		string $backHref = '',
		string $backLabel = ''
	): void {
		$firstName = $user?->firstName ?: 'Utilisateur';
		$lastName = $user?->lastName ?? '';
		$fullName = trim($firstName . ' ' . $lastName);
		$fullName = $fullName !== '' ? $fullName : 'Utilisateur';
		?>
		<header class="app-page-header">
			<div class="app-page-heading">
				<?php if ($backHref !== '' && $backLabel !== ''): ?>
					<a class="back-link" href="<?= self::e($backHref) ?>">‹ <?= self::e($backLabel) ?></a>
				<?php endif; ?>
				<h1><?= self::e($title) ?></h1>
				<?php if ($subtitle !== ''): ?>
					<p><?= self::e($subtitle) ?></p>
				<?php endif; ?>
			</div>

			<div class="app-page-header-actions">
				<?php if ($actionsHtml !== ''): ?>
					<div class="app-header-actions">
						<?= $actionsHtml ?>
					</div>
				<?php endif; ?>
				<details class="app-user-menu">
					<summary aria-label="Ouvrir le menu utilisateur">
						<span class="user-avatar"><?= self::e(self::initials($fullName)) ?></span>
						<strong><?= self::e($fullName) ?></strong>
						<span class="user-chevron">⌄</span>
					</summary>
					<div class="app-user-dropdown">
						<a href="?action=logout">Déconnexion</a>
					</div>
				</details>
			</div>
		</header>
		<?php
	}

	private static function initials(string $value): string {
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

	private static function e(mixed $value): string {
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
}
