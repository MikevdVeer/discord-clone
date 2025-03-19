<?php
require_once 'config/database.php';

// Check if sockets extension is loaded
if (!extension_loaded('sockets')) {
    die("The sockets extension is not loaded. Please enable it in php.ini\n");
}

// Initialize database connection
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection established\n";
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

class WebSocketServer {
    private $server;
    private $clients = [];
    private $channels = [];
    private $conn;

    public function __construct($host = '0.0.0.0', $port = 8081) {
        global $conn;
        $this->conn = $conn;
        
        echo "Initializing WebSocket server...\n";
        
        $this->server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->server === false) {
            die("Failed to create socket: " . socket_strerror(socket_last_error()));
        }

        if (!socket_set_option($this->server, SOL_SOCKET, SO_REUSEADDR, 1)) {
            die("Failed to set socket options: " . socket_strerror(socket_last_error()));
        }

        if (!socket_bind($this->server, $host, $port)) {
            die("Failed to bind socket: " . socket_strerror(socket_last_error()));
        }

        if (!socket_listen($this->server)) {
            die("Failed to listen on socket: " . socket_strerror(socket_last_error()));
        }

        echo "WebSocket server started on ws://$host:$port\n";
    }

    public function run() {
        while (true) {
            $read = array_merge([$this->server], $this->clients);
            $write = $except = null;
            
            if (socket_select($read, $write, $except, null) < 1) {
                continue;
            }

            // New connection
            if (in_array($this->server, $read)) {
                $client = socket_accept($this->server);
                if ($client === false) {
                    echo "Failed to accept connection: " . socket_strerror(socket_last_error()) . "\n";
                    continue;
                }
                $this->clients[] = $client;
                $this->handleNewConnection($client);
            }

            // Handle client messages
            foreach ($this->clients as $key => $client) {
                if (in_array($client, $read)) {
                    $data = $this->receive($client);
                    if ($data === false) {
                        $this->handleDisconnection($client);
                        continue;
                    }

                    try {
                        $message = json_decode($data, true);
                        if ($message) {
                            echo "Received message: " . $data . "\n";
                            $this->handleMessage($client, $message);
                        }
                    } catch (Exception $e) {
                        echo "Error processing message: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }

    private function handleNewConnection($client) {
        $headers = $this->receive($client);
        if ($this->performHandshake($client, $headers)) {
            echo "New client connected\n";
        } else {
            echo "Failed to perform handshake\n";
            $this->handleDisconnection($client);
        }
    }

    private function performHandshake($client, $headers) {
        $headers = explode("\r\n", $headers);
        $key = '';
        
        foreach ($headers as $header) {
            if (strpos($header, 'Sec-WebSocket-Key:') === 0) {
                $key = trim(substr($header, 19));
                break;
            }
        }

        if (empty($key)) {
            return false;
        }

        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        
        $response = "HTTP/1.1 101 Switching Protocols\r\n";
        $response .= "Upgrade: websocket\r\n";
        $response .= "Connection: Upgrade\r\n";
        $response .= "Sec-WebSocket-Accept: $acceptKey\r\n";
        $response .= "Sec-WebSocket-Version: 13\r\n\r\n";
        
        if (!socket_write($client, $response, strlen($response))) {
            echo "Failed to send handshake response: " . socket_strerror(socket_last_error()) . "\n";
            return false;
        }
        
        return true;
    }

    private function handleMessage($client, $message) {
        if (!isset($message['type'])) {
            echo "Message type not set\n";
            return;
        }

        echo "Handling message type: " . $message['type'] . "\n";

        switch ($message['type']) {
            case 'join':
                $this->handleJoinChannel($client, $message['channel_id']);
                break;
            case 'leave':
                $this->handleLeaveChannel($client, $message['channel_id']);
                break;
            case 'message':
                $this->handleChatMessage($client, $message);
                break;
            default:
                echo "Unknown message type: " . $message['type'] . "\n";
        }
    }

    private function handleJoinChannel($client, $channelId) {
        if (!isset($this->channels[$channelId])) {
            $this->channels[$channelId] = [];
        }
        $this->channels[$channelId][] = $client;
        echo "Client joined channel: $channelId\n";
    }

    private function handleLeaveChannel($client, $channelId) {
        if (isset($this->channels[$channelId])) {
            $this->channels[$channelId] = array_diff($this->channels[$channelId], [$client]);
            echo "Client left channel: $channelId\n";
        }
    }

    private function handleChatMessage($client, $message) {
        if (!isset($message['channel_id']) || !isset($message['content']) || !isset($message['user_id'])) {
            echo "Invalid message format\n";
            return;
        }

        $channelId = $message['channel_id'];
        if (!isset($this->channels[$channelId])) {
            echo "Channel not found: $channelId\n";
            return;
        }

        // Save message to database
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO messages (channel_id, user_id, content) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $channelId,
                $message['user_id'],
                $message['content']
            ]);

            $messageId = $this->conn->lastInsertId();

            // Get complete message data
            $stmt = $this->conn->prepare("
                SELECT m.*, u.username 
                FROM messages m 
                JOIN users u ON m.user_id = u.id 
                WHERE m.id = ?
            ");
            $stmt->execute([$messageId]);
            $messageData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$messageData) {
                echo "Failed to fetch message data\n";
                return;
            }

            // Broadcast to all clients in the channel
            $this->broadcast($channelId, json_encode([
                'type' => 'new-message',
                'message' => $messageData
            ]));
            
            echo "Message broadcasted to channel: $channelId\n";
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage() . "\n";
        }
    }

    private function broadcast($channelId, $message) {
        if (!isset($this->channels[$channelId])) {
            return;
        }

        foreach ($this->channels[$channelId] as $client) {
            $this->send($client, $message);
        }
    }

    private function send($client, $message) {
        $frame = chr(0x81) . chr(strlen($message)) . $message;
        if (!socket_write($client, $frame, strlen($frame))) {
            echo "Failed to send message: " . socket_strerror(socket_last_error()) . "\n";
        }
    }

    private function receive($client) {
        $data = socket_read($client, 1024, PHP_BINARY_READ);
        if ($data === false) {
            echo "Failed to read from socket: " . socket_strerror(socket_last_error()) . "\n";
            return false;
        }

        // If this is the initial handshake
        if (strpos($data, 'GET / HTTP/1.1') !== false) {
            return $data;
        }

        // Handle WebSocket frames
        $opcode = ord($data[0]) & 0x0F;
        $length = ord($data[1]) & 0x7F;
        $mask = (ord($data[1]) & 0x80) !== 0;
        
        $payload = substr($data, 2);
        if ($mask) {
            $maskingKey = substr($payload, 0, 4);
            $payload = substr($payload, 4);
            $unmasked = '';
            for ($i = 0; $i < strlen($payload); $i++) {
                $unmasked .= $payload[$i] ^ $maskingKey[$i % 4];
            }
            return $unmasked;
        }
        
        return $payload;
    }

    private function handleDisconnection($client) {
        $key = array_search($client, $this->clients);
        if ($key !== false) {
            unset($this->clients[$key]);
        }

        // Remove client from all channels
        foreach ($this->channels as $channelId => $clients) {
            $this->channels[$channelId] = array_diff($clients, [$client]);
        }

        socket_close($client);
        echo "Client disconnected\n";
    }
}

// Start the WebSocket server
try {
    $server = new WebSocketServer();
    $server->run();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 