<?php include 'includes/header.php'; ?>

<?php
// Fetch active booking count for this user
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM bookings 
    WHERE user_id = ? AND status = 'confirmed' AND check_out > CURRENT_DATE
");
$stmt->execute([$_SESSION['user_id']]);
$activeCount = (int)$stmt->fetchColumn();
?>

<!-- Hero Banner -->
<div class="fade-up" style="
    background: linear-gradient(135deg, #fff4ec 0%, #ffe0c4 100%);
    border: 1px solid rgba(224,123,57,0.2);
    border-radius: 20px;
    padding: 3rem 2.5rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
">
    <!-- Decorative circles -->
    <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(224,123,57,0.08);"></div>
    <div style="position:absolute;bottom:-60px;right:100px;width:160px;height:160px;border-radius:50%;background:rgba(224,123,57,0.05);"></div>

    <div style="position:relative; z-index:1;">
        <p style="color:var(--accent); font-size:0.82rem; font-weight:700; letter-spacing:2.5px; text-transform:uppercase; margin-bottom:10px;">Welcome back</p>
        <h1 style="font-family:var(--font-display); font-size:2.2rem; font-weight:700; color:var(--text); margin-bottom:10px; line-height:1.2;">
            <?php echo $currentUser; ?>
        </h1>
        <p style="color:var(--muted); font-size:1rem; max-width:500px; margin-bottom:1.5rem;">
            Your personal portal to LuxeStay's premium rooms and services. What would you like to do today?
        </p>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="rooms.php" class="btn btn-primary">Browse Rooms</a>
            <a href="bookings.php" class="btn btn-secondary">My Bookings<?php if($activeCount>0): ?> <span style="background:var(--accent);color:#fff;border-radius:20px;padding:1px 8px;font-size:0.75rem;margin-left:4px;"><?php echo $activeCount; ?></span><?php endif; ?></a>
        </div>
    </div>
</div>

<!-- Quick action cards -->
<div class="stats-grid fade-up fade-up-d1" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); margin-bottom: 1.5rem;">

    <a href="rooms.php" style="text-decoration:none;">
        <div class="glass-panel stat-card" style="padding:1.8rem; text-align:left; position:relative; overflow:hidden;">
            <h3 style="font-size:1.1rem; font-weight:700; color:var(--text); margin-bottom:6px; text-transform:none; letter-spacing:0;">Find a Room</h3>
            <p style="font-size:0.88rem; color:var(--muted); text-transform:none; letter-spacing:0; margin-bottom:0;">Explore our premium accommodations and book instantly.</p>
            <div style="position:absolute;bottom:16px;right:18px;color:var(--accent);font-size:1.1rem;">→</div>
        </div>
    </a>

    <a href="bookings.php" style="text-decoration:none;">
        <div class="glass-panel stat-card" style="padding:1.8rem; text-align:left; position:relative; overflow:hidden;">
            <h3 style="font-size:1.1rem; font-weight:700; color:var(--text); margin-bottom:6px; text-transform:none; letter-spacing:0;">My Bookings</h3>
            <p style="font-size:0.88rem; color:var(--muted); text-transform:none; letter-spacing:0; margin-bottom:0;">
                <?php echo $activeCount > 0 ? "You have <strong style='color:var(--accent);'>$activeCount</strong> active reservation(s)." : "View and track all your stays in one place."; ?>
            </p>
            <div style="position:absolute;bottom:16px;right:18px;color:var(--accent);font-size:1.1rem;">→</div>
        </div>
    </a>

    <a href="chat.php" style="text-decoration:none;">
        <div class="glass-panel stat-card" style="padding:1.8rem; text-align:left; position:relative; overflow:hidden;">
            <h3 style="font-size:1.1rem; font-weight:700; color:var(--text); margin-bottom:6px; text-transform:none; letter-spacing:0;">Chat with Admin</h3>
            <p style="font-size:0.88rem; color:var(--muted); text-transform:none; letter-spacing:0; margin-bottom:0;">Have questions? Our team is ready to assist you.</p>
            <div style="position:absolute;bottom:16px;right:18px;color:var(--accent);font-size:1.1rem;">→</div>
        </div>
    </a>

</div>

<!-- Special Offers -->
<div class="glass-panel fade-up fade-up-d2" style="padding: 2rem; background: linear-gradient(135deg, #fff4ec 0%, #ffe8d4 100%); border-color: rgba(224,123,57,0.25);">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <p style="color:var(--accent); font-size:0.78rem; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-bottom:6px;">Limited Time</p>
            <h3 style="font-family:var(--font-display); font-size:1.4rem; color:var(--text); margin-bottom:6px;">Special Offers &amp; Packages</h3>
            <p style="color:var(--muted); font-size:0.9rem;">Exclusive weekend getaway packages available. Contact the front desk for details.</p>
        </div>
        <a href="chat.php" class="btn btn-primary" style="white-space:nowrap; flex-shrink:0;">Ask About Offers</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>