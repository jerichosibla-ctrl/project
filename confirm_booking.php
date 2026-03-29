<?php
$pageTitle = 'Booking Confirmed';
require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT res.*, r.room_type, r.price, h.hotel_name, h.location
    FROM reservations res
    JOIN rooms r ON res.room_id = r.id
    JOIN hotels h ON r.hotel_id = h.id
    WHERE res.id = ?
");
$stmt->execute([$id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    header('Location: index.php');
    exit();
}

// Calculate total
$nights = (strtotime($reservation['check_out']) - strtotime($reservation['check_in'])) / 86400;
$total  = $nights * $reservation['price'];

require_once 'includes/header.php';
?>

<div class="page-header" style="text-align:center;">
  <div class="container">
    <h1><i class="fas fa-check-circle me-2"></i>Booking Submitted!</h1>
    <p style="color:rgba(255,255,255,0.75);margin:0;">Your reservation has been received and is pending confirmation.</p>
  </div>
</div>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <div class="confirm-card">

        <div class="confirm-icon">
          <i class="fas fa-check"></i>
        </div>

        <h3 style="color:var(--success);font-family:'Playfair Display',serif;">
          Booking Received!
        </h3>
        <p class="text-muted">
          Thank you, <strong><?= htmlspecialchars($reservation['full_name']) ?></strong>!
          Your booking is currently <strong>Pending</strong>. The hotel will confirm your reservation shortly.
        </p>

        <!-- Booking Reference -->
        <div style="background:var(--primary);color:white;border-radius:var(--radius-sm);padding:10px 20px;display:inline-block;margin:12px 0;">
          <span style="font-size:0.85rem;opacity:0.7;">Booking Reference</span><br>
          <strong style="font-size:1.3rem;font-family:'Playfair Display',serif;color:var(--accent);">
            #PCH-<?= str_pad($reservation['id'], 5, '0', STR_PAD_LEFT) ?>
          </strong>
        </div>

        <!-- Details -->
        <div class="confirm-details">
          <div class="confirm-row">
            <span class="label"><i class="fas fa-hotel me-2"></i>Hotel</span>
            <span class="value"><?= htmlspecialchars($reservation['hotel_name']) ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-bed me-2"></i>Room Type</span>
            <span class="value"><?= htmlspecialchars($reservation['room_type']) ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-user me-2"></i>Guest Name</span>
            <span class="value"><?= htmlspecialchars($reservation['full_name']) ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-envelope me-2"></i>Email</span>
            <span class="value"><?= htmlspecialchars($reservation['email']) ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-phone me-2"></i>Phone</span>
            <span class="value"><?= htmlspecialchars($reservation['phone']) ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-calendar-alt me-2"></i>Check-in</span>
            <span class="value"><?= date('F j, Y', strtotime($reservation['check_in'])) ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-calendar-alt me-2"></i>Check-out</span>
            <span class="value"><?= date('F j, Y', strtotime($reservation['check_out'])) ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-moon me-2"></i>Duration</span>
            <span class="value"><?= $nights ?> night<?= $nights !== 1 ? 's' : '' ?></span>
          </div>
          <div class="confirm-row">
            <span class="label"><i class="fas fa-peso-sign me-2"></i>Rate per Night</span>
            <span class="value">₱<?= number_format($reservation['price'], 2) ?></span>
          </div>
          <div class="confirm-row" style="background:rgba(46,125,82,0.08);border-radius:var(--radius-sm);padding:10px 12px;">
            <span class="label" style="font-weight:700;color:var(--success);font-size:1rem;">
              <i class="fas fa-receipt me-2"></i>Total Amount
            </span>
            <span class="value" style="font-size:1.2rem;color:var(--success);">
              ₱<?= number_format($total, 2) ?>
            </span>
          </div>
        </div>

        <!-- Status badge -->
        <div class="mb-4">
          <span class="badge badge-pending px-3 py-2" style="font-size:0.85rem;border-radius:50px;">
            <i class="fas fa-clock me-1"></i>Status: Pending
          </span>
        </div>

        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="index.php" class="btn-primary-custom">
            <i class="fas fa-home me-2"></i>Back to Home
          </a>
          <a href="hotels.php" class="btn-outline-custom">
            <i class="fas fa-building me-2"></i>More Hotels
          </a>
        </div>

        <p class="text-muted mt-4" style="font-size:0.85rem;">
          <i class="fas fa-info-circle me-1"></i>
          A confirmation will be sent to <strong><?= htmlspecialchars($reservation['email']) ?></strong>
          once the hotel processes your booking.
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
