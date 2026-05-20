Instructions pour activer l'authentification

1) Base de données (SQLite par défaut)

Le projet utilise SQLite par défaut pour la simplicité. Le fichier SQLite utilisé est :

    src/database/database.sqlite

Pour créer la base et la table `users` :

- depuis macOS avec sqlite3 installé :

```bash
cd src/database
sqlite3 database.sqlite < create_users_table.sql
```

- ou exécuter le SQL contenu dans `src/database/create_users_table.sql` dans votre client de base de données (MySQL/MariaDB : adapter le type AUTOINCREMENT selon la base).

2) Variables d'environnement (optionnel)

Vous pouvez utiliser une base différente en définissant `DB_DSN`, `DB_USER`, `DB_PASS` (ex: MySQL). Exemple :

```bash
export DB_DSN="mysql:host=127.0.0.1;dbname=gestion_revision;charset=utf8mb4"
export DB_USER="dbuser"
export DB_PASS="dbpass"
```

3) Lancer l'application

Le point d'entrée est `src/index.php`. Avec PHP intégré vous pouvez lancer un serveur local :

```bash
php -S localhost:8000 -t public
```

Ensuite, ouvrez `http://localhost:8000/` — le routeur redirigera vers la page de connexion. Pour accéder directement à l'inscription : `http://localhost:8000/?action=register`.

4) Remarques

- Les vues utilisent `public/css/style.css` pour le style existant.
- Après inscription, l'utilisateur est automatiquement connecté et redirigé vers `?action=dashboard` (page placeholder).
- Pour intégrer avec vos controllers/services existants, vous pouvez remplacer l'implémentation de `AuthService` par votre logique métier.
