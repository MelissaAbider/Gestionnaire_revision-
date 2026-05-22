<?php
/**
 * Modèle Flashcard
 */

class Flashcard {
    public ?int $id;
    public int $proprietaire;
    public ?int $matiereId;
    public string $title;
    public string $subject;
    public array $questionResponses;
    public DateTime $createdAt;
    public DateTime $updatedAt;

    public function __construct(array $data = []) {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->proprietaire = isset($data['proprietaire']) ? (int)$data['proprietaire'] : 0;
        $this->matiereId = isset($data['matiereId']) ? (int)$data['matiereId'] : null;
        $this->title = $data['title'] ?? '';
        $this->subject = $data['subject'] ?? '';
        $this->questionResponses = is_array($data['questionResponses'] ?? null)
            ? $data['questionResponses']
            : [];
        $this->createdAt = $data['createdAt'] instanceof DateTime
            ? $data['createdAt']
            : new DateTime($data['createdAt'] ?? 'now');
        $this->updatedAt = $data['updatedAt'] instanceof DateTime
            ? $data['updatedAt']
            : new DateTime($data['updatedAt'] ?? 'now');
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'proprietaire' => $this->proprietaire,
            'matiereId' => $this->matiereId,
            'title' => $this->title,
            'subject' => $this->subject,
            'questionResponses' => $this->questionResponses,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
