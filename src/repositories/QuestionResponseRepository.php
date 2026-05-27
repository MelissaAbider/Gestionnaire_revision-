<?php
/**
 * Repository des questions/réponses
 *
 * RESPONSABLE : Asma AZRI
 * Perimetre : sauvegarde et chargement des cartes question/reponse d'une fiche.
 */

class QuestionResponseRepository {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    /**
     * @return QuestionResponse[]
     */
    public function findByFlashcardId(int $flashcardId): array {
        $stmt = $this->pdo->prepare(
            'SELECT id, flashcard_id, question, response
            FROM question_responses
            WHERE flashcard_id = :flashcard_id
            ORDER BY id ASC'
        );
        $stmt->execute(['flashcard_id' => $flashcardId]);

        return array_map(
            fn(array $row): QuestionResponse => QuestionResponseFactory::fromDatabaseRow($row),
            $stmt->fetchAll()
        );
    }

    /**
     * @param array<int, array<string, string>|QuestionResponse> $questionResponses
     */
    public function replaceForFlashcard(int $flashcardId, array $questionResponses): void {
        $stmt = $this->pdo->prepare('DELETE FROM question_responses WHERE flashcard_id = :flashcard_id');
        $stmt->execute(['flashcard_id' => $flashcardId]);

        $stmt = $this->pdo->prepare(
            'INSERT INTO question_responses (flashcard_id, question, response)
            VALUES (:flashcard_id, :question, :response)'
        );

        foreach ($questionResponses as $questionResponse) {
            if ($questionResponse instanceof QuestionResponse) {
                $question = trim($questionResponse->question);
                $response = trim($questionResponse->response);
            } else {
                $question = trim((string)($questionResponse['question'] ?? ''));
                $response = trim((string)($questionResponse['response'] ?? ''));
            }

            if ($question === '' || $response === '') {
                continue;
            }

            $stmt->execute([
                'flashcard_id' => $flashcardId,
                'question' => $question,
                'response' => $response,
            ]);
        }
    }
}
