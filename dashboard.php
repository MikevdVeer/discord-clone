<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get user's servers
$stmt = $conn->prepare("
    SELECT s.*, sm.role 
    FROM servers s 
    JOIN server_members sm ON s.id = sm.server_id 
    WHERE sm.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$servers = $stmt->fetchAll();

// Get default server's channels if any
$channels = [];
if (!empty($servers)) {
    $stmt = $conn->prepare("SELECT * FROM channels WHERE server_id = ?");
    $stmt->execute([$servers[0]['id']]);
    $channels = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Clone - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
</head>
<body class="dashboard" data-user-id="<?php echo $_SESSION['user_id']; ?>">
    <div class="server-list">
        <div class="server-item active">
            <a href="#" class="server-icon">+</a>
        </div>
        <?php foreach ($servers as $server): ?>
        <div class="server-item">
            <a href="#" class="server-icon" data-server-id="<?php echo $server['id']; ?>">
                <?php echo substr($server['name'], 0, 1); ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="channel-list">
        <div class="server-header">
            <h2><?php echo !empty($servers) ? $servers[0]['name'] : 'No Server Selected'; ?></h2>
        </div>
        <?php foreach ($channels as $channel): ?>
        <div class="channel-item">
            <a href="#" class="channel-link" data-channel-id="<?php echo $channel['id']; ?>">
                # <?php echo htmlspecialchars($channel['name']); ?>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="chat-container">
        <div class="chat-header">
            <h3># <?php echo !empty($channels) ? $channels[0]['name'] : 'general'; ?></h3>
        </div>
        <div class="messages" id="messages">
            <!-- Messages will be loaded here -->
        </div>
        <div class="message-input">
            <form id="message-form">
                <input type="text" id="message" placeholder="Type a message...">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <div class="members-list">
        <div class="members-header">
            <h3>Members</h3>
        </div>
        <div class="members-section">
            <h4>Online - <span id="online-count">0</span></h4>
            <div id="online-members">
                <!-- Online members will be loaded here -->
            </div>
        </div>
        <div class="members-section">
            <h4>Offline - <span id="offline-count">0</span></h4>
            <div id="offline-members">
                <!-- Offline members will be loaded here -->
            </div>
        </div>
    </div>

    <script src="assets/js/chat.js"></script>
</body>
</html> 