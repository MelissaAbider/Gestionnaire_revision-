# Gestionnaire de Révision

Application de gestion de flashcards pour faciliter l'apprentissage et la révision.

## Structure du Projet

```
Gestionnaire_revision/
├── index.php                           # Point d'entrée principal
├── config.php                          # Configuration globale
├── .gitignore                          # Fichiers à ignorer par Git
│
├── src/                                # Code source
│   ├── Router.php                      # Routeur de l'application
│   │
│   ├── controllers/                    # Contrôleurs
│   │   ├── AuthController.php          # Authentification
│   │   └── FlashcardController.php     # Gestion des flashcards
│   │
│   ├── models/                         # Modèles de données
│   │   ├── User.php                    # Utilisateur
│   │   ├── Flashcard.php               # Carte mémoire
│   │   ├── QuestionResponse.php        # Question/Réponse
│   │   └── Share.php                   # Partage
│   │
│   ├── views/                          # Vues (interfaces)
│   │   ├── RegisterView.php            # Inscription
│   │   ├── LoginView.php               # Connexion
│   │   ├── FlashcardStatsView.php      # Statistiques
│   │   ├── FlashcardFormView.php       # Formulaire
│   │   └── ChartReportView.php         # Rapports graphiques
│   │
│   ├── services/                       # Services (logique métier)
│   │   ├── AuthService.php             # Service d'authentification
│   │   ├── FlashcardService.php        # Service flashcards
│   │   └── StatsService.php            # Service statistiques
│   │
│   ├── repositories/                   # Accès aux données
│   │   ├── IRepository.php             # Interface repository
│   │   ├── UserRepository.php          # Repository utilisateurs
│   │   ├── FlashcardRepository.php     # Repository flashcards
│   │   ├── QuestionResponseRepository.php # Repository Q/R
│   │   └── ShareRepository.php         # Repository partages
│   │
│   ├── database/                       # Connexion base de données
│   │   ├── DatabaseConnection.php      # Classe de connexion
│   │   └── SingletonConnection.php     # Singleton de connexion
│   │
│   └── factory/                        # Factories
│       ├── UserFactory.php             # Factory utilisateurs
│       ├── FlashcardFactory.php        # Factory flashcards
│       └── QuestionResponseFactory.php # Factory Q/R
│
├── public/                             # Ressources publiques
│   ├── css/
│   │   └── style.css                   # Styles CSS
│   ├── js/
│   │   └── script.js                   # Scripts JavaScript
│   └── assets/                         # Images, polices, etc.
│
└── plans/                              # Documentation
    ├── architecture.md                 # Architecture du projet
    ├── structure_dossiers.md           # Structure des dossiers
    └── implementation_complete.md      # Plan d'implémentation
```

## Architecture

Le projet suit une architecture MVC (Modèle-Vue-Contrôleur) avec des couches supplémentaires :

- **Modèles** : Entités de données (User, Flashcard, QuestionResponse, Share)
- **Vues** : Interfaces utilisateur
- **Contrôleurs** : Gestion des requêtes et logique d'application
- **Services** : Logique métier
- **Repositories** : Accès aux données (pattern Repository)
- **Factories** : Création d'objets (pattern Factory)
- **Database** : Gestion de la connexion (pattern Singleton)

## Classes Principales

### Contrôleurs
- **AuthController** : Gestion de l'authentification (login, register)
- **FlashcardController** : Gestion des flashcards et statistiques

### Modèles
- **User** : Représente un utilisateur
- **Flashcard** : Représente une carte mémoire
- **QuestionResponse** : Représente une question et sa réponse
- **Share** : Représente le partage d'une flashcard

### Services
- **AuthService** : Logique d'authentification
- **FlashcardService** : Logique de gestion des flashcards
- **StatsService** : Logique de calcul des statistiques

### Repositories
- **IRepository** : Interface pour tous les repositories
- **UserRepository** : Opérations CRUD sur les utilisateurs
- **FlashcardRepository** : Opérations CRUD sur les flashcards
- **QuestionResponseRepository** : Opérations CRUD sur les Q/R
- **ShareRepository** : Opérations CRUD sur les partages

### Factories
- **UserFactory** : Création d'objets User
- **FlashcardFactory** : Création d'objets Flashcard
- **QuestionResponseFactory** : Création d'objets QuestionResponse

### Database
- **DatabaseConnection** : Classe de connexion à la base de données
- **SingletonConnection** : Implémentation du pattern Singleton pour la connexion

## Installation

1. Cloner le projet
2. Configurer la base de données dans `config.php`
3. Importer le schéma de base de données
4. Lancer l'application via un serveur web (Apache, Nginx, etc.)

## Technologies

- PHP 7.4+
- MySQL/MariaDB
- HTML5/CSS3
- JavaScript

## Prochaines Étapes

Les fichiers ont été créés avec une structure de base. Vous devez maintenant :

1. Implémenter les méthodes dans chaque classe selon le diagramme de classe
2. Configurer la connexion à la base de données
3. Créer le schéma de base de données
4. Implémenter le routage dans Router.php
5. Développer les vues HTML
6. Ajouter les styles CSS et scripts JavaScript
