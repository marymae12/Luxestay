<?php include 'includes/header.php'; ?>

<?php
// Pre-fetch user info to pre-fill the booking form
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$sessionUser = $stmt->fetch();
?>

<!-- Booking Modal -->
<div id="booking-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:9999; overflow-y:auto;">
    <div style="background:#fff; max-width:620px; margin:40px auto; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg,#1a1a2e,#16213e); padding:24px 28px; color:white; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0; font-size:1.3rem;">Book a Room</h3>
                <p id="modal-room-label" style="margin:4px 0 0 0; opacity:0.75; font-size:0.9rem;"></p>
            </div>
            <button onclick="closeBookingModal()" style="background:none;border:none;color:white;font-size:1.6rem;cursor:pointer;line-height:1;">&times;</button>
        </div>

        <!-- Success / Error banners -->
        <div id="booking-success" style="display:none; background:#d4edda; color:#155724; padding:14px 24px; font-weight:500; border-left:4px solid #28a745;">
            ✅ <span id="booking-success-msg"></span>
        </div>
        <div id="booking-error" style="display:none; background:#f8d7da; color:#721c24; padding:14px 24px; font-weight:500; border-left:4px solid #dc3545;">
            ❌ <span id="booking-error-msg"></span>
        </div>

        <div style="padding:28px;">
            <form id="booking-form" onsubmit="submitBooking(event)">
                <input type="hidden" id="booking-room-id">

                <!-- Section 1: Guest Details -->
                <div style="margin-bottom:22px;">
                    <h4 style="margin:0 0 14px 0; color:#e67e22; font-size:0.95rem; letter-spacing:.5px; text-transform:uppercase;">1. Guest Details</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.85rem;">First Name *</label>
                            <input type="text" id="b-first-name" placeholder="First name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.85rem;">Last Name *</label>
                            <input type="text" id="b-last-name" placeholder="Last name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.85rem;">Email *</label>
                            <input type="email" id="b-email" placeholder="Email address" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.85rem;">Phone</label>
                            <input type="text" id="b-phone" placeholder="Phone number" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Room & Dates -->
                <div style="margin-bottom:22px; padding:16px; background:#f8f9fa; border-radius:10px;">
                    <h4 style="margin:0 0 14px 0; color:#e67e22; font-size:0.95rem; letter-spacing:.5px; text-transform:uppercase;">2. Room &amp; Dates</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div class="form-group" style="margin:0; grid-column:1/-1;">
                            <label style="font-size:0.85rem;">Room</label>
                            <input type="text" id="b-room-display" readonly style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; background:#e9ecef; box-sizing:border-box;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.85rem;">Check In *</label>
                            <input type="date" id="b-check-in" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.85rem;">Check Out *</label>
                            <input type="date" id="b-check-out" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                        </div>
                    </div>
                    <div id="b-price-calc" style="display:none; margin-top:12px; padding:10px 14px; background:linear-gradient(135deg,#1a1a2e,#16213e); color:white; border-radius:8px; font-size:0.9rem;">
                        🏨 <span id="b-nights-label"></span> × <span id="b-rate-label"></span>/night = <strong id="b-total-label"></strong>
                    </div>
                </div>

                <!-- Section 3: Payment (decorative) -->
                <div style="margin-bottom:22px;">
                    <h4 style="margin:0 0 14px 0; color:#e67e22; font-size:0.95rem; letter-spacing:.5px; text-transform:uppercase;">3. Payment Details</h4>

                    <!-- Card type selector -->
                    <div style="display:flex; gap:10px; margin-bottom:16px;">
                        <label id="card-visa" onclick="selectCard('visa')" style="cursor:pointer; border:2px solid #ddd; border-radius:10px; padding:8px 16px; display:flex; align-items:center; gap:6px; transition:all .2s; background:white; font-weight:600; color:#1a1f71; font-size:0.9rem; user-select:none;">
                            <span style="font-size:1.3rem;">💳</span> VISA
                        </label>
                        <label id="card-mastercard" onclick="selectCard('mastercard')" style="cursor:pointer; border:2px solid #ddd; border-radius:10px; padding:8px 16px; display:flex; align-items:center; gap:6px; transition:all .2s; background:white; font-weight:600; color:#eb001b; font-size:0.9rem; user-select:none;">
                            <span style="font-size:1.3rem;">🔴</span> Mastercard
                        </label>
                        <label id="card-amex" onclick="selectCard('amex')" style="cursor:pointer; border:2px solid #ddd; border-radius:10px; padding:8px 16px; display:flex; align-items:center; gap:6px; transition:all .2s; background:white; font-weight:600; color:#007bc1; font-size:0.9rem; user-select:none;">
                            <span style="font-size:1.3rem;">🔵</span> Amex
                        </label>
                    </div>

                    <!-- Visual card preview -->
                    <div id="card-preview" style="background:linear-gradient(135deg,#1a1a2e 0%,#2d3561 100%); border-radius:14px; padding:22px 24px; color:white; margin-bottom:16px; min-height:130px; position:relative; overflow:hidden; box-shadow:0 8px 24px rgba(0,0,0,0.25);">
                        <div style="position:absolute;top:-20px;right:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,0.06);"></div>
                        <div style="position:absolute;top:20px;right:30px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,0.04);"></div>
                        <div style="font-size:0.7rem; letter-spacing:2px; opacity:0.6; margin-bottom:18px; text-transform:uppercase;" id="card-type-label">SELECT CARD TYPE</div>
                        <div style="font-size:1.2rem; letter-spacing:4px; font-weight:500; margin-bottom:16px; font-family:monospace;" id="card-number-display">•••• •••• •••• ••••</div>
                        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                            <div>
                                <div style="font-size:0.65rem; opacity:0.5; text-transform:uppercase; letter-spacing:1px;">Card Holder</div>
                                <div style="font-size:0.9rem;" id="card-holder-display">YOUR NAME</div>
                            </div>
                            <div>
                                <div style="font-size:0.65rem; opacity:0.5; text-transform:uppercase; letter-spacing:1px;">Expires</div>
                                <div style="font-size:0.9rem;" id="card-expiry-display">MM/YY</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card inputs -->
                    <div style="display:grid; gap:10px;">
                        <div>
                            <label style="font-size:0.85rem; display:block; margin-bottom:4px;">Card Number</label>
                            <input type="text" id="card-number-input" maxlength="19" placeholder="0000 0000 0000 0000" oninput="formatCardNumber(this)" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; font-family:monospace; letter-spacing:2px; box-sizing:border-box;">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <div>
                                <label style="font-size:0.85rem; display:block; margin-bottom:4px;">Name on Card</label>
                                <input type="text" id="card-name-input" placeholder="Full name" oninput="document.getElementById('card-holder-display').textContent = this.value.toUpperCase() || 'YOUR NAME'" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="font-size:0.85rem; display:block; margin-bottom:4px;">Expiry (MM/YY)</label>
                                <input type="text" id="card-expiry-input" maxlength="5" placeholder="MM/YY" oninput="formatExpiry(this)" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="font-size:0.85rem; display:block; margin-bottom:4px;">CVV</label>
                                <input type="text" id="card-cvv-input" maxlength="4" placeholder="•••" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; font-size:0.95rem; letter-spacing:3px; box-sizing:border-box;">
                            </div>
                        </div>
                    </div>
                    <p style="margin:8px 0 0 0; font-size:0.78rem; color:#999;">🔒 Payment details are for display only and are not processed.</p>
                </div>

                <!-- Submit -->
                <button type="submit" id="book-submit-btn" class="btn btn-primary" style="width:100%; padding:14px; font-size:1rem; border-radius:10px;">
                    Confirm Booking
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Page Header -->
<div class="glass-panel mb-2" style="padding: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h2 style="margin:0;">Available Rooms</h2>
        <p style="margin: 5px 0 0 0; color: #666;">Browse our selection of premium accommodations.</p>
    </div>
</div>

<div class="glass-panel" style="padding: 2rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php
        $rooms = get_rooms();
        if (empty($rooms)): ?>
            <p>No rooms are currently available.</p>
        <?php else: ?>
            <?php foreach ($rooms as $room): ?>
                <div class="glass-panel" style="padding: 15px; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h3 style="margin: 0;">Room <?php echo sanitize($room['room_number']); ?></h3>
                        <?php if ($room['status'] === 'available'): ?>
                            <span class="status-badge" style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.8rem;">Available</span>
                        <?php elseif (strtolower($room['status']) === 'pending'): ?>
                            <span class="status-badge" style="background-color: #fd7e14; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.8rem;">Pending</span>
                        <?php else: ?>
                            <span class="status-badge" style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.8rem;">Occupied</span>
                        <?php endif; ?>
                    </div>
                    <p style="color:var(--accent-color); font-weight: bold; font-size: 1.2rem; margin: 10px 0;">
                        $<?php echo number_format($room['price'], 2); ?> / night
                    </p>
                    <p style="flex-grow: 1; color: #555;">
                        Type: <span style="text-transform: capitalize;"><?php echo sanitize($room['type']); ?></span><br>
                        <?php echo sanitize($room['description']); ?>
                    </p>
                    <button class="btn btn-primary" style="margin-top: 15px; width: 100%; <?php echo ($room['status'] !== 'available') ? 'background-color: #ccc; border-color: #ccc; cursor: not-allowed;' : ''; ?>"
                        <?php if ($room['status'] !== 'available'): ?>
                            disabled
                        <?php else: ?>
                            onclick="openBookingModal(<?php echo $room['id']; ?>, '<?php echo sanitize($room['room_number']); ?>', '<?php echo sanitize($room['type']); ?>', <?php echo $room['price']; ?>)"
                        <?php endif; ?>>
                        <?php echo ($room['status'] !== 'available') ? 'Unavailable' : 'Book Now'; ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    const TODAY = new Date().toISOString().split('T')[0];
    let selectedCard = null;
    let currentPrice = 0;

    // Pre-fill name from session username as a hint
    const sessionUsername = "<?php echo addslashes($sessionUser['username'] ?? ''); ?>";

    function openBookingModal(roomId, roomNumber, roomType, price) {
        currentPrice = price;
        document.getElementById('booking-room-id').value = roomId;
        document.getElementById('b-room-display').value = 'Room ' + roomNumber + ' (' + roomType + ') — $' + parseFloat(price).toLocaleString('en-US', {minimumFractionDigits:2}) + '/night';
        document.getElementById('modal-room-label').textContent = 'Room ' + roomNumber + ' · ' + roomType.charAt(0).toUpperCase() + roomType.slice(1);

        // Set min date
        document.getElementById('b-check-in').min = TODAY;
        document.getElementById('b-check-out').min = TODAY;

        // Pre-fill username hint
        if (sessionUsername) {
            document.getElementById('b-first-name').value = sessionUsername;
        }

        // Clear banners
        document.getElementById('booking-success').style.display = 'none';
        document.getElementById('booking-error').style.display = 'none';
        document.getElementById('b-price-calc').style.display = 'none';
        document.getElementById('book-submit-btn').disabled = false;
        document.getElementById('book-submit-btn').textContent = 'Confirm Booking';

        document.getElementById('booking-modal').style.display = 'block';
    }

    function closeBookingModal() {
        document.getElementById('booking-modal').style.display = 'none';
        document.getElementById('booking-form').reset();
        selectedCard = null;
        resetCardSelectors();
        document.getElementById('card-number-display').textContent = '•••• •••• •••• ••••';
        document.getElementById('card-type-label').textContent = 'SELECT CARD TYPE';
        document.getElementById('card-holder-display').textContent = 'YOUR NAME';
        document.getElementById('card-expiry-display').textContent = 'MM/YY';
        document.getElementById('b-price-calc').style.display = 'none';
    }

    // Close on backdrop click
    document.getElementById('booking-modal').addEventListener('click', function(e) {
        if (e.target === this) closeBookingModal();
    });

    // Live price calculation
    function calcPrice() {
        const ci = document.getElementById('b-check-in').value;
        const co = document.getElementById('b-check-out').value;
        if (ci && co && co > ci) {
            const nights = Math.ceil((new Date(co) - new Date(ci)) / 86400000);
            const total = nights * currentPrice;
            document.getElementById('b-nights-label').textContent = nights + (nights === 1 ? ' night' : ' nights');
            document.getElementById('b-rate-label').textContent = '$' + parseFloat(currentPrice).toLocaleString('en-US', {minimumFractionDigits:2});
            document.getElementById('b-total-label').textContent = '$' + total.toLocaleString('en-US', {minimumFractionDigits:2});
            document.getElementById('b-price-calc').style.display = 'block';
            // auto-set check-out min
            document.getElementById('b-check-out').min = ci;
        } else {
            document.getElementById('b-price-calc').style.display = 'none';
        }
    }
    document.getElementById('b-check-in').addEventListener('change', calcPrice);
    document.getElementById('b-check-out').addEventListener('change', calcPrice);

    // Card selection
    function selectCard(type) {
        selectedCard = type;
        resetCardSelectors();
        const el = document.getElementById('card-' + type);
        el.style.borderColor = '#0d6efd';
        el.style.background = '#e8f0fe';

        const labels = { visa: 'VISA', mastercard: 'MASTERCARD', amex: 'AMERICAN EXPRESS' };
        document.getElementById('card-type-label').textContent = labels[type];

        // Change gradient per card
        const gradients = {
            visa: 'linear-gradient(135deg,#1a1f71 0%,#2d54c5 100%)',
            mastercard: 'linear-gradient(135deg,#eb001b 0%,#f79e1b 100%)',
            amex: 'linear-gradient(135deg,#007bc1 0%,#00b4d8 100%)'
        };
        document.getElementById('card-preview').style.background = gradients[type];
    }

    function resetCardSelectors() {
        ['visa','mastercard','amex'].forEach(c => {
            const el = document.getElementById('card-' + c);
            el.style.borderColor = '#ddd';
            el.style.background = 'white';
        });
    }

    function formatCardNumber(input) {
        let val = input.value.replace(/\D/g, '').substring(0, 16);
        let formatted = val.match(/.{1,4}/g)?.join(' ') || '';
        input.value = formatted;
        // Update card preview display
        let display = formatted || '';
        while (display.replace(/\s/g,'').length < 16) display += (display.length && display[display.length-1] !== ' ' && display.length % 5 === 4 ? ' ' : '•');
        const raw = val.padEnd(16, '•');
        const disp = raw.match(/.{1,4}/g)?.join(' ') || '•••• •••• •••• ••••';
        document.getElementById('card-number-display').textContent = disp;
    }

    function formatExpiry(input) {
        let val = input.value.replace(/\D/g, '').substring(0, 4);
        if (val.length >= 3) val = val.substring(0,2) + '/' + val.substring(2);
        input.value = val;
        document.getElementById('card-expiry-display').textContent = val || 'MM/YY';
    }

    async function submitBooking(e) {
        e.preventDefault();
        const btn = document.getElementById('book-submit-btn');
        btn.disabled = true;
        btn.textContent = 'Processing...';

        document.getElementById('booking-success').style.display = 'none';
        document.getElementById('booking-error').style.display = 'none';

        const payload = {
            room_id:    document.getElementById('booking-room-id').value,
            first_name: document.getElementById('b-first-name').value,
            last_name:  document.getElementById('b-last-name').value,
            email:      document.getElementById('b-email').value,
            phone:      document.getElementById('b-phone').value,
            check_in:   document.getElementById('b-check-in').value,
            check_out:  document.getElementById('b-check-out').value,
        };

        try {
            const res = await fetch('book_room.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('booking-success-msg').textContent =
                    'Booking confirmed! (#' + data.booking_id + ') — ' + data.nights + ' night(s) · Total: $' + data.total;
                document.getElementById('booking-success').style.display = 'block';
                btn.textContent = '✅ Booked!';
                // Reload the room grid after a short delay so statuses update
                setTimeout(() => location.reload(), 2200);
            } else {
                document.getElementById('booking-error-msg').textContent = data.error || 'An error occurred.';
                document.getElementById('booking-error').style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Confirm Booking';
            }
        } catch (err) {
            document.getElementById('booking-error-msg').textContent = 'Network error. Please try again.';
            document.getElementById('booking-error').style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Confirm Booking';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>