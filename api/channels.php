<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get channels for a server
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['server_id'])) {
    $server_id = filter_input(INPUT_GET, 'server_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Check if user is a member of the server
    $stmt = $conn->prepare("
        SELECT 1 
        FROM server_members 
        WHERE server_id = ? AND user_id = ?
    ");
    $stmt->execute([$server_id, $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Not a member of this server']);
        exit();
    }
    
    // Get channels with server name
    $stmt = $conn->prepare("
        SELECT c.*, s.name as server_name 
        FROM channels c 
        JOIN servers s ON c.server_id = s.id 
        WHERE c.server_id = ? 
        ORDER BY c.name ASC
    ");
    $stmt->execute([$server_id]);
    $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($channels);
    exit();
}

// Create a new channel
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['server_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }
    
    $server_id = filter_var($data['server_id'], FILTER_SANITIZE_NUMBER_INT);
    $name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
    
    // Check if user is an admin or owner of the server
    $stmt = $conn->prepare("
        SELECT role 
        FROM server_members 
        WHERE server_id = ? AND user_id = ?
    ");
    $stmt->execute([$server_id, $_SESSION['user_id']]);
    $role = $stmt->fetchColumn();
    
    if (!in_array($role, ['owner', 'admin'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Not authorized to create channels']);
        exit();
    }
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO channels (server_id, name) 
            VALUES (?, ?)
        ");
        $stmt->execute([$server_id, $name]);
        
        $channel_id = $conn->lastInsertId();
        
        // Get the created channel with server name
        $stmt = $conn->prepare("
            SELECT c.*, s.name as server_name 
            FROM channels c 
            JOIN servers s ON c.server_id = s.id 
            WHERE c.id = ?
        ");
        $stmt->execute([$channel_id]);
        $channel = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($channel);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create channel']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?> 