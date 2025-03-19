<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

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
    
    // Get all members of the server with their online status
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.avatar, sm.role, 
               CASE WHEN u.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END as is_online
        FROM server_members sm
        JOIN users u ON sm.user_id = u.id
        WHERE sm.server_id = ?
        ORDER BY is_online DESC, u.username ASC
    ");
    $stmt->execute([$server_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($members);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?> 