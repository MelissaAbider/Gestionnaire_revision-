<?php
/**
 * Classe de connexion à la base de données
 */
class DatabaseConnection {
	private static ?DatabaseConnection $instance = null;
	private \PDO $pdo;

	private function __construct() {
		// Configure via env vars if present, otherwise use sqlite file
		$dsn = getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/database.sqlite';
		$user = getenv('DB_USER') ?: null;
		$pass = getenv('DB_PASS') ?: null;

		$options = [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_EMULATE_PREPARES => false,
		];

		// If using MySQL, ensure proper charset on connection
		if (stripos($dsn, 'mysql:') === 0) {
			if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
				$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8mb4'";
			}
		}

		if ($user !== null) {
			$this->pdo = new \PDO($dsn, $user, $pass, $options);
		} else {
			$this->pdo = new \PDO($dsn, null, null, $options);
		}
	}

	public static function getInstance(): DatabaseConnection {
		if (self::$instance === null) {
			self::$instance = new DatabaseConnection();
		}

		return self::$instance;
	}

	public function getPdo(): \PDO {
		return $this->pdo;
	}
}
