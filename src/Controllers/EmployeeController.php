<?php

namespace App\Controllers;

use App\Models\User;
use App\Middleware\AdminMiddleware;
use PDO;

class EmployeeController
{
    private $db;
    
    public function __construct()
    {
        $userModel = new User();
        $this->db = $userModel->getConnection();
    }
    
    public function getAllEmployees()
    {
        if (!AdminMiddleware::verify($this->db)) {
            $this->sendResponse(false, 'Unauthorized. Admin access required.', null, 403);
            return;
        }
        
        $stmt = $this->db->query("
            SELECT id, employee_id, name, email, department, position, phone, join_date, salary, status, created_at 
            FROM employees 
            ORDER BY employee_id
        ");
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->sendResponse(true, 'Employees retrieved', $employees);
    }
    
    public function addEmployee()
    {
        if (!AdminMiddleware::verify($this->db)) {
            $this->sendResponse(false, 'Unauthorized. Admin access required.', null, 403);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        $stmt = $this->db->prepare("
            INSERT INTO employees (employee_id, name, email, department, position, phone, join_date, salary, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([
            $data['employee_id'], $data['name'], $data['email'], 
            $data['department'], $data['position'], $data['phone'],
            $data['join_date'], $data['salary'], $data['status']
        ])) {
            $this->sendResponse(true, 'Employee added successfully', ['id' => $this->db->lastInsertId()]);
        } else {
            $this->sendResponse(false, 'Failed to add employee', null, 500);
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