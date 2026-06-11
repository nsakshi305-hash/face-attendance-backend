<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;
    
    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'face_attendance';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        
        try {
            $this->connection = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $username,
                $password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection()
    {
        return $this->connection;
    }
}