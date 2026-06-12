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
        // Get database URL from environment
        $databaseUrl = getenv('DATABASE_URL');
        
        if ($databaseUrl) {
            // Parse PostgreSQL connection string
            $dbopts = parse_url($databaseUrl);
            $host = $dbopts["host"] ?? 'localhost';
            $port = $dbopts["port"] ?? '5432';
            $dbname = ltrim($dbopts["path"], '/');
            $username = $dbopts["user"] ?? 'root';
            $password = $dbopts["pass"] ?? '';
            
            try {
                $this->connection = new PDO(
                    "pgsql:host={$host};port={$port};dbname={$dbname}",
                    $username,
                    $password
                );
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        } else {
            die("DATABASE_URL environment variable not set");
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