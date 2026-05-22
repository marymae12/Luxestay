<<<<<<< HEAD
<?php include 'includes/header.php'; ?>

<div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
    <h2>Dashboard</h2>
    <p>Welcome to LuxeStay Management System.</p>
</div>

<div class="stats-grid">
    <div class="glass-panel stat-card">
        <h3><?php echo get_total_rooms(); ?></h3>
        <p>Total Rooms</p>
    </div>
    <div class="glass-panel stat-card">
        <h3><?php echo get_active_bookings_count(); ?></h3>
        <p>Active Bookings</p>
    </div>
    <div class="glass-panel stat-card">
        <h3><?php echo get_completed_bookings_count(); ?></h3>
        <p>Completed Bookings</p>
    </div>
</div>

<div class="glass-panel" style="padding: 2rem;">
    <h3 class="mb-1">Recent Activity</h3>
    <p>System operational. Ready for check-ins.</p>
</div>

=======
<?php include 'includes/header.php'; ?>

<div class="glass-panel" style="padding: 2rem; margin-bottom: 2rem;">
    <h2>Dashboard</h2>
    <p>Welcome to LuxeStay Management System.</p>
</div>

<div class="stats-grid">
    <div class="glass-panel stat-card">
        <h3><?php echo get_total_rooms(); ?></h3>
        <p>Total Rooms</p>
    </div>
    <div class="glass-panel stat-card">
        <h3><?php echo get_active_bookings_count(); ?></h3>
        <p>Active Bookings</p>
    </div>
    <div class="glass-panel stat-card">
        <h3><?php echo get_completed_bookings_count(); ?></h3>
        <p>Completed Bookings</p>
    </div>
</div>

<div class="glass-panel" style="padding: 2rem;">
    <h3 class="mb-1">Recent Activity</h3>
    <p>System operational. Ready for check-ins.</p>
</div>

>>>>>>> 665f4a3a3c4c4205492a17f248bce813b22a689e
<?php include 'includes/footer.php'; ?>