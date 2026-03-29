<?php
$pageTitle = 'Book Room';
require_once 'includes/db.php';

$room_id  = isset($_GET['room_id'])  ? (int)$_GET['room_id']  : 0;
$checkIn  = isset($_GET['check_in'])  ? $_GET['check_in']  : '';
$checkOut = isset($_GET['check_out']) ? $_GET['check_out'] : '';

if (!$room_id) {
    header('Location: hotels.php');
    exit();
}

// Fetch room and hotel
$stmt = $pdo->prepare("
    SELECT r.*, h.hotel_name, h.id as hotel_id, h.location
    FROM rooms r
    JOIN hotels h ON r.hotel_id = h.id
    WHERE r.id = ?
");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room || $room['status'] !== 'Available') {
    header('Location: hotels.php');
    exit();
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email']     ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $checkIn   = trim($_POST['check_in']  ?? '');
    $checkOut  = trim($_POST['check_out'] ?? '');
    $roomId    = (int)($_POST['room_id']  ?? 0);

    // Validation
    $errors = [];
    if (strlen($full_name) < 3)   $errors[] = 'Full name must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (!preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) $errors[] = 'Please enter a valid phone number.';
    if (!$checkIn)  $errors[] = 'Check-in date is required.';
    if (!$checkOut) $errors[] = 'Check-out date is required.';
    if ($checkIn && $checkOut && $checkOut <= $checkIn)
        $errors[] = 'Check-out must be after check-in.';
    if ($checkIn && strtotime($checkIn) < strtotime(date('Y-m-d')))
        $errors[] = 'Check-in date cannot be in the past.';

    if (empty($errors)) {
        // Check double booking
        $chk = $pdo->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE room_id = ?
              AND status != 'Cancelled'
              AND check_in < ?
              AND check_out > ?
        ");
        $chk->execute([$roomId, $checkOut, $checkIn]);
        if ($chk->fetchColumn() > 0) {
            $errors[] = 'This room is already booked for the selected dates. Please choose different dates.';
        }
    }

    if (empty($errors)) {
        $ins = $pdo->prepare("
            INSERT INTO reservations (full_name, email, phone, check_in, check_out, room_id, status)
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $ins->execute([$full_name, $email, $phone, $checkIn, $checkOut, $roomId]);
        $reservationId = $pdo->lastInsertId();
        header("Location: confirm_booking.php?id=$reservationId");
        exit();
    } else {
        $error = implode('<br>', $errors);
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="hotels.php">Hotels</a></li>
        <li class="breadcrumb-item">
          <a href="rooms.php?hotel_id=<?= $room['hotel_id'] ?>"><?= htmlspecialchars($room['hotel_name']) ?></a>
        </li>
        <li class="breadcrumb-item active">Book Room</li>
      </ol>
    </nav>
    <h1><i class="fas fa-calendar-plus me-2"></i>Book Your Room</h1>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">

    <!-- Booking Form -->
    <div class="col-lg-7">
      <div class="booking-card">
        <h4 style="color:var(--primary);font-family:'Playfair Display',serif;margin-bottom:4px;">
          Guest Information
        </h4>
        <div class="gold-line mb-4"></div>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="fas fa-exclamation-circle me-2"></i>
          <?= $error ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="booking.php?room_id=<?= $room_id ?>" id="bookingForm">
          <input type="hidden" name="room_id" value="<?= $room['id'] ?>">

          <div class="mb-3">
            <label class="form-label" for="full_name">
              <i class="fas fa-user me-1 text-accent"></i>Full Name *
            </label>
            <input type="text" class="form-control" id="full_name" name="full_name"
              placeholder="e.g. Juan Dela Cruz"
              value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            <div class="invalid-feedback">Please enter your full name (at least 3 characters).</div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="email">
                <i class="fas fa-envelope me-1 text-accent"></i>Email Address *
              </label>
              <input type="email" class="form-control" id="email" name="email"
                placeholder="you@email.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              <div class="invalid-feedback">Please enter a valid email.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="phone">
                <i class="fas fa-phone me-1 text-accent"></i>Phone Number *
              </label>
              <input type="text" class="form-control" id="phone" name="phone"
                placeholder="e.g. 09xx-xxx-xxxx"
                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
              <div class="invalid-feedback">Please enter a valid phone number.</div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="check_in">
                <i class="fas fa-calendar-alt me-1 text-accent"></i>Check-in Date *
              </label>
              <input type="date" class="form-control" id="check_in" name="check_in"
                value="<?= htmlspecialchars($checkIn ?: ($_POST['check_in'] ?? '')) ?>"
                min="<?= date('Y-m-d') ?>" required>
              <div class="invalid-feedback">Please select a check-in date.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="check_out">
                <i class="fas fa-calendar-alt me-1 text-accent"></i>Check-out Date *
              </label>
              <input type="date" class="form-control" id="check_out" name="check_out"
                value="<?= htmlspecialchars($checkOut ?: ($_POST['check_out'] ?? '')) ?>"
                min="<?= date('Y-m-d') ?>" required>
              <div class="invalid-feedback">Please select a check-out date.</div>
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label">Hotel</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($room['hotel_name']) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Room Type</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($room['room_type']) ?>" disabled>
            </div>
          </div>

          <!-- Hidden price for JS -->
          <input type="hidden" id="pricePerNight" value="<?= $room['price'] ?>">
          <input type="hidden" id="total_price" name="total_price">

          <button type="submit" class="btn-accent w-100 text-center py-3 rounded" style="font-size:1rem;">
            <i class="fas fa-check-circle me-2"></i>Confirm Booking
          </button>

          <p class="text-muted text-center mt-3" style="font-size:0.85rem;">
            <i class="fas fa-shield-alt me-1"></i>
            Your booking will be submitted as Pending and confirmed by the hotel.
          </p>
        </form>
      </div>
    </div>

    <!-- Price Summary Sidebar -->
    <div class="col-lg-5">
      <!-- Room Preview -->
      <div class="booking-card mb-4">
        <h5 style="color:var(--primary);font-family:'Playfair Display',serif;margin-bottom:4px;">
          Room Summary
        </h5>
        <div class="gold-line mb-3"></div>

        <div class="room-img-wrap mb-3" style="height:160px;border-radius:var(--radius-sm);">
          <?php
          $rImgPath = 'uploads/rooms/' . $room['image'];
          $rShowImg = (file_exists($rImgPath) && $room['image'] !== 'default_room.jpg') ? $rImgPath : null;
          ?>
          <?php if ($rShowImg): ?>
            <img src="<?= htmlspecialchars($rShowImg) ?>" alt="<?= htmlspecialchars($room['room_type']) ?>">
          <?php else: ?>
            <div class="img-placeholder" style="height:100%"><i class="fas fa-bed"></i></div>
          <?php endif; ?>
        </div>

        <h5 style="font-family:'Playfair Display',serif;color:var(--primary);">
          <?= htmlspecialchars($room['room_type']) ?>
        </h5>
        <p class="text-muted mb-1"><i class="fas fa-hotel me-1 text-accent"></i><?= htmlspecialchars($room['hotel_name']) ?></p>
        <p class="text-muted mb-0" style="font-size:0.85rem;"><i class="fas fa-map-marker-alt me-1 text-accent"></i><?= htmlspecialchars($room['location']) ?></p>
      </div>

      <!-- Price Summary -->
      <div class="price-summary">
        <h5><i class="fas fa-receipt me-2"></i>Price Breakdown</h5>
        <div class="mt-3">
          <div class="price-row">
            <span>Room Rate</span>
            <span>₱<?= number_format($room['price'], 2) ?>/night</span>
          </div>
          <div class="price-row">
            <span>Duration</span>
            <span id="totalDays">Select dates</span>
          </div>
          <div class="price-row total">
            <span>Total Amount</span>
            <span id="totalPrice">—</span>
          </div>
        </div>
        <p class="mt-3 mb-0" style="font-size:0.8rem;color:rgba(255,255,255,0.6);">
          * Final amount calculated based on selected dates.
        </p>
      </div>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
