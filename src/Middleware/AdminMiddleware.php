<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AdminMiddleware
{
    public static function verify($db)
    {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        if (!$token) {
            return false;
        }
        
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            
            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$decoded->user_id]);
            $user = $stmt->fetch();
            
            return $user && $user['role'] === 'admin';
        } catch (\Exception $e) {
            return false;
        }
    }
}
?>