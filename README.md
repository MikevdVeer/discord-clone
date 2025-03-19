# Discord Clone

A real-time chat application built with PHP, MySQL, and WebSocket technology, inspired by Discord.

## Features

- User authentication (register/login)
- Server creation and management
- Text channels
- Real-time messaging
- Online user status
- Modern Discord-like UI

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Pusher account for real-time functionality

## Setup

1. Clone the repository:
```bash
git clone https://github.com/yourusername/discord-clone.git
cd discord-clone
```

2. Install dependencies:
```bash
composer install
```

3. Create a MySQL database and import the schema:
```bash
mysql -u root -p < database.sql
```

4. Configure your database connection in `config/database.php`

5. Sign up for a Pusher account and get your credentials

6. Update Pusher credentials in:
   - `assets/js/chat.js`
   - `api/messages.php`

7. Configure your web server (Apache/Nginx) to point to the project directory

## Usage

1. Start your web server
2. Visit the application in your browser
3. Register a new account
4. Create or join servers
5. Start chatting!

## Security

- All passwords are hashed using PHP's password_hash()
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars()
- CSRF protection implemented
- Secure session handling

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 