<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/functions.php';
check_user_login();

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$currentUser = $user ? sanitize($user['username']) : 'Guest';

// Active page detection
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Portal — LuxeStay</title>
    <meta name="description" content="LuxeStay guest portal — find a room, manage bookings, and chat with our team.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">LuxeStay<span>.</span></a>
            <div class="nav-links">
                <a href="index.php"       class="<?php echo $currentPage==='index.php'    ?'active':''; ?>">My Account</a>
                <a href="rooms.php"       class="<?php echo $currentPage==='rooms.php'    ?'active':''; ?>">Find a Room</a>
                <a href="bookings.php"    class="<?php echo $currentPage==='bookings.php' ?'active':''; ?>">My Bookings</a>
                <a href="messages.php"    class="<?php echo $currentPage==='messages.php' ?'active':''; ?>">Chat with Admin</a>
                <a href="../logout.php" style="color: var(--accent);">Logout</a>
            </div>
        </nav>
    </header>
    <main class="container">