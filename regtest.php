<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/src/Models/User.php';
use App\Models\User;

$userModel = new User();
$db = $userModel->getConnection();

$name = 'Test User';
$email = 'test_' . time() . '@example.com';
$password = password_hash('123456', PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    
    if ($stmt->execute([$name, $email, $password])) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user_id' => $db->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>