<?php
require_once '../config.php';
require_once '../includes/functions.php';
check_user_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$first_name  = trim($data['first_name']  ?? '');
$last_name   = trim($data['last_name']   ?? '');
$email       = trim($data['email']       ?? '');
$phone       = trim($data['phone']       ?? '');
$room_id     = (int)($data['room_id']    ?? 0);
$check_in    = trim($data['check_in']    ?? '');
$check_out   = trim($data['check_out']   ?? '');
$user_id     = (int)$_SESSION['user_id'];

// Basic validation
if (!$first_name || !$email || !$room_id || !$check_in || !$check_out) {
    echo json_encode(['error' => 'Please fill in all required fields.']);
    exit;
}

if ($check_in >= $check_out) {
    echo json_encode(['error' => 'Check-out date must be after check-in date.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Check or create guest
    $stmt = $pdo->prepare("SELECT id FROM guests WHERE email = ?");
    $stmt->execute([$email]);
    $guest = $stmt->fetch();

    if ($guest) {
        $guest_id = $guest['id'];
        // Update name/phone in case they changed
        $pdo->prepare("UPDATE guests SET first_name=?, last_name=?, phone=? WHERE id=?")
            ->execute([$first_name, $last_name, $phone, $guest_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO guests (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)");
        $ins->execute([$first_name, $last_name, $email, $phone]);
        $guest_id = $pdo->lastInsertId();
    }

    // 2. Get room price
    $stmt = $pdo->prepare("SELECT price, status FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        throw new Exception("Room not found.");
    }

    // 3. Check for overlapping bookings
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings
        WHERE room_id = ?
        AND status IN ('confirmed', 'checked_in')
        AND (
            (check_in <= ? AND check_out > ?) OR
            (check_in < ? AND check_out >= ?) OR
            (check_in >= ? AND check_out <= ?)
        )
    ");
    $stmt->execute([$room_id, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Room is already booked for the selected dates.");
    }

    // 4. Calculate total
    $idx  = new DateTime($check_in);
    $outx = new DateTime($check_out);
    $days = max(1, (int)$idx->diff($outx)->days);
    $total_price = $room['price'] * $days;

    // 5. Insert booking with user_id
    $pdo->prepare("
        INSERT INTO bookings (room_id, guest_id, user_id, check_in, check_out, total_price, status)
        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
    ")->execute([$room_id, $guest_id, $user_id, $check_in, $check_out, $total_price]);

    $booking_id = $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'success'    => true,
        'booking_id' => $booking_id,
        'total'      => number_format($total_price, 2),
        'nights'     => $days,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
