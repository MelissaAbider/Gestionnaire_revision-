<?php
/**
 * Service de statistiques
 *
 * RESPONSABLE : Alexandre BRUGGER
 * Perimetre : indicateurs du tableau de bord et suivi des revisions recentes.
 */

class StatsService {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
        $this->ensureRevisionEventsTable();
    }

    public function recordRevision(int $userId, int $flashcardId): void {
        if ($this->hasRecentRevision($userId, $flashcardId)) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO revision_events (user_id, flashcard_id, reviewed_at)
            VALUES (:user_id, :flashcard_id, :reviewed_at)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'flashcard_id' => $flashcardId,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function countRevisionsSince(int $userId, \DateTimeInterface $since): int {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM revision_events
            WHERE user_id = :user_id
            AND reviewed_at >= :since'
        );
        $stmt->execute([
            'user_id' => $userId,
            'since' => $since->format('Y-m-d H:i:s'),
        ]);

        return (int)$stmt->fetchColumn();
    }

    private function hasRecentRevision(int $userId, int $flashcardId): bool {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM revision_events
            WHERE user_id = :user_id
            AND flashcard_id = :flashcard_id
            AND reviewed_at >= :since'
        );
        $stmt->execute([
            'user_id' => $userId,
            'flashcard_id' => $flashcardId,
            'since' => (new DateTimeImmutable('-30 minutes'))->format('Y-m-d H:i:s'),
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function ensureRevisionEventsTable(): void {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS revision_events (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL,
                flashcard_id INTEGER NOT NULL,
                reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_revision_events_user
                    FOREIGN KEY (user_id)
                    REFERENCES users(id)
                    ON DELETE CASCADE,
                CONSTRAINT fk_revision_events_flashcard
                    FOREIGN KEY (flashcard_id)
                    REFERENCES flashcards(id)
                    ON DELETE CASCADE
            )'
        );

        $this->pdo->exec(
            'CREATE INDEX IF NOT EXISTS idx_revision_events_user_reviewed_at
            ON revision_events(user_id, reviewed_at)'
        );
    }
}
