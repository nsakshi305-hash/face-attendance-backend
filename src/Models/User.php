<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class User
{
    private $db;
    
    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    public function getConnection()
    {
        return $this->db;
    }
    
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, photo, role, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['password'],
            $data['photo'] ?? null,
            $data['role'] ?? 'user'
        ]);
    }
}