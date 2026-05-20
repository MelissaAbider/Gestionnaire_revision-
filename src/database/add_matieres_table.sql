-- Migration pour ajouter les matieres sans supprimer les donnees existantes
CREATE TABLE IF NOT EXISTS matieres (
    id SERIAL PRIMARY KEY,
    owner_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(30) DEFAULT 'blue',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_matieres_owner
        FOREIGN KEY (owner_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT unique_matiere_per_user
        UNIQUE (owner_id, name)
);

ALTER TABLE flashcards
ADD COLUMN IF NOT EXISTS matiere_id INTEGER;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'fk_flashcards_matiere'
    ) THEN
        ALTER TABLE flashcards
        ADD CONSTRAINT fk_flashcards_matiere
        FOREIGN KEY (matiere_id)
        REFERENCES matieres(id)
        ON DELETE SET NULL;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS idx_matieres_owner_id
ON matieres(owner_id);

CREATE INDEX IF NOT EXISTS idx_flashcards_matiere_id
ON flashcards(matiere_id);
