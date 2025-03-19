<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get messages for a channel
    if (!isset($_GET['channel_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Channel ID is required']);
        exit();
    }

    try {
        $stmt = $conn->prepare("
            SELECT m.*, u.username 
            FROM messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.channel_id = ? 
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$_GET['channel_id']]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($messages);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create a new message
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['channel_id']) || !isset($data['content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Channel ID and content are required']);
        exit();
    }

    try {
        // Start transaction
        $conn->beginTransaction();

        // Insert the message
        $stmt = $conn->prepare("
            INSERT INTO messages (channel_id, user_id, content) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $data['channel_id'],
            $_SESSION['user_id'],
            $data['content']
        ]);
        
        $messageId = $conn->lastInsertId();

        // Get the complete message data with username
        $stmt = $conn->prepare("
            SELECT m.*, u.username 
            FROM messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.id = ?
        ");
        $stmt->execute([$messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        // Commit transaction
        $conn->commit();

        echo json_encode($message);
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?> 