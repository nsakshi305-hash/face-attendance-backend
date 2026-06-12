<?php

namespace App\Controllers;

use App\Models\User;
use PDO;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private $userModel;
    private $db;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->db = $this->userModel->getConnection();
    }
    public function register()
    {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
        $this->sendResponse(false, 'Name, email and password are required', null, 400);
        return;
    }
    
    $existingUser = $this->userModel->findByEmail($data['email']);
    if ($existingUser) {
        $this->sendResponse(false, 'Email already registered', null, 400);
        return;
    }
    
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Get role from request, default to 'user'
    $role = $data['role'] ?? 'user';
    
    $stmt = $this->db->prepare("
        INSERT INTO users (name, email, password, role, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([$data['name'], $data['email'], $hashedPassword, $role])) {
        $userId = $this->db->lastInsertId();
        $this->sendResponse(true, 'Registration successful', ['user_id' => $userId], 201);
    } else {
        $this->sendResponse(false, 'Registration failed', null, 500);
    }
}
    
        
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['email']) || empty($data['password'])) {
            $this->sendResponse(false, 'Email and password are required', null, 400);
            return;
        }
        
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user) {
            $this->sendResponse(false, 'Invalid credentials', null, 401);
            return;
        }
        
        if (!password_verify($data['password'], $user['password'])) {
            $this->sendResponse(false, 'Invalid credentials', null, 401);
            return;
        }
        
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'exp' => time() + 3600
        ];
        
        $jwt = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
        
        $this->sendResponse(true, 'Login successful', [
            'token' => $jwt,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    }
    
    public function sendOTP()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['email'])) {
            $this->sendResponse(false, 'Email is required', null, 400);
            return;
        }
        
        $otp = rand(100000, 999999);
        
        $stmt = $this->db->prepare("
            INSERT INTO otp_verification (email, otp, expires_at)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))
        ");
        
        if ($stmt->execute([$data['email'], $otp])) {
            $this->sendResponse(true, 'OTP sent successfully', ['otp' => $otp]);
        } else {
            $this->sendResponse(false, 'Failed to send OTP', null, 500);
        }
    }
    
    public function verifyOTP()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['email']) || empty($data['otp'])) {
            $this->sendResponse(false, 'Email and OTP are required', null, 400);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT id FROM otp_verification 
            WHERE email = ? AND otp = ? AND is_verified = FALSE 
            AND expires_at > NOW()
            ORDER BY id DESC LIMIT 1
        ");
        
        $stmt->execute([$data['email'], $data['otp']]);
        $result = $stmt->fetch();
        
        if ($result) {
            $update = $this->db->prepare("
                UPDATE otp_verification SET is_verified = TRUE WHERE id = ?
            ");
            $update->execute([$result['id']]);
            $this->sendResponse(true, 'OTP verified successfully');
        } else {
            $this->sendResponse(false, 'Invalid or expired OTP', null, 400);
        }
    }
    
    private function sendResponse($success, $message, $data = null, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
    }
}