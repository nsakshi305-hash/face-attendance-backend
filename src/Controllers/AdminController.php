<?php

namespace App\Controllers;

use App\Models\User;
use App\Middleware\AdminMiddleware;
use PDO;

class AdminController
{
    private $db;
    
    public function __construct()
    {
        $userModel = new User();
        $this->db = $userModel->getConnection();
    }
    
    public function getAllUsers()
    {
        if (!AdminMiddleware::verify($this->db)) {
            $this->sendResponse(false, 'Unauthorized. Admin access required.', null, 403);
            return;
        }
        
        $stmt = $this->db->query("
            SELECT id, name, email, role, is_active, created_at 
            FROM users 
            ORDER BY created_at DESC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(true, 'Users retrieved', $users);
    }
    
    public function getAllAttendance()
    {
        if (!AdminMiddleware::verify($this->db)) {
            $this->sendResponse(false, 'Unauthorized. Admin access required.', null, 403);
            return;
        }
        
        $stmt = $this->db->query("
            SELECT a.id, a.type, a.location_lat, a.location_lng, a.created_at,
                   u.name, u.email
            FROM attendance a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT 100
        ");
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(true, 'Attendance records retrieved', $attendance);
    }
    
    public function getUserAttendance($userId)
    {
        if (!AdminMiddleware::verify($this->db)) {
            $this->sendResponse(false, 'Unauthorized. Admin access required.', null, 403);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT id, type, location_lat, location_lng, created_at 
            FROM attendance 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(true, 'User attendance retrieved', $attendance);
    }
    
    public function updateUserRole()
    {
        if (!AdminMiddleware::verify($this->db)) {
            $this->sendResponse(false, 'Unauthorized. Admin access required.', null, 403);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['user_id']) || empty($data['role'])) {
            $this->sendResponse(false, 'User ID and role are required', null, 400);
            return;
        }
        
        $stmt = $this->db->prepare("
            UPDATE users SET role = ? WHERE id = ?
        ");
        
        if ($stmt->execute([$data['role'], $data['user_id']])) {
            $this->sendResponse(true, 'User role updated successfully');
        } else {
            $this->sendResponse(false, 'Failed to update user role', null, 500);
        }
    }
    
    public function getDashboardStats()
    {
        if (!AdminMiddleware::verify($this->db)) {
            $this->sendResponse(false, 'Unauthorized. Admin access required.', null, 403);
            return;
        }
        
        $stats = [];
        
        // Total users - handle empty table
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        $stats['total_users'] = $result ? (int)$result['count'] : 0;
        
        // Today's attendance - handle empty table
        $stmt = $this->db->query("
            SELECT COUNT(*) as count FROM attendance 
            WHERE DATE(created_at) = CURDATE()
        ");
        $result = $stmt->fetch();
        $stats['today_attendance'] = $result ? (int)$result['count'] : 0;
        
        // This week's attendance - handle empty table
        $stmt = $this->db->query("
            SELECT COUNT(*) as count FROM attendance 
            WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())
        ");
        $result = $stmt->fetch();
        $stats['weekly_attendance'] = $result ? (int)$result['count'] : 0;
        
        // Total attendance - handle empty table
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM attendance");
        $result = $stmt->fetch();
        $stats['total_attendance'] = $result ? (int)$result['count'] : 0;
        
        $this->sendResponse(true, 'Dashboard stats retrieved', $stats);
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
?>