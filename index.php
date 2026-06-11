<?php
require_once __DIR__ . '/cors.php';

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Controllers/AttendanceController.php';
require_once __DIR__ . '/src/Controllers/AdminController.php';
require_once __DIR__ . '/src/Controllers/EmployeeController.php';

use App\Controllers\AuthController;
use App\Controllers\AttendanceController;
use App\Controllers\AdminController;
use App\Controllers\EmployeeController;

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

$request_uri = strtok($request_uri, '?');

header('Content-Type: application/json');

if ($method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Auth Routes
if ($method == 'POST' && strpos($request_uri, '/api/auth/register') !== false) {
    $controller = new AuthController();
    $controller->register();
    exit();
}

if ($method == 'POST' && strpos($request_uri, '/api/auth/login') !== false) {
    $controller = new AuthController();
    $controller->login();
    exit();
}

// OTP Routes
if ($method == 'POST' && strpos($request_uri, '/api/auth/send-otp') !== false) {
    $controller = new AuthController();
    $controller->sendOTP();
    exit();
}

if ($method == 'POST' && strpos($request_uri, '/api/auth/verify-otp') !== false) {
    $controller = new AuthController();
    $controller->verifyOTP();
    exit();
}

// Attendance Routes
if ($method == 'POST' && strpos($request_uri, '/api/attendance/clock-in') !== false) {
    $controller = new AttendanceController();
    $controller->clockIn();
    exit();
}

if ($method == 'POST' && strpos($request_uri, '/api/attendance/clock-out') !== false) {
    $controller = new AttendanceController();
    $controller->clockOut();
    exit();
}

if ($method == 'GET' && strpos($request_uri, '/api/attendance/history') !== false) {
    $controller = new AttendanceController();
    $controller->getHistory();
    exit();
}

// ============ ADMIN ROUTES ============
if ($method == 'GET' && strpos($request_uri, '/api/admin/users') !== false) {
    $controller = new AdminController();
    $controller->getAllUsers();
    exit();
}

if ($method == 'GET' && strpos($request_uri, '/api/admin/attendance') !== false) {
    $controller = new AdminController();
    $controller->getAllAttendance();
    exit();
}

if ($method == 'GET' && strpos($request_uri, '/api/admin/stats') !== false) {
    $controller = new AdminController();
    $controller->getDashboardStats();
    exit();
}

if ($method == 'PUT' && strpos($request_uri, '/api/admin/user-role') !== false) {
    $controller = new AdminController();
    $controller->updateUserRole();
    exit();
}
// ============ END ADMIN ROUTES ============

// ============ EMPLOYEE ROUTES ============
if ($method == 'GET' && strpos($request_uri, '/api/admin/employees') !== false) {
    $controller = new EmployeeController();
    $controller->getAllEmployees();
    exit();
}

if ($method == 'POST' && strpos($request_uri, '/api/admin/employees') !== false) {
    $controller = new EmployeeController();
    $controller->addEmployee();
    exit();
}
// ============ END EMPLOYEE ROUTES ============

// Root endpoint
if ($request_uri == '/' || $request_uri == '/index.php') {
    echo json_encode(['success' => true, 'message' => 'Backend is working']);
    exit();
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Endpoint not found',
    'path' => $request_uri,
    'method' => $method
]);