# Structure des Dossiers

Voici la structure de dossiers à créer pour le projet Gestionnaire de Révision:

```
Gestionnaire_revision/
├── src/
│   ├── controllers/      # Gestion des requêtes et logique d'application
│   ├── models/           # Entités de données
│   ├── views/            # Interfaces utilisateur
│   ├── services/         # Logique métier
│   ├── repositories/     # Accès aux données
│   ├── database/         # Connexion à la base de données
│   ├── factory/          # Factories pour la création d'objets
│   └── config/           # Configurations
├── public/               # Ressources accessibles publiquement
│   ├── css/              # Feuilles de style
│   ├── js/               # Scripts JavaScript
│   └── assets/           # Images, polices, etc.
└── tests/                # Tests unitaires et d'intégration
```

## Points d'entrée

- `index.php` - Point d'entrée principal de l'application
- `config.php` - Configuration de l'application