-- Suppression des tables si elles existent déjà
DROP TABLE IF EXISTS shares CASCADE;
DROP TABLE IF EXISTS question_responses CASCADE;
DROP TABLE IF EXISTS flashcards CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- =========================
-- TABLE USERS
-- =========================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    birthdate DATE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABLE FLASHCARDS
-- =========================
CREATE TABLE flashcards (
    id SERIAL PRIMARY KEY,
    owner_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    theme VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_flashcards_owner
        FOREIGN KEY (owner_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =========================
-- TABLE QUESTION_RESPONSES
-- =========================
CREATE TABLE question_responses (
    id SERIAL PRIMARY KEY,
    flashcard_id INTEGER NOT NULL,
    question TEXT NOT NULL,
    response TEXT NOT NULL,

    CONSTRAINT fk_question_responses_flashcard
        FOREIGN KEY (flashcard_id)
        REFERENCES flashcards(id)
        ON DELETE CASCADE
);

-- =========================
-- TABLE SHARES
-- =========================
CREATE TABLE shares (
    id SERIAL PRIMARY KEY,
    flashcard_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_shares_flashcard
        FOREIGN KEY (flashcard_id)
        REFERENCES flashcards(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_shares_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT unique_share
        UNIQUE (flashcard_id, user_id)
);

-- =========================
-- INDEX POUR AMÉLIORER LES REQUÊTES
-- =========================
CREATE INDEX idx_flashcards_owner_id 
ON flashcards(owner_id);

CREATE INDEX idx_question_responses_flashcard_id 
ON question_responses(flashcard_id);

CREATE INDEX idx_shares_flashcard_id 
ON shares(flashcard_id);

CREATE INDEX idx_shares_user_id 
ON shares(user_id);