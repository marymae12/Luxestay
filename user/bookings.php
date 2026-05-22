<?php
include 'includes/header.php';

// Fetch this user's bookings
$stmt = $pdo->prepare("
    SELECT b.id, b.check_in, b.check_out, b.total_price, b.status, b.created_at,
           r.room_number, r.type AS room_type,
           g.first_name, g.last_name
    FROM bookings b
    JOIN rooms r   ON b.room_id  = r.id
    JOIN guests g  ON b.guest_id = g.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$myBookings = $stmt->fetchAll();
?>

<div class="glass-panel mb-2" style="padding: 2rem; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2 style="margin:0;">My Bookings</h2>
        <p style="margin: 5px 0 0 0; color: #666;">View your booking history and current reservations.</p>
    </div>
    <a href="rooms.php" class="btn btn-primary" style="white-space:nowrap;">+ New Booking</a>
</div>

<?php if (empty($myBookings)): ?>
<div class="glass-panel" style="padding: 2rem;">
    <div style="text-align: center; padding: 40px 20px;">
        <h1 style="font-size: 3rem; margin-bottom: 20px;">🛎️</h1>
        <h3>No Bookings Found</h3>
        <p style="color: #666; max-width: 500px; margin: 0 auto 20px auto;">
            You currently have no active or past bookings associated with this account.
            Once you make a reservation, your booking details will appear here.
        </p>
        <a href="rooms.php" class="btn btn-primary">Browse Rooms</a>
    </div>
</div>

<?php else: ?>
<div style="display:flex; flex-direction:column; gap:14px;">
    <?php
    $today = date('Y-m-d');
    foreach ($myBookings as $bk):
        // Determine human status
        if ($bk['status'] === 'cancelled') {
            $label = 'Cancelled'; $badgeColor = '#dc3545'; $badgeBg = '#fde8ea';
        } elseif ($today >= $bk['check_in'] && $today < $bk['check_out']) {
            $label = 'Active Stay'; $badgeColor = '#e67e22'; $badgeBg = '#fff3e0';
        } elseif ($today >= $bk['check_out']) {
            $label = 'Completed'; $badgeColor = '#6c757d'; $badgeBg = '#f0f0f0';
        } else {
            $label = 'Confirmed'; $badgeColor = '#28a745'; $badgeBg = '#e8f5e9';
        }

        $nights = max(1, (int)(new DateTime($bk['check_in']))->diff(new DateTime($bk['check_out']))->days);
    ?>
    <div class="glass-panel" style="padding:0; overflow:hidden;">
        <div style="display:flex; align-items:stretch;">
            <!-- Accent bar -->
            <div style="width:6px; background:<?php echo $badgeColor; ?>; flex-shrink:0;"></div>

            <div style="flex:1; padding:20px 24px;">
                <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; gap:10px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                            <span style="font-size:1rem; font-weight:700;">Room <?php echo sanitize($bk['room_number']); ?></span>
                            <span style="font-size:0.75rem; text-transform:capitalize; color:<?php echo $badgeColor; ?>; background:<?php echo $badgeBg; ?>; padding:3px 10px; border-radius:20px; font-weight:600; border:1px solid <?php echo $badgeColor; ?>;">
                                <?php echo $label; ?>
                            </span>
                        </div>
                        <div style="color:#555; font-size:0.88rem;">
                            Type: <strong><?php echo sanitize($bk['room_type']); ?></strong>
                            &nbsp;·&nbsp;
                            Guest: <?php echo sanitize($bk['first_name'] . ' ' . $bk['last_name']); ?>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:1.3rem; font-weight:700; color:var(--accent-color);">$<?php echo number_format($bk['total_price'], 2); ?></div>
                        <div style="font-size:0.8rem; color:#888;"><?php echo $nights; ?> night(s)</div>
                    </div>
                </div>

                <hr style="border:none; border-top:1px solid #eee; margin:14px 0;">

                <div style="display:flex; flex-wrap:wrap; gap:20px; font-size:0.88rem; color:#555;">
                    <div>
                        <div style="font-size:0.75rem; color:#999; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Check In</div>
                        <div style="font-weight:600; color:#333;"><?php echo date('M j, Y', strtotime($bk['check_in'])); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; color:#999; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Check Out</div>
                        <div style="font-weight:600; color:#333;"><?php echo date('M j, Y', strtotime($bk['check_out'])); ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; color:#999; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Booking #</div>
                        <div style="font-weight:600; color:#333;">#<?php echo $bk['id']; ?></div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem; color:#999; text-transform:uppercase; letter-spacing:.5px; margin-bottom:2px;">Booked On</div>
                        <div style="font-weight:600; color:#333;"><?php echo date('M j, Y', strtotime($bk['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>