<?php
require_once 'includes/functions.php';

$success_msg = '';
$error_msg = '';
$edit_mode = false;
$room_to_edit = null;

// Handle Form Submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room']) || isset($_POST['update_room'])) {
        $room_number = sanitize($_POST['room_number']);
        $type = sanitize($_POST['type']);
        $price = sanitize($_POST['price']);
        $description = sanitize($_POST['description']);
        $status = 'available'; // Default status logic remains

        if ($room_number && $type && $price) {
            try {
                if (isset($_POST['update_room']) && isset($_POST['room_id'])) {
                    // Update
                    $id = sanitize($_POST['room_id']);
                    $stmt = $pdo->prepare("UPDATE rooms SET room_number = ?, type = ?, price = ?, description = ? WHERE id = ?");
                    $stmt->execute([$room_number, $type, $price, $description, $id]);
                    $success_msg = "Room updated successfully!";
                } else {
                    // Add
                    $stmt = $pdo->prepare("INSERT INTO rooms (room_number, type, price, description, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$room_number, $type, $price, $description, $status]);
                    $success_msg = "Room added successfully!";
                }
            } catch (PDOException $e) {
                $error_msg = "Error processing room: " . $e->getMessage();
            }
        } else {
            $error_msg = "Please fill in all required fields.";
        }
    }
}

// Handle Edit Fetch
if (isset($_GET['edit'])) {
    $id = sanitize($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$id]);
    $room_to_edit = $stmt->fetch();
    if ($room_to_edit) {
        $edit_mode = true;
    }
}

$rooms = get_rooms();
?>

<?php include 'includes/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h2>Room Management</h2>
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
    <h3 class="mb-1"><?php echo $edit_mode ? 'Edit Room' : 'Add New Room'; ?></h3>
    <form method="POST" action="rooms.php">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="room_id" value="<?php echo $room_to_edit['id']; ?>">
            <input type="hidden" name="update_room" value="1">
        <?php else: ?>
            <input type="hidden" name="add_room" value="1">
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div class="form-group">
                <label>Room Number *</label>
                <input type="text" name="room_number" required placeholder="e.g. 101"
                    value="<?php echo $edit_mode ? htmlspecialchars($room_to_edit['room_number']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Type *</label>
                <select name="type" required>
                    <option value="Single" <?php echo ($edit_mode && $room_to_edit['type'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                    <option value="Double" <?php echo ($edit_mode && $room_to_edit['type'] === 'Double') ? 'selected' : ''; ?>>Double</option>
                    <option value="Suite" <?php echo ($edit_mode && $room_to_edit['type'] === 'Suite') ? 'selected' : ''; ?>>Suite</option>
                    <option value="Deluxe" <?php echo ($edit_mode && $room_to_edit['type'] === 'Deluxe') ? 'selected' : ''; ?>>Deluxe</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price per Night ($) *</label>
                <input type="number" step="0.01" name="price" required placeholder="0.00"
                    value="<?php echo $edit_mode ? $room_to_edit['price'] : ''; ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="2"
                placeholder="Room details..."><?php echo $edit_mode ? htmlspecialchars($room_to_edit['description']) : ''; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update Room' : 'Add Room'; ?></button>
        <?php if ($edit_mode): ?>
            <a href="rooms.php" class="btn btn-secondary"
                style="margin-left: 10px; padding: 10px 20px; text-decoration: none;">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="glass-panel" style="padding: 2rem;">
    <h3 class="mb-1">Room List</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th style="width: 200px;">Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rooms) > 0): ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo $room['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($room['type']); ?></td>
                            <td>$<?php echo number_format($room['price'], 2); ?></td>
                            <td class="truncate" title="<?php echo htmlspecialchars($room['description']); ?>"
                                style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: help;">
                                <?php echo htmlspecialchars($room['description']); ?>
                            </td>
                            <td>
                                <span style="
                                padding: 5px 10px; 
                                border-radius: 20px; 
                                font-size: 0.85rem; 
                                background: <?php echo $room['status'] === 'available' ? 'rgba(39, 174, 96, 0.2)' : ($room['status'] === 'occupied' ? 'rgba(230, 126, 34, 0.2)' : 'rgba(231, 76, 60, 0.2)'); ?>;
                                color: <?php echo $room['status'] === 'available' ? '#27ae60' : ($room['status'] === 'occupied' ? '#e67e22' : '#c0392b'); ?>;
                            ">
                                    <?php echo ucfirst($room['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="rooms.php?edit=<?php echo $room['id']; ?>" class="btn"
                                    style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No rooms found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>