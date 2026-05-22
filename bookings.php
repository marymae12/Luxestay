<?php
require_once 'includes/functions.php';

$success_msg = '';
$error_msg = '';

// Handle New Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_room'])) {
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $room_id = sanitize($_POST['room_id']);
    $check_in = sanitize($_POST['check_in']);
    $check_out = sanitize($_POST['check_out']);

    if ($first_name && $email && $room_id && $check_in && $check_out) {
        try {
            $pdo->beginTransaction();

            // 1. Check or Create Guest
            $stmt = $pdo->prepare("SELECT id FROM guests WHERE email = ?");
            $stmt->execute([$email]);
            $guest = $stmt->fetch();

            if ($guest) {
                $guest_id = $guest['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO guests (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$first_name, $last_name, $email, $phone]);
                $guest_id = $pdo->lastInsertId();
            }

            // 2. Calculate Total Price
            // Get room price
            $stmt = $pdo->prepare("SELECT price FROM rooms WHERE id = ?");
            $stmt->execute([$room_id]);
            $room_price = $stmt->fetchColumn();

            $idx = new DateTime($check_in);
            $outx = new DateTime($check_out);
            $interval = $idx->diff($outx);
            $days = $interval->days;
            if ($days < 1)
                $days = 1;

            $total_price = $room_price * $days;

            // 2.5 Check for Overlapping Bookings
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

            // 3. Create Booking
            $stmt = $pdo->prepare("INSERT INTO bookings (room_id, guest_id, check_in, check_out, total_price, status) VALUES (?, ?, ?, ?, ?, 'confirmed')");
            $stmt->execute([$room_id, $guest_id, $check_in, $check_out, $total_price]);

            // 4. Update Room Status (Optional - keeping simple)
            // We are now computing status dynamically in get_rooms(), so no need to update the table column persistently unless needed.

            $pdo->commit();
            $success_msg = "Booking created successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_msg = "Error creating booking: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

// Fetch Bookings with details
$stmt = $pdo->query("
    SELECT b.*, r.room_number, r.type, g.first_name, g.last_name 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    JOIN guests g ON b.guest_id = g.id 
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();

// Fetch Available Rooms for stats
$available_rooms = get_rooms();
?>

<?php include 'includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Booking Management</h2>
</div>

<?php if ($success_msg): ?>
    <div class="glass-panel"
        style="padding: 1rem; margin-bottom: 1rem; background: rgba(39, 174, 96, 0.2); border-color: #27ae60; color: #1e8449;">
        <?php echo $success_msg; ?>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="glass-panel"
        style="padding: 1rem; margin-bottom: 1rem; background: rgba(231, 76, 60, 0.2); border-color: #e74c3c; color: #c0392b;">
        <?php echo $error_msg; ?>
    </div>
<?php endif; ?>

<div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
    <h3 class="mb-1">New Booking</h3>
    <form method="POST" action="">
        <h4 style="margin-bottom: 15px; color: var(--accent-color);">1. Guest Details</h4>
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>
        </div>

        <h4 style="margin-bottom: 15px; color: var(--accent-color);">2. Room & Dates</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="form-group">
                <label>Room *</label>
                <select name="room_id" required>
                    <option value="">Select a Room</option>
                    <?php foreach ($available_rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>">
                            <?php echo $room['room_number'] . ' (' . $room['type'] . ') - $' . $room['price']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Check In *</label>
                <input type="date" name="check_in" required>
            </div>
            <div class="form-group">
                <label>Check Out *</label>
                <input type="date" name="check_out" required>
            </div>
        </div>

        <button type="submit" name="book_room" class="btn btn-primary" style="margin-top: 15px;">Confirm
            Booking</button>
    </form>
</div>

<div class="glass-panel" style="padding: 2rem;">
    <h3 class="mb-1">Recent Bookings</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                            <td><strong><?php echo htmlspecialchars($booking['room_number']); ?></strong></td>
                            <td><?php echo $booking['check_in']; ?></td>
                            <td><?php echo $booking['check_out']; ?></td>
                            <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                            <td>
                                <?php
                                $today = date('Y-m-d');
                                $status_label = ucfirst($booking['status']);
                                $status_color = 'var(--primary-color)';

                                if ($booking['status'] !== 'cancelled') {
                                    if ($today >= $booking['check_in'] && $today < $booking['check_out']) {
                                        $status_label = 'Pending'; // In Room
                                        $status_color = '#e67e22'; // Orange
                                    } elseif ($today >= $booking['check_out']) {
                                        $status_label = 'Completed';
                                        $status_color = '#7f8c8d'; // Gray
                                    } else {
                                        $status_label = 'Confirmed';
                                        $status_color = '#27ae60'; // Green
                                    }
                                } else {
                                    $status_color = '#c0392b'; // Red
                                }
                                ?>
                                <span style="font-size: 0.85rem; font-weight: bold; color: <?php echo $status_color; ?>;">
                                    <?php echo $status_label; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No bookings yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>