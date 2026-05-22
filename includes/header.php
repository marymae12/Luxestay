<?php
require_once __DIR__ . '/functions.php';
check_login();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuxeStay Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <header class="container">
        <nav class="navbar glass-panel">
            <a href="index.php" class="logo">LuxeStay.</a>
            <div class="nav-links">
                <a href="index.php">Dashboard</a>
                <a href="rooms.php">Rooms</a>
                <a href="bookings.php">Bookings</a>
                <a href="messages.php">Messages</a>
                <a href="logout.php" style="color: var(--accent-color);">Logout</a>
            </div>
        </nav>
    </header>
    <main class="container">