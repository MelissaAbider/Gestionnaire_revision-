<?php
/**
 * Vue de connexion
 *
 * RESPONSABLE : Melissa ABIDER
 * Perimetre : formulaire de connexion et affichage des erreurs d'authentification.
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
		<body class="auth-body">
			<main class="auth-card">
				<section class="auth-brand-panel">
					<div class="auth-brand-content">
						<div class="auth-logo">
							<span class="auth-logo-icon">F</span>
							<span>FlashMind</span>
						</div>
						<p>Apprenez aujourd'hui,<br>reussissez demain.</p>
					</div>
				</section>

				<section class="auth-form-panel">
					<div class="auth-header">
						<h1>Bienvenue !</h1>
						<p>Connexion a votre compte</p>
					</div>

					<?php if ($error): ?>
						<div class="auth-error">
							<?= htmlspecialchars($error) ?>
						</div>
					<?php endif; ?>

					<form class="auth-form" method="POST" action="?action=loginSubmit" data-login-form novalidate>
						<div class="form-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" placeholder="Entrez votre email" required>
							<span class="auth-field-error" data-error-for="loginEmail"></span>
						</div>

						<div class="form-group">
							<label for="password">Mot de passe</label>
							<input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
							<span class="auth-field-error" data-error-for="loginPassword"></span>
						</div>

						<div class="auth-row">
							<label class="remember-choice">
								<input type="checkbox" name="remember">
								<span>Se souvenir de moi</span>
							</label>
							<a href="#">Mot de passe oublie ?</a>
						</div>

						<button type="submit" class="btn-submit">Se connecter</button>
					</form>

					<div class="login-link">
						<p>Pas encore de compte ? <a href="?action=register">S'inscrire</a></p>
					</div>
				</section>
			</main>
			<script src="/js/script.js" defer></script>
		</body>
		</html>
		<?php
	}
}
