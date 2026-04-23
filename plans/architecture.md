# Architecture du Gestionnaire de Révision

Ce document détaille l'architecture du projet basée sur le diagramme de classes fourni.

## Aperçu de l'Architecture

Le projet suit une architecture MVC (Modèle-Vue-Contrôleur) avec des couches supplémentaires:
- **Vues**: Interfaces utilisateur
- **Contrôleurs**: Gestion des requêtes et logique d'application
- **Services**: Logique métier
- **Repositories**: Accès aux données
- **Modèles**: Entités de données
- **Base de données**: Gestion de la connexion à la base de données
- **Factories**: Création d'objets

## Structure des Dossiers Proposée

```
Gestionnaire_revision/
├── src/
│   ├── controllers/    # Contrôleurs (AuthController, FlashcardController)
│   ├── models/         # Modèles (User, Flashcard, QuestionResponse, Share)
│   ├── views/          # Vues (RegisterView, LoginView, etc.)
│   ├── services/       # Services (AuthService, FlashcardService, StatsService)
│   ├── repositories/   # Repositories (UserRepository, FlashcardRepository, etc.)
│   ├── database/       # Connexion à la base de données
│   ├── factory/        # Factories pour la création d'objets
│   └── config/         # Configurations
├── public/             # Ressources accessibles publiquement
│   ├── css/            # Feuilles de style
│   ├── js/             # Scripts JavaScript
│   └── assets/         # Images, polices, etc.
└── tests/              # Tests unitaires et d'intégration
```

## Classes Identifiées

### Vues
- RegisterView
- LoginView
- FlashcardStatsView
- FlashcardFormView
- ChartReportView

### Contrôleurs
- AuthController
- FlashcardController

### Services
- AuthService
- FlashcardService
- StatsService

### Repositories
- IRepository (interface)
- UserRepository
- FlashcardRepository
- QuestionResponseRepository
- FlashcardRepository

### Modèles
- User
- Flashcard
- QuestionResponse
- Share

### Base de Données
- DatabaseConnection
- SingletonConnection

### Factories
- UserFactory
- FlashcardFactory
- QuestionResponseFactory