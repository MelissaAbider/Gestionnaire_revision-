-- Migration pour laisser updated_at vide tant qu'une fiche n'a pas ete modifiee
ALTER TABLE flashcards
ALTER COLUMN updated_at DROP DEFAULT;

UPDATE flashcards
SET updated_at = NULL
WHERE updated_at = created_at;
