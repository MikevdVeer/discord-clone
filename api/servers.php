<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get user's servers
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("
        SELECT s.*, sm.role 
        FROM servers s 
        JOIN server_members sm ON s.id = sm.server_id 
        WHERE sm.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($servers);
    exit();
}

// Create a new server
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Server name is required']);
        exit();
    }
    
    $name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
    
    try {
        $conn->beginTransaction();
        
        // Create server
        $stmt = $conn->prepare("
            INSERT INTO servers (name, owner_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$name, $_SESSION['user_id']]);
        $server_id = $conn->lastInsertId();
        
        // Add creator as owner
        $stmt = $conn->prepare("
            INSERT INTO server_members (server_id, user_id, role) 
            VALUES (?, ?, 'owner')
        ");
        $stmt->execute([$server_id, $_SESSION['user_id']]);
        
        // Create default channel
        $stmt = $conn->prepare("
            INSERT INTO channels (server_id, name) 
            VALUES (?, 'general')
        ");
        $stmt->execute([$server_id]);
        
        $conn->commit();
        
        // Get the created server
        $stmt = $conn->prepare("
            SELECT s.*, sm.role 
            FROM servers s 
            JOIN server_members sm ON s.id = sm.server_id 
            WHERE s.id = ? AND sm.user_id = ?
        ");
        $stmt->execute([$server_id, $_SESSION['user_id']]);
        $server = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($server);
    } catch (PDOException $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create server']);
    }
    exit();
}

// Join a server
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['server_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Server ID is required']);
        exit();
    }
    
    $server_id = filter_var($data['server_id'], FILTER_SANITIZE_NUMBER_INT);
    
    try {
        // Check if user is already a member
        $stmt = $conn->prepare("
            SELECT 1 FROM server_members 
            WHERE server_id = ? AND user_id = ?
        ");
        $stmt->execute([$server_id, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Already a member of this server']);
            exit();
        }
        
        // Add user as member
        $stmt = $conn->prepare("
            INSERT INTO server_members (server_id, user_id, role) 
            VALUES (?, ?, 'member')
        ");
        $stmt->execute([$server_id, $_SESSION['user_id']]);
        
        // Get the server details
        $stmt = $conn->prepare("
            SELECT s.*, sm.role 
            FROM servers s 
            JOIN server_members sm ON s.id = sm.server_id 
            WHERE s.id = ? AND sm.user_id = ?
        ");
        $stmt->execute([$server_id, $_SESSION['user_id']]);
        $server = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($server);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to join server']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?> 