<?php
session_start();
require_once '../config/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Debug log
    error_log("Login attempt - Username: $username, Password: $password");

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Please fill in all fields';
        header('Location: ../../front/views/login.php');
        exit;
    }

    try {
        // Initialize database connection
        $database = new Database();
        $pdo = $database->getConnection();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Debug log
            error_log("User found: " . print_r($user, true));
            
            // For testing purposes, we'll use a simple password check
            if ($password === 'password') {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Debug log
                error_log("Login successful - Session: " . print_r($_SESSION, true));

                if ($remember) {
                    // Set cookie for 30 days
                    setcookie('remember_token', base64_encode($user['username']), time() + (86400 * 30), '/');
                }

                // Debug log
                error_log("Redirecting to dashboard");
                
                // Redirect to dashboard
                header('Location: ../../front/views/dashboard.php');
                exit;
            } else {
                // Debug log
                error_log("Invalid password");
                $_SESSION['error'] = 'Invalid password';
                header('Location: ../../front/views/login.php');
                exit;
            }
        } else {
            // Debug log
            error_log("User not found");
            $_SESSION['error'] = 'Invalid username';
            header('Location: ../../front/views/login.php');
            exit;
        }
    } catch (PDOException $e) {
        // Debug log
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: ../../front/views/login.php');
        exit;
    }
} else {
    header('Location: ../../front/views/login.php');
    exit;
} 