<?php
$pageTitle = 'Dashboard';
require_once 'admin_header.php';
require_once '../includes/db.php';

// Stats
$totalHotels       = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
$totalRooms        = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$availableRooms    = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='Available'")->fetchColumn();
$totalReservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$pendingRes        = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='Pending'")->fetchColumn();
$confirmedRes      = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='Confirmed'")->fetchColumn();
$cancelledRes      = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='Cancelled'")->fetchColumn();

// Recent reservations
$recent = $pdo->query("
    SELECT res.*, r.room_type, h.hotel_name
    FROM reservations res
    JOIN rooms r ON res.room_id = r.id
    JOIN hotels h ON r.hotel_id = h.id
    ORDER BY res.created_at DESC
    LIMIT 8
")->fetchAll();
?>

<!-- STATS ROW -->
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fas fa-hotel"></i></div>
      <div>
        <div class="stat-num"><?= $totalHotels ?></div>
        <div class="stat-label">Total Hotels</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="stat-icon gold"><i class="fas fa-bed"></i></div>
      <div>
        <div class="stat-num"><?= $totalRooms ?></div>
        <div class="stat-label">Total Rooms</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
      <div>
        <div class="stat-num"><?= $availableRooms ?></div>
        <div class="stat-label">Available Rooms</div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="stat-card">
      <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
      <div>
        <div class="stat-num"><?= $totalReservations ?></div>
        <div class="stat-label">Total Reservations</div>
      </div>
    </div>
  </div>
</div>

<!-- RESERVATION STATUS ROW -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
      <div>
        <div class="stat-num"><?= $pendingRes ?></div>
        <div class="stat-label">Pending</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon green"><i class="fas fa-check"></i></div>
      <div>
        <div class="stat-num"><?= $confirmedRes ?></div>
        <div class="stat-label">Confirmed</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
      <div>
        <div class="stat-num"><?= $cancelledRes ?></div>
        <div class="stat-label">Cancelled</div>
      </div>
    </div>
  </div>
</div>

<!-- RECENT RESERVATIONS -->
<div class="admin-card">
  <div class="admin-card-header">
    <h5><i class="fas fa-calendar-alt me-2 text-accent"></i>Recent Reservations</h5>
    <a href="reservations.php" class="btn-sm-action btn-view">
      <i class="fas fa-eye me-1"></i>View All
    </a>
  </div>
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Guest</th>
          <th>Hotel</th>
          <th>Room</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent)): ?>
        <tr>
          <td colspan="8" class="text-center py-4 text-muted">
            <i class="fas fa-calendar-times me-2"></i>No reservations yet
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($recent as $r): ?>
        <tr>
          <td><strong>#PCH-<?= str_pad($r['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
          <td>
            <div style="font-weight:600;"><?= htmlspecialchars($r['full_name']) ?></div>
            <small style="color:var(--muted)"><?= htmlspecialchars($r['email']) ?></small>
          </td>
          <td><?= htmlspecialchars($r['hotel_name']) ?></td>
          <td><?= htmlspecialchars($r['room_type']) ?></td>
          <td><?= date('M j, Y', strtotime($r['check_in'])) ?></td>
          <td><?= date('M j, Y', strtotime($r['check_out'])) ?></td>
          <td>
            <span class="status-badge badge-<?= strtolower($r['status']) ?>">
              <?= $r['status'] ?>
            </span>
          </td>
          <td>
            <a href="reservations.php?edit=<?= $r['id'] ?>" class="btn-sm-action btn-edit">
              <i class="fas fa-edit"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'admin_footer.php'; ?>
