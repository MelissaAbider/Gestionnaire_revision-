<?php
/**
 * Factory pour créer des flashcards
 */

class FlashcardFactory {
    public static function fromArray(array $data): Flashcard {
        return new Flashcard($data);
    }

    public static function fromDatabaseRow(array $row): Flashcard {
        return new Flashcard([
            'id' => $row['id'] ?? null,
            'proprietaire' => $row['owner_id'] ?? 0,
            'matiereId' => $row['matiere_id'] ?? null,
            'title' => $row['title'] ?? '',
            'subject' => $row['subject'] ?? '',
            'theme' => $row['theme'] ?? '',
            'questionResponses' => $row['questionResponses'] ?? [],
            'createdAt' => $row['created_at'] ?? date('Y-m-d H:i:s'),
            'updatedAt' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }
}
