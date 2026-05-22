<?php
/**
 * Modèle Share
 */

class Share {
    public ?int $id;
    public int $flashcardId;
    public int $userId;
    public string $sharedAt;

    public function __construct(array $data = []) {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->flashcardId = isset($data['flashcardId']) ? (int)$data['flashcardId'] : 0;
        $this->userId = isset($data['userId']) ? (int)$data['userId'] : 0;
        $this->sharedAt = $data['sharedAt'] ?? date('Y-m-d H:i:s');
    }
}
