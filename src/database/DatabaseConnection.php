<?php
/**
 * Classe de connexion à la base de données
 */
class DatabaseConnection {
	private static ?DatabaseConnection $instance = null;
	private \PDO $pdo;

	private function __construct() {
        $config = [];
        $configFile = __DIR__ . '/../config.php';
        //Vérifie que le fichier existe avant de le charger
        if (file_exists($configFile)) {
            $config = require $configFile;
        }

        // Configure la connexion vers la base de données POSTGRESQL 
        $dsn = $config['dsn'] ?? getenv('DB_DSN');
        $user = $config['user'] ?? getenv('DB_USER') ?: null;
        $pass = $config['pass'] ?? getenv('DB_PASS') ?: null;
        
		$options = [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_EMULATE_PREPARES => false,
		];

        //Si l'utilisateur n'est pas défini,
		if ($user !== null) {
			$this->pdo = new \PDO($dsn, $user, $pass, $options);
		} else {
			$this->pdo = new \PDO($dsn, null, null, $options);
		}
	}

    //Récupère l'instance de la connexion à la base de données (singleton)
	public static function getInstance(): DatabaseConnection {
		if (self::$instance === null) {
			self::$instance = new DatabaseConnection();
		}

		return self::$instance;
	}

    // Récupère l'objet PDO pour exécuter des requêtes SQL
	public function getPdo(): \PDO {
		return $this->pdo;
	}
}
