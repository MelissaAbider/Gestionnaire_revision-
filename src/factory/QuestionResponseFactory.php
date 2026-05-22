<?php
/**
 * Factory pour créer des questions/réponses
 */

class QuestionResponseFactory {
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): QuestionResponse {
        return new QuestionResponse(
            isset($data['id']) ? (int)$data['id'] : null,
            (int)($data['flashcardId'] ?? $data['flashcard_id'] ?? 0),
            trim((string)($data['question'] ?? '')),
            trim((string)($data['response'] ?? ''))
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): QuestionResponse {
        return self::fromArray([
            'id' => $row['id'] ?? null,
            'flashcardId' => $row['flashcard_id'] ?? 0,
            'question' => $row['question'] ?? '',
            'response' => $row['response'] ?? '',
        ]);
    }
}
