<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "Please fill in all fields";
        header('Location: ../register.php');
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header('Location: ../register.php');
        exit();
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username or email already exists";
        header('Location: ../register.php');
        exit();
    }

    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$username, $email, $hashed_password]);
        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: ../index.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header('Location: ../register.php');
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}
?> 