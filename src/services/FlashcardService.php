<?php
/**
 * Service de gestion des flashcards
 */

class FlashcardService {
    private FlashcardRepository $flashcardRepo;
    private MatiereRepository $matiereRepo;

    public function __construct() {
        $this->flashcardRepo = new FlashcardRepository();
        $this->matiereRepo = new MatiereRepository();
    }

    public function create(array $data, int $ownerId): array {
        $title = trim($data['title'] ?? '');
        $matiereId = isset($data['matiereId']) && $data['matiereId'] !== ''
            ? (int)$data['matiereId']
            : null;
        $errors = [];

        if ($title === '') {
            $errors[] = 'Le titre de la flashcard est requis.';
        }

        if ($matiereId !== null && !$this->matiereRepo->findByIdForUser($matiereId, $ownerId)) {
            $errors[] = 'La matiere selectionnee est introuvable.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $flashcard = FlashcardFactory::fromArray([
            'proprietaire' => $ownerId,
            'matiereId' => $matiereId,
            'title' => $title,
            'subject' => trim($data['subject'] ?? ''),
            'theme' => trim($data['theme'] ?? ''),
        ]);
        $flashcard->id = $this->flashcardRepo->create($flashcard);

        return ['success' => true, 'flashcard' => $flashcard];
    }

    /**
     * @return Flashcard[]
     */
    public function findByMatiereForUser(int $matiereId, int $ownerId): array {
        return $this->flashcardRepo->findByMatiereForUser($matiereId, $ownerId);
    }

    public function findRecentActivityForUser(int $ownerId, int $limit = 5): array {
        return $this->flashcardRepo->findRecentActivityForUser($ownerId, $limit);
    }
}
