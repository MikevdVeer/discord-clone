<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if (!isset($_GET['channel_id'])) {
    http_response_code(400);
    exit('Channel ID is required');
}

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Function to send SSE data
function sendSSE($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Get the last message ID from the client
$lastMessageId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

while (true) {
    try {
        // Check for new messages
        $stmt = $conn->prepare("
            SELECT m.*, u.username 
            FROM messages m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.channel_id = ? AND m.id > ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$_GET['channel_id'], $lastMessageId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($messages)) {
            foreach ($messages as $message) {
                sendSSE([
                    'type' => 'new-message',
                    'message' => $message
                ]);
                $lastMessageId = max($lastMessageId, $message['id']);
            }
        }

        // Check if client is still connected
        if (connection_aborted()) {
            break;
        }

        // Sleep for a short time to prevent excessive database queries
        sleep(1);
    } catch (PDOException $e) {
        error_log('Database error in messages_stream.php: ' . $e->getMessage());
        break;
    }
} 