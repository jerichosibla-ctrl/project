<?php
$pageTitle = 'All Hotels';
require_once 'includes/db.php';
require_once 'includes/header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE hotel_name LIKE ? OR location LIKE ? ORDER BY hotel_name");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM hotels ORDER BY hotel_name");
}
$hotels = $stmt->fetchAll();
?>

<div class="page-header">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">All Hotels</li>
      </ol>
    </nav>
    <h1><i class="fas fa-building me-2"></i>Hotels in Pagadian City</h1>
    <p style="color:rgba(255,255,255,0.75);margin:0;">
      <?= count($hotels) ?> hotel<?= count($hotels) !== 1 ? 's' : '' ?> found
      <?= $search ? ' for "' . htmlspecialchars($search) . '"' : '' ?>
    </p>
  </div>
</div>

<div class="container py-5">

  <!-- Search bar -->
  <div class="search-bar-wrap mb-5" style="margin-top:0;box-shadow:var(--shadow)">
    <form method="GET" action="hotels.php">
      <div class="row g-3 align-items-end">
        <div class="col-md-8">
          <label class="form-label fw-semibold">
            <i class="fas fa-search me-2 text-accent"></i>Search Hotels
          </label>
          <input type="text" class="form-control" name="search"
            placeholder="Hotel name or location..."
            value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn-primary-custom w-100 text-center py-2 rounded">
            <i class="fas fa-search me-1"></i>Search
          </button>
        </div>
        <div class="col-md-2">
          <a href="hotels.php" class="btn-outline-custom w-100 text-center py-2 rounded d-block">
            Clear
          </a>
        </div>
      </div>
    </form>
  </div>

  <?php if (empty($hotels)): ?>
  <div class="text-center py-5">
    <div style="font-size:4rem;color:var(--muted);margin-bottom:16px;"><i class="fas fa-hotel"></i></div>
    <h4 style="color:var(--muted)">No hotels found</h4>
    <p class="text-muted">Try a different search term.</p>
    <a href="hotels.php" class="btn-primary-custom">View All Hotels</a>
  </div>
  <?php else: ?>
  <div class="row g-4" id="hotelsGrid">
    <?php foreach ($hotels as $hotel): ?>
    <div class="col-lg-4 col-md-6 hotel-card-wrapper"
         data-name="<?= htmlspecialchars($hotel['hotel_name']) ?>"
         data-location="<?= htmlspecialchars($hotel['location']) ?>">
      <div class="hotel-card h-100">
        <div class="hotel-img-wrap">
          <?php
          $imgPath = 'uploads/hotels/' . $hotel['image'];
          $showImg = (file_exists($imgPath) && $hotel['image'] !== 'default_hotel.jpg') ? $imgPath : null;
          ?>
          <?php if ($showImg): ?>
            <img src="<?= htmlspecialchars($showImg) ?>" alt="<?= htmlspecialchars($hotel['hotel_name']) ?>">
          <?php else: ?>
            <div class="img-placeholder" style="height:100%">
              <i class="fas fa-hotel"></i>
            </div>
          <?php endif; ?>
          <div class="hotel-img-overlay">
            <span class="badge"><i class="fas fa-star me-1"></i>Pagadian City</span>
          </div>
        </div>
        <div class="hotel-body d-flex flex-column">
          <h3 class="hotel-name"><?= htmlspecialchars($hotel['hotel_name']) ?></h3>
          <p class="hotel-location">
            <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($hotel['location']) ?>
          </p>
          <p class="hotel-desc"><?= htmlspecialchars($hotel['description']) ?></p>
          <?php
          $rc = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ? AND status = 'Available'");
          $rc->execute([$hotel['id']]);
          $availRooms = $rc->fetchColumn();
          $minPrice = $pdo->prepare("SELECT MIN(price) FROM rooms WHERE hotel_id = ? AND status='Available'");
          $minPrice->execute([$hotel['id']]);
          $startPrice = $minPrice->fetchColumn();
          ?>
          <div class="mt-auto">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <small class="text-muted">
                <i class="fas fa-door-open me-1 text-accent"></i>
                <strong><?= $availRooms ?></strong> rooms available
              </small>
              <?php if ($startPrice): ?>
              <small style="color:var(--accent);font-weight:600;">
                From ₱<?= number_format($startPrice, 2) ?>/night
              </small>
              <?php endif; ?>
            </div>
            <a href="rooms.php?hotel_id=<?= $hotel['id'] ?>" class="btn-primary-custom w-100 text-center">
              <i class="fas fa-eye me-2"></i>View Rooms
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
