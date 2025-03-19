-- Insert sample users
INSERT INTO users (username, email, password, avatar) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'), -- password: password
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'), -- password: password
('Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'), -- password: password
('Sarah Wilson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png'); -- password: password

-- Insert sample servers
INSERT INTO servers (name, description, owner_id) VALUES
('Gaming Community', 'A place for gamers to hang out', 1),
('Study Group', 'Study and homework help', 2),
('Tech Enthusiasts', 'Discussion about technology', 3),
('Art Gallery', 'Share and discuss artwork', 4);

-- Insert server members
INSERT INTO server_members (server_id, user_id, role) VALUES
-- Gaming Community members
(1, 1, 'owner'),
(1, 2, 'member'),
(1, 3, 'member'),
(1, 4, 'member'),

-- Study Group members
(2, 2, 'owner'),
(2, 1, 'member'),
(2, 3, 'member'),

-- Tech Enthusiasts members
(3, 3, 'owner'),
(3, 1, 'member'),
(3, 4, 'member'),

-- Art Gallery members
(4, 4, 'owner'),
(4, 2, 'member'),
(4, 3, 'member');

-- Insert default channels for each server
INSERT INTO channels (server_id, name, type) VALUES
-- Gaming Community channels
(1, 'general', 'text'),
(1, 'gaming', 'text'),
(1, 'off-topic', 'text'),
(1, 'voice-chat', 'voice'),

-- Study Group channels
(2, 'general', 'text'),
(2, 'math', 'text'),
(2, 'science', 'text'),
(2, 'study-room', 'voice'),

-- Tech Enthusiasts channels
(3, 'general', 'text'),
(3, 'programming', 'text'),
(3, 'hardware', 'text'),
(3, 'tech-talk', 'voice'),

-- Art Gallery channels
(4, 'general', 'text'),
(4, 'artwork', 'text'),
(4, 'critique', 'text'),
(4, 'voice-chat', 'voice'); 