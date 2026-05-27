<?php
/**
 * Service de gestion des flashcards
 *
 * RESPONSABLE PRINCIPAL : Asma AZRI
 * Perimetre : creation, affichage, modification, suppression et visualisation des fiches.
 * Points de contact : Jana CHEHWAN pour les matieres, Alban COUSIN pour les partages.
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
        $questionResponses = $this->normalizeQuestionResponses($data);
        $questionResponseValidation = $this->validateQuestionResponses($questionResponses);
        $errors = [];

        if ($title === '') {
            $errors[] = 'Le titre de la flashcard est requis.';
        }

        if ($matiereId !== null && !$this->matiereRepo->findByIdForUser($matiereId, $ownerId)) {
            $errors[] = 'La matiere selectionnee est introuvable.';
        }

        if ($questionResponseValidation !== null) {
            $errors[] = $questionResponseValidation;
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $flashcard = FlashcardFactory::fromArray([
            'proprietaire' => $ownerId,
            'matiereId' => $matiereId,
            'title' => $title,
            'subject' => trim($data['subject'] ?? ''),
            'questionResponses' => $questionResponses,
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
     * @return array<string, mixed>|null
     */
    public function findViewForUser(int $id, int $userId): ?array {
        if ($id <= 0) {
            return null;
        }

        return $this->flashcardRepo->findViewForUser($id, $userId);
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
                return strcmp($this->activityDate($left), $this->activityDate($right));
            }

            if ($sort === 'title') {
                return strcasecmp((string)($left['title'] ?? ''), (string)($right['title'] ?? ''));
            }

            return strcmp($this->activityDate($right), $this->activityDate($left));
        });

        return $filtered;
    }

    /**
     * @param array<string, mixed> $flashcard
     */
    private function activityDate(array $flashcard): string {
        return (string)($flashcard['updated_at'] ?? $flashcard['created_at'] ?? '');
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
            $normalized['questionResponses'],
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
            $normalized['questionResponses'],
            $normalized['matiereId'],
            $matiereName,
            $normalized['sharedUserIds']
        );

        return ['success' => true, 'id' => $id];
    }

    public function deleteForUser(int $id, int $ownerId): bool {
        if ($id <= 0) {
            return false;
        }

        return $this->flashcardRepo->deleteForUser($id, $ownerId);
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
            'questionResponses' => [
                ['question' => '', 'response' => ''],
            ],
            'matiereId' => '',
            'sharedUserIds' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(int $ownerId): array {
        // Alban COUSIN : la liste des utilisateurs sert a proposer les destinataires du partage.
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

        // Alban COUSIN : normalisation des utilisateurs selectionnes pour le partage.
        $sharedUserIds = $data['sharedUserIds'] ?? [];
        if (!is_array($sharedUserIds)) {
            $sharedUserIds = [];
        }

        return [
            'title' => trim($data['title'] ?? ''),
            'questionResponses' => $this->normalizeQuestionResponses($data),
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

        $questionResponseValidation = $this->validateQuestionResponses($data['questionResponses'] ?? []);
        if ($questionResponseValidation !== null) {
            $errors['questionResponses'] = $questionResponseValidation;
        }

        if ($data['matiereId'] !== null && !$this->matiereRepo->findByIdForUser((int)$data['matiereId'], $ownerId)) {
            $errors['matiereId'] = 'La matiere selectionnee est introuvable.';
        }

        return $errors;
    }

    // normalise les données de question/réponse provenant du formulaire, en gérant à la fois les formats "questionResponses" et "questions"/"responses"
    private function normalizeQuestionResponses(array $data): array {
        if (isset($data['questionResponses']) && is_array($data['questionResponses'])) {
            $questionResponses = [];

            foreach ($data['questionResponses'] as $questionResponse) {
                if (!is_array($questionResponse)) {
                    continue;
                }

                $question = trim((string)($questionResponse['question'] ?? ''));
                $response = trim((string)($questionResponse['response'] ?? ''));

                if ($question === '' && $response === '') {
                    continue;
                }

                $questionResponses[] = [
                    'question' => $question,
                    'response' => $response,
                ];
            }

            return !empty($questionResponses)
                ? $questionResponses
                : [['question' => '', 'response' => '']];
        }

        $questions = $data['questions'] ?? [];
        $responses = $data['responses'] ?? [];

        if (!is_array($questions)) {
            $questions = [];
        }

        if (!is_array($responses)) {
            $responses = [];
        }

        $questionResponses = [];
        $count = max(count($questions), count($responses));

        for ($index = 0; $index < $count; $index++) {
            $question = trim((string)($questions[$index] ?? ''));
            $response = trim((string)($responses[$index] ?? ''));

            if ($question === '' && $response === '') {
                continue;
            }

            $questionResponses[] = [
                'question' => $question,
                'response' => $response,
            ];
        }

        return !empty($questionResponses)
            ? $questionResponses
            : [['question' => '', 'response' => '']];
    }

    /**
     * @param array<int, array<string, string>> $questionResponses
     */
    private function validateQuestionResponses(array $questionResponses): ?string {
        $hasCompleteQuestionResponse = false;

        foreach ($questionResponses as $questionResponse) {
            $question = trim((string)($questionResponse['question'] ?? ''));
            $response = trim((string)($questionResponse['response'] ?? ''));

            if ($question === '' && $response === '') {
                continue;
            }

            if ($question === '' || $response === '') {
                return 'Chaque carte doit avoir une question et une reponse.';
            }

            $hasCompleteQuestionResponse = true;
        }

        return $hasCompleteQuestionResponse
            ? null
            : 'Ajoute au moins une question et sa reponse.';
    }

    private function findMatiereName(?int $matiereId, int $ownerId): string {
        if ($matiereId === null) {
            return '';
        }

        $matiere = $this->matiereRepo->findByIdForUser($matiereId, $ownerId);

        return $matiere?->name ?? '';
    }
}
