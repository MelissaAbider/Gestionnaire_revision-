<?php
/**
 * Vue d'inscription
 */

class RegisterView {
    
    /**
     * Affiche la page d'inscription
     */
    public function render() {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Inscription - Gestionnaire de Révision</title>
            <link rel="stylesheet" href="/css/style.css">
        </head>
        <body class="auth-body">
            <main class="auth-card auth-card-register">
                <section class="auth-brand-panel">
                    <div class="auth-brand-content">
                        <div class="auth-logo">
                            <span class="auth-logo-icon">F</span>
                            <span>FlashMind</span>
                        </div>
                        <p>Rejoignez des milliers<br>d'etudiants motives.</p>
                    </div>
                </section>

                <section class="auth-form-panel">
                    <div class="auth-header">
                        <h1>Créer un compte</h1>
                        <p>Inscription rapide et gratuite</p>
                    </div>

                    <?php $errors = $GLOBALS['registerErrors'] ?? []; ?>
                    <?php $old = $_POST ?? []; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="auth-error">
                            <ul style="list-style:none; padding:0; margin:0;">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" method="POST" action="?action=registerSubmit" data-register-form novalidate>
                        <div class="auth-name-grid">
                            <div class="form-group <?= isset($errors['firstName']) ? 'has-error' : '' ?>">
                            <label for="firstName">Prénom</label>
                            <input 
                                type="text" 
                                id="firstName" 
                                name="firstName" 
                                placeholder="Entrez votre prénom" 
                                value="<?= htmlspecialchars($old['firstName'] ?? '') ?>"
                                required
                            >
                            <span class="auth-field-error" data-error-for="firstName"><?= htmlspecialchars($errors['firstName'] ?? '') ?></span>
                            </div>

                            <div class="form-group <?= isset($errors['lastName']) ? 'has-error' : '' ?>">
                            <label for="lastName">Nom</label>
                            <input 
                                type="text" 
                                id="lastName" 
                                name="lastName" 
                                placeholder="Entrez votre nom" 
                                value="<?= htmlspecialchars($old['lastName'] ?? '') ?>"
                                required
                            >
                            <span class="auth-field-error" data-error-for="lastName"><?= htmlspecialchars($errors['lastName'] ?? '') ?></span>
                            </div>
                        </div>

                        <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                            <label for="email">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                placeholder="Entrez votre email" 
                                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                required
                            >
                            <span class="auth-field-error" data-error-for="email"><?= htmlspecialchars($errors['email'] ?? '') ?></span>
                        </div>

                        <div class="form-group <?= isset($errors['birthDate']) ? 'has-error' : '' ?>">
                            <label for="birthDate">Date de naissance</label>
                            <input
                                type="text"
                                id="birthDate"
                                name="birthDate"
                                placeholder="AAAAMMJJ"
                                value="<?= htmlspecialchars($old['birthDate'] ?? '') ?>"
                                inputmode="numeric"
                                maxlength="8"
                                required
                            >
                            <span class="auth-field-error" data-error-for="birthDate"><?= htmlspecialchars($errors['birthDate'] ?? '') ?></span>
                        </div>

                        <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
                            <label for="password">Mot de passe</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Au moins 6 caractères" 
                                required
                            >
                            <span class="auth-field-error" data-error-for="password"><?= htmlspecialchars($errors['password'] ?? '') ?></span>
                        </div>

                        <div class="form-group <?= isset($errors['confirmPassword']) ? 'has-error' : '' ?>">
                            <label for="confirmPassword">Confirmez le mot de passe</label>
                            <input 
                                type="password" 
                                id="confirmPassword" 
                                name="confirmPassword" 
                                placeholder="Confirmez votre mot de passe" 
                                required
                            >
                            <span class="auth-field-error" data-error-for="confirmPassword"><?= htmlspecialchars($errors['confirmPassword'] ?? '') ?></span>
                        </div>

                        <label class="remember-choice terms-choice">
                            <input type="checkbox" name="terms" required>
                            <span>J'accepte les <a href="#">conditions d'utilisation</a></span>
                        </label>

                        <button type="submit" class="btn-submit">S'inscrire</button>
                    </form>

                    <div class="login-link">
                        <p>Déjà un compte ? <a href="?action=login">Se connecter</a></p>
                    </div>
                </section>
            </main>
            <script src="/js/script.js" defer></script>
        </body>
        </html>
        <?php
    }
}
