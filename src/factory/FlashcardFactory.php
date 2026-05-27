<?php
/**
 * Factory pour créer des flashcards
 *
 * RESPONSABLE : Asma AZRI
 * Perimetre : construction des objets Flashcard depuis formulaires et lignes SQL.
 */

class FlashcardFactory {
    // Crée une instance de Flashcard à partir d'un tableau de données
    public static function fromArray(array $data): Flashcard {
        return new Flashcard($data);
    }

    // Crée une instance de Flashcard à partir d'une ligne de la base de données
    public static function fromDatabaseRow(array $row): Flashcard {
        return new Flashcard([
            'id' => $row['id'] ?? null,
            'proprietaire' => $row['owner_id'] ?? 0,
            'matiereId' => $row['matiere_id'] ?? null,
            'title' => $row['title'] ?? '',
            'subject' => $row['subject'] ?? '',
            'questionResponses' => $row['questionResponses'] ?? [],
            'createdAt' => $row['created_at'] ?? date('Y-m-d H:i:s'),
            'updatedAt' => $row['updated_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }
}
