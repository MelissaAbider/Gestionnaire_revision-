<?php
/**
 * Repository des flashcards
 */

class FlashcardRepository {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    public function create(Flashcard $flashcard): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO flashcards (owner_id, matiere_id, title, subject, theme, created_at, updated_at)
            VALUES (:owner_id, :matiere_id, :title, :subject, :theme, :created_at, :updated_at)
            RETURNING id'
        );
        $stmt->execute([
            'owner_id' => $flashcard->proprietaire,
            'matiere_id' => $flashcard->matiereId,
            'title' => $flashcard->title,
            'subject' => $flashcard->subject,
            'theme' => $flashcard->theme,
            'created_at' => $flashcard->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $flashcard->updatedAt->format('Y-m-d H:i:s'),
        ]);

        return (int)$stmt->fetchColumn();
    }

    public function findByIdForUser(int $id, int $ownerId): ?Flashcard {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM flashcards WHERE id = :id AND owner_id = :owner_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'owner_id' => $ownerId,
        ]);
        $row = $stmt->fetch();

        return $row ? FlashcardFactory::fromDatabaseRow($row) : null;
    }

    /**
     * @return Flashcard[]
     */
    public function findByMatiereForUser(int $matiereId, int $ownerId): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM flashcards
            WHERE matiere_id = :matiere_id AND owner_id = :owner_id
            ORDER BY updated_at DESC'
        );
        $stmt->execute([
            'matiere_id' => $matiereId,
            'owner_id' => $ownerId,
        ]);

        return array_map(
            fn(array $row): Flashcard => FlashcardFactory::fromDatabaseRow($row),
            $stmt->fetchAll()
        );
    }
}
