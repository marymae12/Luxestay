<?php
require_once 'config.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        redirect('register.html?error=' . urlencode("All fields are required."));
    }

    if ($password !== $confirm_password) {
        redirect('register.html?error=' . urlencode("Passwords do not match."));
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        redirect('register.html?error=' . urlencode("Username already exists."));
    }

    // Insert new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");

    if ($stmt->execute([$username, $hashed_password])) {
        redirect('register.html?success=' . urlencode("Account created successfully. You can now log in."));
    } else {
        redirect('register.html?error=' . urlencode("Registration failed. Please try again."));
    }
} else {
    redirect('register.html');
}
?>