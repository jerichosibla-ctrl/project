<?php
$pageTitle = 'Home';
require_once 'includes/db.php';
require_once 'includes/header.php';

// Fetch all hotels
$stmt = $pdo->query("SELECT * FROM hotels ORDER BY hotel_name");
$hotels = $stmt->fetchAll();

// Count stats
$totalHotels = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
$totalRooms  = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='Available'")->fetchColumn();
$totalBookings = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
?>

<!-- HERO -->
<section class="hero-section">
  <div class="container position-relative">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="hero-badge fade-in-up">
          <i class="fas fa-map-marker-alt me-2"></i>Pagadian City, Zamboanga del Sur
        </div>
        <h1 class="hero-title fade-in-up">
          Discover the Finest
          <span>Hotels in Pagadian</span>
        </h1>
        <p class="hero-subtitle fade-in-up">
          Book your perfect stay in Pagadian City. From budget-friendly pensions to luxury suites —
          experience genuine Southern Filipino hospitality at its best.
        </p>
        <div class="d-flex gap-3 flex-wrap fade-in-up">
          <a href="hotels.php" class="btn-accent">
            <i class="fas fa-search me-2"></i>Browse Hotels
          </a>
          <a href="#hotels-section" class="btn-outline-custom" style="color:white;border-color:rgba(255,255,255,0.5)">
            <i class="fas fa-arrow-down me-2"></i>View All Hotels
          </a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block text-center">
        <div style="font-size:8rem; opacity:0.15; color:white; position:absolute; right:0; top:-20px;">
          <i class="fas fa-hotel"></i>
        </div>
      </div>
    </div>

    <!-- Quick stats -->
    <div class="row mt-5 g-3">
      <div class="col-4">
        <div class="text-center" style="color:white;">
          <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--accent)">
            <?= $totalHotels ?>
          </div>
          <div style="font-size:0.8rem;opacity:0.7;text-transform:uppercase;letter-spacing:1px;">Hotels</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SEARCH BAR -->
<div class="container">
  <div class="search-bar-wrap">
    <div class="row g-3 align-items-end">
      <div class="col-md-8">
        <label class="form-label fw-semibold">
          <i class="fas fa-search me-2 text-accent"></i>Search Hotels
        </label>
        <input type="text" class="form-control form-control-lg" id="hotelSearch"
          placeholder="Search by hotel name or location...">
      </div>
      <div class="col-md-4">
        <a href="hotels.php" class="btn-primary-custom w-100 text-center py-3 rounded">
          <i class="fas fa-building me-2"></i>View All Hotels
        </a>
      </div>
    </div>
  </div>
</div>

<!-- HOTELS SECTION -->
<section class="py-5" id="hotels-section">
  <div class="container">
    <div class="row mb-4">
      <div class="col">
        <div class="section-title">Hotels in Pagadian City</div>
        <div class="gold-line"></div>
        <p class="section-subtitle">Choose from <?= $totalHotels ?> quality accommodations across the city</p>
      </div>
    </div>

    <div class="row g-4" id="hotelsGrid">
      <?php foreach ($hotels as $i => $hotel): ?>
      <div class="col-lg-4 col-md-6 hotel-card-wrapper"
           data-name="<?= htmlspecialchars($hotel['hotel_name']) ?>"
           data-location="<?= htmlspecialchars($hotel['location']) ?>">
        <div class="hotel-card h-100">
          <div class="hotel-img-wrap">
            <?php
            $imgPath = 'uploads/hotels/' . $hotel['image'];
            $defaultImg = 'assets/images/hotel-default.jpg';
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
            $roomCount = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE hotel_id = ? AND status = 'Available'");
            $roomCount->execute([$hotel['id']]);
            $availRooms = $roomCount->fetchColumn();
            ?>
            <div class="mt-auto">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <small class="text-muted">
                  <i class="fas fa-door-open me-1 text-accent"></i>
                  <strong><?= $availRooms ?></strong> rooms available
                </small>
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
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="py-5" style="background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);">
  <div class="container">
    <div class="text-center mb-5">
      <div style="color:var(--accent);font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;">
        Why Book With PagadianStay?
      </div>
      <div class="gold-line mx-auto"></div>
    </div>
    <div class="row g-4 text-center text-white">
      <div class="col-md-3 stagger-1">
        <div style="font-size:2.5rem;color:var(--accent);margin-bottom:16px;"><i class="fas fa-shield-alt"></i></div>
        <h5 style="color:white;font-family:'DM Sans',sans-serif;font-weight:700;">Secure Booking</h5>
        <p style="color:rgba(255,255,255,0.65);font-size:0.9rem;">Your reservation is protected with our verified system.</p>
      </div>
      <div class="col-md-3 stagger-2">
        <div style="font-size:2.5rem;color:var(--accent);margin-bottom:16px;"><i class="fas fa-dollar-sign"></i></div>
        <h5 style="color:white;font-family:'DM Sans',sans-serif;font-weight:700;">Best Rates</h5>
        <p style="color:rgba(255,255,255,0.65);font-size:0.9rem;">Direct hotel rates — no hidden fees or markups.</p>
      </div>
      <div class="col-md-3 stagger-3">
        <div style="font-size:2.5rem;color:var(--accent);margin-bottom:16px;"><i class="fas fa-check-circle"></i></div>
        <h5 style="color:white;font-family:'DM Sans',sans-serif;font-weight:700;">Instant Confirmation</h5>
        <p style="color:rgba(255,255,255,0.65);font-size:0.9rem;">Get your booking confirmation immediately on submit.</p>
      </div>
      <div class="col-md-3 stagger-1">
        <div style="font-size:2.5rem;color:var(--accent);margin-bottom:16px;"><i class="fas fa-map-marked-alt"></i></div>
        <h5 style="color:white;font-family:'DM Sans',sans-serif;font-weight:700;">Local Expertise</h5>
        <p style="color:rgba(255,255,255,0.65);font-size:0.9rem;">We know Pagadian City inside out.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
