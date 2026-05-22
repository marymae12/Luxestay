<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

if ($action === 'fetch') {
    $other_id = isset($_GET['other_id']) ? (int) $_GET['other_id'] : null;

    if (!$other_id) {
        // If no other_id specified and we are a user, we fetch messages with the admin
        if ($_SESSION['role'] === 'user') {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();
            if ($admin) {
                $other_id = $admin['id'];
            }
        }
    }

    if ($other_id) {
        $stmt = $pdo->prepare("
            SELECT m.*, u.username as sender_name 
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$user_id, $other_id, $other_id, $user_id]);
        $messages = $stmt->fetchAll();

        // If this is the user fetching their chat with the admin, and there are no messages, create a welcome message
        if (empty($messages) && $_SESSION['role'] === 'user' && $other_id) {
            $welcomeMsg = "Hello! Welcome to LuxeStay. How can we help you today?";
            $insertStmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $insertStmt->execute([$other_id, $user_id, $welcomeMsg]);

            // Re-fetch the message to return it immediately
            $stmt->execute([$user_id, $other_id, $other_id, $user_id]);
            $messages = $stmt->fetchAll();
        }

        echo json_encode(['messages' => $messages, 'other_id' => $other_id]);
    } else {
        echo json_encode(['error' => 'Other party not found.']);
    }

} elseif ($action === 'send') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message = trim($data['message'] ?? '');
    $receiver_id = isset($data['receiver_id']) ? (int) $data['receiver_id'] : null;

    if (!$receiver_id && $_SESSION['role'] === 'user') {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();
        if ($admin) {
            $receiver_id = $admin['id'];
        }
    }

    if ($message && $receiver_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $receiver_id, $message])) {
            
            // Add automated response if it's from a user
            if ($_SESSION['role'] === 'user') {
                $autoReply = "";
                $lowerMsg = strtolower($message);
                
                if (strpos($lowerMsg, 'how to book') !== false) {
                    $autoReply = "To book a room, please navigate to the 'Rooms' tab, select your preferred room, and click the 'Book Now' button. Follow the on-screen instructions to confirm your booking.";
                } elseif (strpos($lowerMsg, 'available rooms') !== false) {
                    $autoReply = "Yes! You can view all currently available rooms on our 'Rooms' page. Availability is updated in real-time.";
                } elseif (strpos($lowerMsg, 'payment methods') !== false) {
                    $autoReply = "We currently accept major Credit/Debit cards, PayPal, and secure Bank Transfers. You can select your preferred payment method during checkout.";
                }

                if ($autoReply !== "") {
                    // Slight artificial delay before inserting the auto-reply to simulate bot typing
                    usleep(500000); // 0.5 seconds
                    $replyStmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
                    $replyStmt->execute([$receiver_id, $user_id, $autoReply]);
                }
            }

            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to send message']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid data']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
}
?>