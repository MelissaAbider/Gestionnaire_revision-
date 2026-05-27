<?php
/**
 * Repository des partages
 *
 * RESPONSABLE : Alban COUSIN
 * Perimetre : fiches partagees avec un utilisateur, filtres et statistiques de partage.
 */

class ShareRepository {
    private \PDO $pdo;

    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getPdo();
    }

    public function findSharedWithUser(int $userId, array $filters = []): array {
        $where = ['s.user_id = :user_id'];
        $params = ['user_id' => $userId];

        $search = trim($filters['q'] ?? '');
        if ($search !== '') {
            $where[] = '(LOWER(f.title) LIKE :search OR LOWER(COALESCE(f.subject, \'\')) LIKE :search)';
            $params['search'] = '%' . strtolower($search) . '%';
        }

        $matiere = trim($filters['matiere'] ?? '');
        if ($matiere !== '') {
            $where[] = 'LOWER(COALESCE(m.name, f.subject, \'\')) = :matiere';
            $params['matiere'] = strtolower($matiere);
        }

        $orderBy = ($filters['sort'] ?? 'recent') === 'oldest' ? 's.shared_at ASC' : 's.shared_at DESC';

        $stmt = $this->pdo->prepare(
            'SELECT
                s.id AS share_id,
                s.shared_at,
                f.id AS id,
                f.id AS flashcard_id,
                f.title,
                f.subject,
                COUNT(qr.id) AS question_count,
                COALESCE(m.name, f.subject, \'Sans matiere\') AS matiere_name,
                COALESCE(m.color, \'blue\') AS matiere_color,
                u.id AS owner_id,
                u.firstname AS owner_firstname,
                u.lastname AS owner_lastname,
                u.email AS owner_email
            FROM shares s
            INNER JOIN flashcards f ON f.id = s.flashcard_id
            INNER JOIN users u ON u.id = f.owner_id
            LEFT JOIN matieres m ON m.id = f.matiere_id
            LEFT JOIN question_responses qr ON qr.flashcard_id = f.id
            WHERE ' . implode(' AND ', $where) . '
            GROUP BY s.id, s.shared_at, f.id, f.title, f.subject, m.name, m.color, u.id, u.firstname, u.lastname, u.email
            ORDER BY ' . $orderBy
        );
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findSharedMatieresForUser(int $userId): array {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT COALESCE(m.name, f.subject, \'Sans matiere\') AS name
            FROM shares s
            INNER JOIN flashcards f ON f.id = s.flashcard_id
            LEFT JOIN matieres m ON m.id = f.matiere_id
            WHERE s.user_id = :user_id
            ORDER BY name ASC'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_filter(array_column($stmt->fetchAll(), 'name'));
    }

    public function countSharedWithUser(int $userId): int {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM shares s
            INNER JOIN flashcards f ON f.id = s.flashcard_id
            WHERE s.user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        return (int)$stmt->fetchColumn();
    }

    public function countSharedWithUserSince(int $userId, \DateTimeInterface $since): int {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM shares s
            INNER JOIN flashcards f ON f.id = s.flashcard_id
            WHERE s.user_id = :user_id
            AND s.shared_at >= :since'
        );
        $stmt->execute([
            'user_id' => $userId,
            'since' => $since->format('Y-m-d H:i:s'),
        ]);

        return (int)$stmt->fetchColumn();
    }
}
