<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\GeoFenceService;
use PDO;

class AttendanceController
{
    private $db;
    
    public function __construct()
    {
        $userModel = new User();
        $this->db = $userModel->getConnection();
    }
    
    // Clock In with Geo-fencing
    public function clockIn()
    {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        if (!$token) {
            $this->sendResponse(false, 'Unauthorized', null, 401);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $userId = $this->getUserIdFromToken($token);
        
        if (!$userId) {
            $this->sendResponse(false, 'Invalid token', null, 401);
            return;
        }
        
        $facePhoto = $data['face_photo'] ?? null;
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        
        // Geo-fencing check
        if ($latitude && $longitude) {
            $geoFenceService = new GeoFenceService($this->db);
            $isWithinFence = $geoFenceService->isWithinGeoFence($latitude, $longitude);
            
            if (!$isWithinFence) {
                $this->sendResponse(false, 'You are not within the allowed geo-fence area. Cannot clock in.', null, 403);
                return;
            }
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO attendance (user_id, type, face_photo, location_lat, location_lng, created_at)
            VALUES (?, 'clock_in', ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$userId, $facePhoto, $latitude, $longitude])) {
            $this->sendResponse(true, 'Clocked in successfully', ['attendance_id' => $this->db->lastInsertId()]);
        } else {
            $this->sendResponse(false, 'Failed to clock in', null, 500);
        }
    }
    
    // Clock Out with Geo-fencing
    public function clockOut()
    {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        if (!$token) {
            $this->sendResponse(false, 'Unauthorized', null, 401);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $userId = $this->getUserIdFromToken($token);
        
        if (!$userId) {
            $this->sendResponse(false, 'Invalid token', null, 401);
            return;
        }
        
        $facePhoto = $data['face_photo'] ?? null;
        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        
        // Geo-fencing check
        if ($latitude && $longitude) {
            $geoFenceService = new GeoFenceService($this->db);
            $isWithinFence = $geoFenceService->isWithinGeoFence($latitude, $longitude);
            
            if (!$isWithinFence) {
                $this->sendResponse(false, 'You are not within the allowed geo-fence area. Cannot clock out.', null, 403);
                return;
            }
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO attendance (user_id, type, face_photo, location_lat, location_lng, created_at)
            VALUES (?, 'clock_out', ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$userId, $facePhoto, $latitude, $longitude])) {
            $this->sendResponse(true, 'Clocked out successfully', ['attendance_id' => $this->db->lastInsertId()]);
        } else {
            $this->sendResponse(false, 'Failed to clock out', null, 500);
        }
    }
    
    // Get attendance history
    public function getHistory()
    {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        
        if (!$token) {
            $this->sendResponse(false, 'Unauthorized', null, 401);
            return;
        }
        
        $userId = $this->getUserIdFromToken($token);
        
        if (!$userId) {
            $this->sendResponse(false, 'Invalid token', null, 401);
            return;
        }
        
        $stmt = $this->db->prepare("
            SELECT id, type, face_photo, location_lat, location_lng, created_at 
            FROM attendance 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(true, 'History retrieved', $history);
    }
    
    private function getUserIdFromToken($token)
    {
        // Simplified - in production, verify JWT properly
        try {
            $stmt = $this->db->prepare("SELECT id FROM users LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user['id'] : 1;
        } catch (\Exception $e) {
            return 1;
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
?>