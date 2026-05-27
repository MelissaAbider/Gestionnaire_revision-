<?php
/**
 * Factory pour créer des questions/réponses
 *
 * RESPONSABLE : Asma AZRI
 * Perimetre : construction des objets QuestionResponse.
 */

class QuestionResponseFactory {
    
    // Crée une instance de QuestionResponse à partir d'un tableau de données
    public static function fromArray(array $data): QuestionResponse {
        return new QuestionResponse(
            isset($data['id']) ? (int)$data['id'] : null,
            (int)($data['flashcardId'] ?? $data['flashcard_id'] ?? 0),
            trim((string)($data['question'] ?? '')),
            trim((string)($data['response'] ?? ''))
        );
    }

    // Crée une instance de QuestionResponse à partir d'une ligne de la base de données
    public static function fromDatabaseRow(array $row): QuestionResponse {
        return self::fromArray([
            'id' => $row['id'] ?? null,
            'flashcardId' => $row['flashcard_id'] ?? 0,
            'question' => $row['question'] ?? '',
            'response' => $row['response'] ?? '',
        ]);
    }
}
