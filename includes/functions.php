<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login()
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: login.html");
        exit;
    }
}

function check_user_login()
{
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
        header("Location: ../login.html");
        exit;
    }
}

function db_connect()
{
    global $pdo;
    return $pdo;
}

function sanitize($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

// Function to get total rooms
function get_total_rooms()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM rooms");
    return $stmt->fetchColumn();
}

// Function to get active bookings (checked_in or confirmed)
function get_active_bookings_count()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed', 'checked_in') AND CURRENT_DATE < check_out");
    return $stmt->fetchColumn();
}

// Function to get completed bookings
function get_completed_bookings_count()
{
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE CURRENT_DATE >= check_out");
    return $stmt->fetchColumn();
}

// Function to get rooms by status
function get_rooms($status = null)
{
    global $pdo;

    // Select rooms
    if ($status) {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE status = ?");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT * FROM rooms");
    }
    $rooms = $stmt->fetchAll();

    // Check for active occupancy for each room
    foreach ($rooms as &$room) {
        // Only check if status is 'available' (don't override maintenance)
        if ($room['status'] === 'available') {
            // Check for current bookings holding the room (pending, confirmed, checked_in)
            $stmt = $pdo->prepare("
                SELECT status FROM bookings 
                WHERE room_id = ? 
                AND status IN ('confirmed', 'checked_in', 'Pending', 'pending') 
                AND CURRENT_DATE >= check_in 
                AND CURRENT_DATE < check_out
                ORDER BY FIELD(LOWER(status), 'checked_in', 'confirmed', 'pending') LIMIT 1
            ");
            $stmt->execute([$room['id']]);
            $bookingStatus = $stmt->fetchColumn();

            if ($bookingStatus) {
                if (strtolower($bookingStatus) === 'pending') {
                    $room['status'] = 'pending';
                } else {
                    $room['status'] = 'occupied';
                }
            }
        }
    }

    return $rooms;
}
?>