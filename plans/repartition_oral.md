# Repartition des fichiers pour l'oral

Ce fichier resume les zones du projet a connaitre selon la repartition presentee dans le groupe.

## Melissa ABIDER

Perimetre : inscription, connexion, deconnexion, sessions et erreurs de formulaires.

- `src/controllers/AuthController.php`
- `src/services/AuthService.php`
- `src/views/RegisterView.php`
- `src/views/LoginView.php`
- `src/repositories/UserRepository.php`
- `src/models/User.php`
- `src/factory/UserFactory.php`
- `public/js/session-timeout.js`
- `public/js/script.js` : blocs de validation inscription/connexion
- `src/database/script.sql` : table `users`

## Asma AZRI

Perimetre : creation, affichage, modification, suppression et visualisation des fiches de revision personnelles.

- `src/services/FlashcardService.php`
- `src/repositories/FlashcardRepository.php`
- `src/repositories/QuestionResponseRepository.php`
- `src/views/FlashcardFormView.php`
- `src/views/FlashcardsListView.php` : bloc "Mes fiches"
- `src/views/FlashcardDetailView.php`
- `src/models/Flashcard.php`
- `src/models/QuestionResponse.php`
- `src/factory/FlashcardFactory.php`
- `src/factory/QuestionResponseFactory.php`
- `public/js/script.js` : ajout/suppression des questions-reponses et carte de revision
- `src/database/script.sql` : tables `flashcards` et `question_responses`

## Alexandre BRUGGER

Perimetre : page d'accueil, tableau de bord, navigation entre les pages et statistiques de revision.

- `src/Router.php`
- `src/views/AccueilView.php`
- `src/views/HomeNavView.php`
- `src/views/PageHeaderView.php`
- `src/services/StatsService.php`
- `src/index.php`
- `public/index.php`
- `public/css/style.css` : navigation, accueil et tableau de bord
- `src/database/script.sql` : table `revision_events`

## Jana CHEHWAN

Perimetre : organisation des fiches par matiere/theme, affichage des pages principales et experience utilisateur.

- `src/views/MatiereView.php`
- `src/services/MatiereService.php`
- `src/repositories/MatiereRepository.php`
- `src/models/Matiere.php`
- `src/factory/MatiereFactory.php`
- `src/database/add_matieres_table.sql`
- `src/database/script.sql` : table `matieres` et lien `flashcards.matiere_id`
- `public/js/script.js` : validation des formulaires de matieres
- `public/css/style.css` : styles des pages connectees et des matieres

## Alban COUSIN

Perimetre : partage des fiches avec d'autres utilisateurs, affichage des fiches partagees et retrait du partage.

- `src/repositories/ShareRepository.php`
- `src/models/Share.php`
- `src/views/FlashcardFormView.php` : bloc de selection des utilisateurs
- `src/views/FlashcardsListView.php` : bloc "Partagees avec moi"
- `src/views/FlashcardDetailView.php` : affichage des destinataires du partage
- `src/repositories/FlashcardRepository.php` : methodes `syncShares()`, `findSharedUserIds()`, `findSharesByFlashcardIds()`
- `src/services/FlashcardService.php` : normalisation des `sharedUserIds`
- `src/repositories/UserRepository.php` : methode `findShareCandidates()`
- `public/js/script.js` : recherche et compteur des utilisateurs partages
- `src/database/script.sql` : table `shares`

## Fichiers transverses a connaitre par tout le groupe

- `README.md`
- `plans/architecture.md`
- `plans/structure_dossiers.md`
- `src/database/DatabaseConnection.php`
- `public/css/style.css`
- `public/js/script.js`
