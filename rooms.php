<?php
$pageTitle = 'Rooms';
require_once 'includes/db.php';

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;

if (!$hotel_id) {
    header('Location: hotels.php');
    exit();
}

// Fetch hotel
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header('Location: hotels.php');
    exit();
}

// Availability check
$checkIn  = isset($_GET['check_in'])  ? $_GET['check_in']  : '';
$checkOut = isset($_GET['check_out']) ? $_GET['check_out'] : '';

// Filters
$typeFilter  = isset($_GET['room_type'])  ? $_GET['room_type']  : 'all';
$priceFilter = isset($_GET['price_range']) ? $_GET['price_range'] : 'all';

// Fetch rooms
$query = "SELECT * FROM rooms WHERE hotel_id = :hotel_id";
$params = ['hotel_id' => $hotel_id];

if ($typeFilter !== 'all') {
    $query .= " AND room_type LIKE :rtype";
    $params['rtype'] = "%$typeFilter%";
}

if ($priceFilter !== 'all') {
    list($minP, $maxP) = explode('-', $priceFilter . '-9999999');
    $query .= " AND price >= :minp";
    $params['minp'] = (float)$minP;
    if ($maxP && $maxP !== '9999999') {
        $query .= " AND price <= :maxp";
        $params['maxp'] = (float)$maxP;
    }
}

$query .= " ORDER BY price ASC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

// If availability dates set, filter out booked rooms
$bookedRoomIds = [];
if ($checkIn && $checkOut) {
    $bStmt = $pdo->prepare("
        SELECT DISTINCT room_id FROM reservations
        WHERE status != 'Cancelled'
          AND check_in < :check_out
          AND check_out > :check_in
    ");
    $bStmt->execute(['check_in' => $checkIn, 'check_out' => $checkOut]);
    $bookedRoomIds = array_column($bStmt->fetchAll(), 'room_id');
}

// Room types for filter
$rtStmt = $pdo->prepare("SELECT DISTINCT room_type FROM rooms WHERE hotel_id = ?");
$rtStmt->execute([$hotel_id]);
$roomTypes = array_column($rtStmt->fetchAll(), 'room_type');

require_once 'includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="hotels.php">Hotels</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($hotel['hotel_name']) ?></li>
      </ol>
    </nav>
    <h1><i class="fas fa-door-open me-2"></i><?= htmlspecialchars($hotel['hotel_name']) ?></h1>
    <p style="color:rgba(255,255,255,0.75);margin:0;">
      <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($hotel['location']) ?>
    </p>
  </div>
</div>

<div class="container py-5">

  <!-- Hotel Info Banner -->
  <div class="hotel-info-banner">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h2><?= htmlspecialchars($hotel['hotel_name']) ?></h2>
        <p class="location-text"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($hotel['location']) ?></p>
        <p style="color:rgba(255,255,255,0.8);margin:0;"><?= htmlspecialchars($hotel['description']) ?></p>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="hotels.php" class="btn-outline-custom" style="color:white;border-color:rgba(255,255,255,0.5)">
          <i class="fas fa-arrow-left me-1"></i>Back to Hotels
        </a>
      </div>
    </div>
  </div>

  <!-- Availability Checker -->
  <div class="avail-checker">
    <h5 class="mb-3" style="color:var(--primary)">
      <i class="fas fa-calendar-check me-2 text-accent"></i>Check Room Availability
    </h5>
    <form method="GET" action="rooms.php">
      <input type="hidden" name="hotel_id" value="<?= $hotel_id ?>">
      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Check-in Date</label>
          <input type="date" class="form-control" name="check_in"
            value="<?= htmlspecialchars($checkIn) ?>"
            min="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Check-out Date</label>
          <input type="date" class="form-control" name="check_out"
            value="<?= htmlspecialchars($checkOut) ?>"
            min="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Room Type</label>
          <select class="form-select" name="room_type">
            <option value="all">All Types</option>
            <?php foreach ($roomTypes as $rt): ?>
            <option value="<?= htmlspecialchars($rt) ?>" <?= $typeFilter === $rt ? 'selected' : '' ?>>
              <?= htmlspecialchars($rt) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Price Range</label>
          <select class="form-select" name="price_range">
            <option value="all">All Prices</option>
            <option value="0-1000" <?= $priceFilter === '0-1000' ? 'selected' : '' ?>>Under ₱1,000</option>
            <option value="1000-2000" <?= $priceFilter === '1000-2000' ? 'selected' : '' ?>>₱1,000 - ₱2,000</option>
            <option value="2000-4000" <?= $priceFilter === '2000-4000' ? 'selected' : '' ?>>₱2,000 - ₱4,000</option>
            <option value="4000-" <?= $priceFilter === '4000-' ? 'selected' : '' ?>>Above ₱4,000</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn-primary-custom w-100 text-center py-2 rounded">
            <i class="fas fa-search me-1"></i>Check
          </button>
        </div>
      </div>
    </form>
    <?php if ($checkIn && $checkOut): ?>
    <div class="mt-2">
      <small class="text-success fw-semibold">
        <i class="fas fa-info-circle me-1"></i>
        Showing availability for <?= htmlspecialchars($checkIn) ?> to <?= htmlspecialchars($checkOut) ?>
        — <a href="rooms.php?hotel_id=<?= $hotel_id ?>">Clear dates</a>
      </small>
    </div>
    <?php endif; ?>
  </div>

  <!-- Section heading -->
  <div class="row mb-4">
    <div class="col">
      <div class="section-title">Available Rooms</div>
      <div class="gold-line"></div>
      <p class="section-subtitle"><?= count($rooms) ?> room type<?= count($rooms) !== 1 ? 's' : '' ?> at <?= htmlspecialchars($hotel['hotel_name']) ?></p>
    </div>
  </div>

  <?php if (empty($rooms)): ?>
  <div class="text-center py-5">
    <div style="font-size:4rem;color:var(--muted);margin-bottom:16px;"><i class="fas fa-door-closed"></i></div>
    <h4 style="color:var(--muted)">No rooms found</h4>
    <p class="text-muted">Try adjusting your filters or dates.</p>
  </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($rooms as $room): ?>
    <?php
    $isBooked = in_array($room['id'], $bookedRoomIds);
    $isAvailable = ($room['status'] === 'Available') && !$isBooked;
    ?>
    <div class="col-lg-4 col-md-6 room-card-wrapper"
         data-price="<?= $room['price'] ?>"
         data-type="<?= htmlspecialchars($room['room_type']) ?>">
      <div class="room-card h-100">
        <div class="room-img-wrap">
          <?php
          $rImgPath = 'uploads/rooms/' . $room['image'];
          $rShowImg = (file_exists($rImgPath) && $room['image'] !== 'default_room.jpg') ? $rImgPath : null;
          ?>
          <?php if ($rShowImg): ?>
            <img src="<?= htmlspecialchars($rShowImg) ?>" alt="<?= htmlspecialchars($room['room_type']) ?>">
          <?php else: ?>
            <div class="img-placeholder" style="height:100%">
              <i class="fas fa-bed"></i>
            </div>
          <?php endif; ?>
          <div class="room-status <?= $isAvailable ? 'available' : 'unavailable' ?>">
            <?php if ($isBooked && $checkIn): ?>
              <i class="fas fa-ban me-1"></i>Booked
            <?php elseif ($room['status'] === 'Available'): ?>
              <i class="fas fa-check me-1"></i>Available
            <?php else: ?>
              <i class="fas fa-times me-1"></i>Unavailable
            <?php endif; ?>
          </div>
        </div>
        <div class="room-body d-flex flex-column">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h4 class="room-type"><?= htmlspecialchars($room['room_type']) ?></h4>
            <div class="room-price">
              ₱<?= number_format($room['price'], 2) ?>
              <span>/night</span>
            </div>
          </div>
          <p class="room-desc"><?= htmlspecialchars($room['description']) ?></p>
          <div class="mt-auto">
            <?php if ($isAvailable): ?>
            <a href="booking.php?room_id=<?= $room['id'] ?><?= $checkIn ? '&check_in='.urlencode($checkIn).'&check_out='.urlencode($checkOut) : '' ?>"
               class="btn-accent w-100 text-center rounded py-2">
              <i class="fas fa-calendar-plus me-2"></i>Book Now
            </a>
            <?php else: ?>
            <button class="btn w-100 py-2 rounded" disabled
              style="background:#f1f5f9;color:var(--muted);font-weight:600;cursor:not-allowed;">
              <i class="fas fa-ban me-2"></i>Not Available
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div id="noRoomsMsg" style="display:none;" class="text-center py-5">
    <div style="font-size:3rem;color:var(--muted);margin-bottom:12px;"><i class="fas fa-filter"></i></div>
    <h5 style="color:var(--muted)">No rooms match your filters</h5>
    <p class="text-muted">Try different filter options.</p>
  </div>
  <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
