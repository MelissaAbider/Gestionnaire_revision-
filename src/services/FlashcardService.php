<?php
/**
 * Service de gestion des flashcards
 */

class FlashcardService {
    private FlashcardRepository $flashcardRepo;
    private MatiereRepository $matiereRepo;
    private UserRepository $userRepo;

    public function __construct() {
        $this->flashcardRepo = new FlashcardRepository();
        $this->matiereRepo = new MatiereRepository();
        $this->userRepo = new UserRepository();
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findListForUser(int $ownerId): array {
        return $this->flashcardRepo->findListForUser($ownerId);
    }

    /**
     * @param array<int, array<string, mixed>> $flashcards
     * @param array<string, string> $filters
     * @return array<int, array<string, mixed>>
     */
    public function filterList(array $flashcards, array $filters): array {
        $query = mb_strtolower(trim($filters['q'] ?? ''));
        $matiere = trim($filters['matiere'] ?? '');
        $sort = $filters['sort'] ?? 'recent';

        $filtered = array_values(array_filter(
            $flashcards,
            function (array $flashcard) use ($query, $matiere): bool {
                if ($matiere !== '' && ($flashcard['matiere_name'] ?? '') !== $matiere) {
                    return false;
                }

                if ($query === '') {
                    return true;
                }

                $haystack = mb_strtolower((string)($flashcard['title'] ?? ''));

                return str_contains($haystack, $query);
            }
        ));

        usort($filtered, function (array $left, array $right) use ($sort): int {
            if ($sort === 'oldest') {
                return strcmp((string)($left['created_at'] ?? ''), (string)($right['created_at'] ?? ''));
            }

            if ($sort === 'title') {
                return strcasecmp((string)($left['title'] ?? ''), (string)($right['title'] ?? ''));
            }

            return strcmp((string)($right['created_at'] ?? ''), (string)($left['created_at'] ?? ''));
        });

        return $filtered;
    }

    /**
     * @param array<int, array<string, mixed>> $flashcards
     * @return string[]
     */
    public function getMatiereOptions(array $flashcards): array {
        $names = [];
        foreach ($flashcards as $flashcard) {
            $name = trim((string)($flashcard['matiere_name'] ?? ''));
            if ($name !== '') {
                $names[$name] = $name;
            }
        }

        natcasesort($names);

        return array_values($names);
    }

    public function createFromForm(array $data, int $ownerId): array {
        $normalized = $this->normalizeFormData($data);
        $errors = $this->validateFormData($normalized, $ownerId);

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $normalized,
            ];
        }

        $matiereName = $this->findMatiereName($normalized['matiereId'], $ownerId);
        $id = $this->flashcardRepo->createFromForm(
            $ownerId,
            $normalized['title'],
            $normalized['content'],
            $normalized['matiereId'],
            $matiereName,
            $normalized['sharedUserIds']
        );

        return ['success' => true, 'id' => $id];
    }

    public function updateFromForm(int $id, array $data, int $ownerId): array {
        $normalized = $this->normalizeFormData($data);
        $normalized['id'] = $id;
        $errors = $this->validateFormData($normalized, $ownerId);

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $normalized,
            ];
        }

        $matiereName = $this->findMatiereName($normalized['matiereId'], $ownerId);
        $this->flashcardRepo->updateFromForm(
            $id,
            $ownerId,
            $normalized['title'],
            $normalized['content'],
            $normalized['matiereId'],
            $matiereName,
            $normalized['sharedUserIds']
        );

        return ['success' => true, 'id' => $id];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findFormDataForUser(int $id, int $ownerId): ?array {
        return $this->flashcardRepo->findFormDataForUser($id, $ownerId);
    }

    /**
     * @return array<string, mixed>
     */
    public function emptyFormData(): array {
        return [
            'id' => null,
            'title' => '',
            'content' => '',
            'matiereId' => '',
            'sharedUserIds' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(int $ownerId): array {
        return [
            'matieres' => $this->matiereRepo->findAllByUser($ownerId),
            'users' => $this->userRepo->findShareCandidates($ownerId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFormData(array $data): array {
        $matiereId = isset($data['matiereId']) && $data['matiereId'] !== ''
            ? (int)$data['matiereId']
            : null;

        $sharedUserIds = $data['sharedUserIds'] ?? [];
        if (!is_array($sharedUserIds)) {
            $sharedUserIds = [];
        }

        return [
            'title' => trim($data['title'] ?? ''),
            'content' => trim($data['content'] ?? ''),
            'matiereId' => $matiereId,
            'sharedUserIds' => array_values(array_unique(array_map('intval', $sharedUserIds))),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validateFormData(array $data, int $ownerId): array {
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'] = 'Le titre est obligatoire.';
        } elseif (mb_strlen($data['title']) > 150) {
            $errors['title'] = 'Le titre ne doit pas depasser 150 caracteres.';
        }

        if ($data['content'] === '') {
            $errors['content'] = 'Le contenu est obligatoire.';
        }

        if ($data['matiereId'] !== null && !$this->matiereRepo->findByIdForUser((int)$data['matiereId'], $ownerId)) {
            $errors['matiereId'] = 'La matiere selectionnee est introuvable.';
        }

        return $errors;
    }

    private function findMatiereName(?int $matiereId, int $ownerId): string {
        if ($matiereId === null) {
            return '';
        }

        $matiere = $this->matiereRepo->findByIdForUser($matiereId, $ownerId);

        return $matiere?->name ?? '';
    }
}
