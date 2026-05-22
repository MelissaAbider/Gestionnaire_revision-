-- Migration pour ajouter la date de naissance au compte utilisateur
ALTER TABLE users
ADD COLUMN IF NOT EXISTS birth_date CHAR(8);
