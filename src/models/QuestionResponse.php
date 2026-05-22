<?php
/**
 * Modèle QuestionResponse
 */

class QuestionResponse {
    public ?int $id;
    public int $flashcardId;
    public string $question;
    public string $response;

    public function __construct(
        ?int $id,
        int $flashcardId,
        string $question,
        string $response
    ) {
        $this->id = $id;
        $this->flashcardId = $flashcardId;
        $this->question = $question;
        $this->response = $response;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'flashcardId' => $this->flashcardId,
            'question' => $this->question,
            'response' => $this->response,
        ];
    }
}
