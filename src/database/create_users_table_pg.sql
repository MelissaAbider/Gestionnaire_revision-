-- Script SQL pour PostgreSQL (exécuter dans PgAdmin)
-- Crée la table `users` utilisée par l'authentification

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Index sur l'email (unique déjà défini par la contrainte UNIQUE ci-dessus)
-- CREATE UNIQUE INDEX IF NOT EXISTS users_email_idx ON users (email);

-- Exemple d'insertion de test :
-- INSERT INTO users (first_name, last_name, email, password_hash) VALUES ('Test', 'User', 'test@example.com', '<hash>');

-- Note : pour utiliser le mot de passe, génère un hash PHP via password_hash() et colle-le dans la requête d'insertion.
