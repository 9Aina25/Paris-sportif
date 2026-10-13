<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        require_once dirname(__DIR__) . '/config.php';
        global $pdo;
        $this->conn = $pdo;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>