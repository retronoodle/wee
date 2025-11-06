<?php

namespace App;

use PDO;
use PDOException;

/**
 * Database connection and query execution
 */
class Database {

    private static $instance;
    private $pdo;
    private $config;

    private function __construct() {
        $this->config = [
            'driver' => \wee::config('database.driver', 'mysql'),
            'host' => \wee::config('database.host', 'localhost'),
            'port' => \wee::config('database.port', '3306'),
            'database' => \wee::config('database.database', 'wee'),
            'username' => \wee::config('database.username', 'root'),
            'password' => \wee::config('database.password', ''),
            'charset' => \wee::config('database.charset', 'utf8mb4'),
            'collation' => \wee::config('database.collation', 'utf8mb4_unicode_ci'),
            'sqlite_path' => \wee::config('database.sqlite_path', __DIR__ . '/../database.sqlite'),
        ];

        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $driver = $this->config['driver'];

            if ($driver === 'sqlite') {
                $dsn = "sqlite:{$this->config['sqlite_path']}";
                $this->pdo = new PDO($dsn);
            } else {
                $dsn = "{$driver}:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
                $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password']);
            }

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function table($table) {
        return new QueryBuilder($table, $this);
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollback();
    }
}
