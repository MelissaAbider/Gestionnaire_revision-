<?php
/**
 * Vue de connexion
 */
class LoginView {
	public function render(): void {
		$error = $GLOBALS['loginError'] ?? null;
		?>
		<!DOCTYPE html>
		<html lang="fr">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Connexion - Gestionnaire de Révision</title>
			<link rel="stylesheet" href="/css/style.css">
		</head>
		<body>
			<div class="register-container">
				<div class="register-box">
					<div class="register-header">
						<h1>Connexion</h1>
						<p>Connectez-vous à votre compte</p>
					</div>

					<?php if ($error): ?>
						<div style="color: var(--error); margin-bottom: 12px; text-align:center;">
							<?= htmlspecialchars($error) ?>
						</div>
					<?php endif; ?>

					<form class="register-form" method="POST" action="?action=loginSubmit">
						<div class="form-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" placeholder="Entrez votre email" required>
						</div>

						<div class="form-group">
							<label for="password">Mot de passe</label>
							<input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
						</div>

						<button type="submit" class="btn-submit">Se connecter</button>
					</form>

					<div class="login-link">
						<p>Pas encore de compte ? <a href="?action=register">Inscrivez-vous</a></p>
					</div>
				</div>
			</div>
		</body>
		</html>
		<?php
	}
}
