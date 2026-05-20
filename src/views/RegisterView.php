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
        <body>
            <div class="register-container">
                <div class="register-box">
                    <div class="register-header">
                        <h1>Inscription</h1>
                        <p>Créez votre compte pour commencer</p>
                    </div>

                    <?php $errors = $GLOBALS['registerErrors'] ?? []; ?>
                    <?php $old = $_POST ?? []; ?>
                    <?php if (!empty($errors)): ?>
                        <div style="color: var(--error); margin-bottom: 12px; text-align:center;">
                            <ul style="list-style:none; padding:0; margin:0;">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form class="register-form" method="POST" action="?action=registerSubmit">
                        <div class="form-group">
                            <label for="firstName">Prénom</label>
                            <input 
                                type="text" 
                                id="firstName" 
                                name="firstName" 
                                placeholder="Entrez votre prénom" 
                                value="<?= htmlspecialchars($old['firstName'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="lastName">Nom</label>
                            <input 
                                type="text" 
                                id="lastName" 
                                name="lastName" 
                                placeholder="Entrez votre nom" 
                                value="<?= htmlspecialchars($old['lastName'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                placeholder="Entrez votre email" 
                                value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Entrez votre mot de passe" 
                                required
                            >
                            <small class="password-hint">Au minimum 8 caractères</small>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirmez le mot de passe</label>
                            <input 
                                type="password" 
                                id="confirmPassword" 
                                name="confirmPassword" 
                                placeholder="Confirmez votre mot de passe" 
                                required
                            >
                        </div>

                        <button type="submit" class="btn-submit">S'inscrire</button>
                    </form>

                    <div class="login-link">
                        <p>Avez-vous déjà un compte ? <a href="?action=login">Connectez-vous ici</a></p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}
