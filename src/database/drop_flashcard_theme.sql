-- Migration pour supprimer le theme des fiches de revision
ALTER TABLE flashcards
DROP COLUMN IF EXISTS theme;
