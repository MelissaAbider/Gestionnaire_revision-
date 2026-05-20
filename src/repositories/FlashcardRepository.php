<?php
/**
 * Repository des flashcards
 */

class FlashcardRepository {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    public function create(Flashcard $flashcard): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO flashcards (owner_id, matiere_id, title, subject, theme, created_at, updated_at)
            VALUES (:owner_id, :matiere_id, :title, :subject, :theme, :created_at, :updated_at)
            RETURNING id'
        );
        $stmt->execute([
            'owner_id' => $flashcard->proprietaire,
            'matiere_id' => $flashcard->matiereId,
            'title' => $flashcard->title,
            'subject' => $flashcard->subject,
            'theme' => $flashcard->theme,
            'created_at' => $flashcard->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $flashcard->updatedAt->format('Y-m-d H:i:s'),
        ]);

        return (int)$stmt->fetchColumn();
    }

    /**
     * @param int[] $sharedUserIds
     */
    public function createFromForm(
        int $ownerId,
        string $title,
        string $content,
        ?int $matiereId,
        string $matiereName,
        array $sharedUserIds
    ): int {
        $now = date('Y-m-d H:i:s');
        $themePreview = mb_substr($content, 0, 255);

        $this->pdo->beginTransaction();

        try {
            if ($this->columnExists('flashcards', 'matiere_id')) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO flashcards (owner_id, matiere_id, title, subject, theme, created_at, updated_at)
                    VALUES (:owner_id, :matiere_id, :title, :subject, :theme, :created_at, :updated_at)
                    RETURNING id'
                );
                $stmt->execute([
                    'owner_id' => $ownerId,
                    'matiere_id' => $matiereId,
                    'title' => $title,
                    'subject' => $matiereName,
                    'theme' => $themePreview,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO flashcards (owner_id, title, subject, theme, created_at, updated_at)
                    VALUES (:owner_id, :title, :subject, :theme, :created_at, :updated_at)
                    RETURNING id'
                );
                $stmt->execute([
                    'owner_id' => $ownerId,
                    'title' => $title,
                    'subject' => $matiereName,
                    'theme' => $themePreview,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $flashcardId = (int)$stmt->fetchColumn();
            $this->saveContent($flashcardId, $content);
            $this->syncShares($flashcardId, $ownerId, $sharedUserIds);
            $this->pdo->commit();

            return $flashcardId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @param int[] $sharedUserIds
     */
    public function updateFromForm(
        int $id,
        int $ownerId,
        string $title,
        string $content,
        ?int $matiereId,
        string $matiereName,
        array $sharedUserIds
    ): void {
        $now = date('Y-m-d H:i:s');
        $themePreview = mb_substr($content, 0, 255);

        if (!$this->findByIdForUser($id, $ownerId)) {
            throw new \RuntimeException('Fiche introuvable.');
        }

        $this->pdo->beginTransaction();

        try {
            if ($this->columnExists('flashcards', 'matiere_id')) {
                $stmt = $this->pdo->prepare(
                    'UPDATE flashcards
                    SET title = :title,
                        matiere_id = :matiere_id,
                        subject = :subject,
                        theme = :theme,
                        updated_at = :updated_at
                    WHERE id = :id AND owner_id = :owner_id'
                );
                $stmt->execute([
                    'id' => $id,
                    'owner_id' => $ownerId,
                    'title' => $title,
                    'matiere_id' => $matiereId,
                    'subject' => $matiereName,
                    'theme' => $themePreview,
                    'updated_at' => $now,
                ]);
            } else {
                $stmt = $this->pdo->prepare(
                    'UPDATE flashcards
                    SET title = :title,
                        subject = :subject,
                        theme = :theme,
                        updated_at = :updated_at
                    WHERE id = :id AND owner_id = :owner_id'
                );
                $stmt->execute([
                    'id' => $id,
                    'owner_id' => $ownerId,
                    'title' => $title,
                    'subject' => $matiereName,
                    'theme' => $themePreview,
                    'updated_at' => $now,
                ]);
            }

            $this->saveContent($id, $content);
            $this->syncShares($id, $ownerId, $sharedUserIds);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function findByIdForUser(int $id, int $ownerId): ?Flashcard {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM flashcards WHERE id = :id AND owner_id = :owner_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'owner_id' => $ownerId,
        ]);
        $row = $stmt->fetch();

        return $row ? FlashcardFactory::fromDatabaseRow($row) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findFormDataForUser(int $id, int $ownerId): ?array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM flashcards WHERE id = :id AND owner_id = :owner_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'owner_id' => $ownerId,
        ]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int)$row['id'],
            'title' => $row['title'] ?? '',
            'content' => $this->findContentForFlashcard((int)$row['id']) ?: ($row['theme'] ?? ''),
            'matiereId' => isset($row['matiere_id']) ? (string)$row['matiere_id'] : '',
            'sharedUserIds' => $this->findSharedUserIds((int)$row['id']),
        ];
    }

    public function deleteForUser(int $id, int $ownerId): bool {
        if (!$this->findByIdForUser($id, $ownerId)) {
            return false;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare('DELETE FROM shares WHERE flashcard_id = :flashcard_id');
            $stmt->execute(['flashcard_id' => $id]);

            $stmt = $this->pdo->prepare('DELETE FROM question_responses WHERE flashcard_id = :flashcard_id');
            $stmt->execute(['flashcard_id' => $id]);

            $stmt = $this->pdo->prepare('DELETE FROM flashcards WHERE id = :id AND owner_id = :owner_id');
            $stmt->execute([
                'id' => $id,
                'owner_id' => $ownerId,
            ]);

            $deleted = $stmt->rowCount() > 0;
            $this->pdo->commit();

            return $deleted;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @return Flashcard[]
     */
    public function findByMatiereForUser(int $matiereId, int $ownerId): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM flashcards
            WHERE matiere_id = :matiere_id AND owner_id = :owner_id
            ORDER BY updated_at DESC'
        );
        $stmt->execute([
            'matiere_id' => $matiereId,
            'owner_id' => $ownerId,
        ]);

        return array_map(
            fn(array $row): Flashcard => FlashcardFactory::fromDatabaseRow($row),
            $stmt->fetchAll()
        );
    }

    public function findRecentActivityForUser(int $ownerId, int $limit = 5): array {
        $hasMatiereRelation = $this->tableExists('matieres')
            && $this->columnExists('flashcards', 'matiere_id');

        if ($hasMatiereRelation) {
            $stmt = $this->pdo->prepare(
                'SELECT
                    f.id,
                    f.title,
                    f.subject,
                    f.theme,
                    f.created_at,
                    f.updated_at,
                    COALESCE(m.name, NULLIF(f.subject, \'\'), \'Sans matiere\') AS matiere_name,
                    COALESCE(m.color, \'blue\') AS matiere_color
                FROM flashcards f
                LEFT JOIN matieres m ON m.id = f.matiere_id AND m.owner_id = f.owner_id
                WHERE f.owner_id = :owner_id
                ORDER BY f.updated_at DESC, f.created_at DESC
                LIMIT :limit'
            );
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT
                    f.id,
                    f.title,
                    f.subject,
                    f.theme,
                    f.created_at,
                    f.updated_at,
                    COALESCE(NULLIF(f.subject, \'\'), \'Sans matiere\') AS matiere_name,
                    \'blue\' AS matiere_color
                FROM flashcards f
                WHERE f.owner_id = :owner_id
                ORDER BY f.updated_at DESC, f.created_at DESC
                LIMIT :limit'
            );
        }

        $stmt->bindValue(':owner_id', $ownerId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findListForUser(int $ownerId): array {
        $hasMatiereRelation = $this->tableExists('matieres')
            && $this->columnExists('flashcards', 'matiere_id');

        if ($hasMatiereRelation) {
            $stmt = $this->pdo->prepare(
                'SELECT f.id, f.title, f.subject, f.theme, f.created_at, f.updated_at,
                    COALESCE(m.name, NULLIF(f.subject, \'\'), NULLIF(f.theme, \'\'), \'Sans matière\') AS matiere_name,
                    COALESCE(m.color, \'blue\') AS matiere_color
                FROM flashcards f
                LEFT JOIN matieres m ON m.id = f.matiere_id AND m.owner_id = f.owner_id
                WHERE f.owner_id = :owner_id
                ORDER BY f.created_at DESC, f.id DESC'
            );
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT f.id, f.title, f.subject, f.theme, f.created_at, f.updated_at,
                    COALESCE(NULLIF(f.subject, \'\'), NULLIF(f.theme, \'\'), \'Sans matière\') AS matiere_name,
                    \'blue\' AS matiere_color
                FROM flashcards f
                WHERE f.owner_id = :owner_id
                ORDER BY f.created_at DESC, f.id DESC'
            );
        }

        $stmt->execute(['owner_id' => $ownerId]);
        $rows = $stmt->fetchAll();
        $sharesByFlashcard = $this->findSharesByFlashcardIds(array_column($rows, 'id'));

        return array_map(
            function (array $row) use ($sharesByFlashcard): array {
                $id = (int)$row['id'];

                return [
                    'id' => $id,
                    'title' => $row['title'] ?? '',
                    'subject' => $row['subject'] ?? '',
                    'theme' => $row['theme'] ?? '',
                    'created_at' => $row['created_at'] ?? null,
                    'updated_at' => $row['updated_at'] ?? null,
                    'matiere_name' => $row['matiere_name'] ?? 'Sans matière',
                    'matiere_color' => $row['matiere_color'] ?? 'blue',
                    'shared_with' => $sharesByFlashcard[$id] ?? [],
                ];
            },
            $rows
        );
    }

    /**
     * @param array<int, int|string> $flashcardIds
     * @return array<int, array<int, array<string, string>>>
     */
    private function findSharesByFlashcardIds(array $flashcardIds): array {
        $ids = array_values(array_unique(array_map('intval', $flashcardIds)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $key = 'id' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'SELECT s.flashcard_id, u.firstname, u.lastname, u.email
            FROM shares s
            INNER JOIN users u ON u.id = s.user_id
            WHERE s.flashcard_id IN (' . implode(', ', $placeholders) . ')
            ORDER BY s.shared_at ASC, u.firstname ASC'
        );
        $stmt->execute($params);

        $shares = [];
        foreach ($stmt->fetchAll() as $row) {
            $flashcardId = (int)$row['flashcard_id'];
            $shares[$flashcardId][] = [
                'firstname' => $row['firstname'] ?? '',
                'lastname' => $row['lastname'] ?? '',
                'email' => $row['email'] ?? '',
            ];
        }

        return $shares;
    }

    private function saveContent(int $flashcardId, string $content): void {
        $stmt = $this->pdo->prepare(
            'SELECT id
            FROM question_responses
            WHERE flashcard_id = :flashcard_id
            ORDER BY id ASC
            LIMIT 1'
        );
        $stmt->execute(['flashcard_id' => $flashcardId]);
        $questionResponseId = $stmt->fetchColumn();

        if ($questionResponseId) {
            $stmt = $this->pdo->prepare(
                'UPDATE question_responses
                SET question = :question, response = :response
                WHERE id = :id'
            );
            $stmt->execute([
                'id' => (int)$questionResponseId,
                'question' => 'Contenu',
                'response' => $content,
            ]);

            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO question_responses (flashcard_id, question, response)
            VALUES (:flashcard_id, :question, :response)'
        );
        $stmt->execute([
            'flashcard_id' => $flashcardId,
            'question' => 'Contenu',
            'response' => $content,
        ]);
    }

    private function findContentForFlashcard(int $flashcardId): string {
        $stmt = $this->pdo->prepare(
            'SELECT response
            FROM question_responses
            WHERE flashcard_id = :flashcard_id
            ORDER BY id ASC
            LIMIT 1'
        );
        $stmt->execute(['flashcard_id' => $flashcardId]);

        return (string)($stmt->fetchColumn() ?: '');
    }

    /**
     * @return int[]
     */
    private function findSharedUserIds(int $flashcardId): array {
        $stmt = $this->pdo->prepare(
            'SELECT user_id FROM shares WHERE flashcard_id = :flashcard_id ORDER BY user_id ASC'
        );
        $stmt->execute(['flashcard_id' => $flashcardId]);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /**
     * @param int[] $sharedUserIds
     */
    private function syncShares(int $flashcardId, int $ownerId, array $sharedUserIds): void {
        $stmt = $this->pdo->prepare('DELETE FROM shares WHERE flashcard_id = :flashcard_id');
        $stmt->execute(['flashcard_id' => $flashcardId]);

        $validUserIds = $this->findValidSharedUserIds($sharedUserIds, $ownerId);
        if (empty($validUserIds)) {
            return;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO shares (flashcard_id, user_id, shared_at)
            VALUES (:flashcard_id, :user_id, :shared_at)'
        );
        $now = date('Y-m-d H:i:s');

        foreach ($validUserIds as $userId) {
            $stmt->execute([
                'flashcard_id' => $flashcardId,
                'user_id' => $userId,
                'shared_at' => $now,
            ]);
        }
    }

    /**
     * @param int[] $userIds
     * @return int[]
     */
    private function findValidSharedUserIds(array $userIds, int $ownerId): array {
        $ids = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        $ids = array_values(array_filter($ids, fn(int $id): bool => $id !== $ownerId));

        if (empty($ids)) {
            return [];
        }

        $placeholders = [];
        $params = ['owner_id' => $ownerId];
        foreach ($ids as $index => $id) {
            $key = 'id' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id
            FROM users
            WHERE id IN (' . implode(', ', $placeholders) . ')
            AND id <> :owner_id'
        );
        $stmt->execute($params);

        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    private function tableExists(string $tableName): bool {
        return !empty($this->getTableColumns($tableName));
    }

    private function columnExists(string $tableName, string $columnName): bool {
        return in_array($columnName, $this->getTableColumns($tableName), true);
    }

    /**
     * @return string[]
     */
    private function getTableColumns(string $tableName): array {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
            return [];
        }

        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        try {
            if ($driver === 'pgsql') {
                $stmt = $this->pdo->prepare(
                    'SELECT column_name
                    FROM information_schema.columns
                    WHERE table_schema = current_schema()
                    AND table_name = :table_name'
                );
                $stmt->execute(['table_name' => $tableName]);

                return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
            }

            if ($driver === 'sqlite') {
                $stmt = $this->pdo->query('PRAGMA table_info(' . $tableName . ')');

                return array_map(
                    fn(array $row): string => (string)$row['name'],
                    $stmt ? $stmt->fetchAll() : []
                );
            }

            if ($driver === 'mysql') {
                $stmt = $this->pdo->prepare(
                    'SELECT column_name
                    FROM information_schema.columns
                    WHERE table_schema = DATABASE()
                    AND table_name = :table_name'
                );
                $stmt->execute(['table_name' => $tableName]);

                return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
            }
        } catch (\Throwable $e) {
            return [];
        }

        return [];
    }
}
