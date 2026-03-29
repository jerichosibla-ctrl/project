<?php
$pageTitle = 'Manage Reservations';
require_once 'admin_header.php';
require_once '../includes/db.php';

$msg = '';
$msgType = 'success';
$editRes = null;

// --- DELETE ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM reservations WHERE id=?")->execute([(int)$_GET['delete']]);
    header('Location: reservations.php?msg=' . urlencode('Reservation deleted.'));
    exit();
}

// --- EDIT FETCH ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT res.*, r.room_type, h.hotel_name FROM reservations res JOIN rooms r ON res.room_id=r.id JOIN hotels h ON r.hotel_id=h.id WHERE res.id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editRes = $stmt->fetch();
}

// --- UPDATE STATUS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resId  = (int)($_POST['res_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($resId && in_array($status, ['Pending','Confirmed','Cancelled'])) {
        $pdo->prepare("UPDATE reservations SET status=? WHERE id=?")->execute([$status, $resId]);
        header('Location: reservations.php?msg=' . urlencode('Reservation status updated to ' . $status . '.'));
        exit();
    }
}

if (isset($_GET['msg'])) { $msg = $_GET['msg']; }

// Filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];
if ($statusFilter !== 'all') {
    $where .= " AND res.status = ?";
    $params[] = $statusFilter;
}
if ($search) {
    $where .= " AND (res.full_name LIKE ? OR res.email LIKE ? OR h.hotel_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM reservations res JOIN rooms r ON res.room_id=r.id JOIN hotels h ON r.hotel_id=h.id $where");
$countStmt->execute($params);
$totalCount = $countStmt->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$stmt = $pdo->prepare("
    SELECT res.*, r.room_type, r.price, h.hotel_name
    FROM reservations res
    JOIN rooms r ON res.room_id=r.id
    JOIN hotels h ON r.hotel_id=h.id
    $where
    ORDER BY res.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$reservations = $stmt->fetchAll();

// Stats
$statAll       = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$statPending   = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='Pending'")->fetchColumn();
$statConfirmed = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='Confirmed'")->fetchColumn();
$statCancelled = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='Cancelled'")->fetchColumn();
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show auto-dismiss mb-3">
  <i class="fas fa-check-circle me-2"></i>
  <?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- STATUS TABS -->
<div class="d-flex flex-wrap gap-2 mb-4">
  <?php
  $tabs = [
    'all'       => ['label' => 'All', 'count' => $statAll,       'color' => 'primary'],
    'Pending'   => ['label' => 'Pending',   'count' => $statPending,   'color' => 'warning'],
    'Confirmed' => ['label' => 'Confirmed', 'count' => $statConfirmed, 'color' => 'success'],
    'Cancelled' => ['label' => 'Cancelled', 'count' => $statCancelled, 'color' => 'danger'],
  ];
  foreach ($tabs as $val => $tab): ?>
  <a href="reservations.php?status=<?= $val ?><?= $search ? '&search='.urlencode($search) : '' ?>"
     class="btn btn-sm <?= $statusFilter === $val ? 'btn-'.$tab['color'] : 'btn-outline-'.$tab['color'] ?>">
    <?= $tab['label'] ?> (<?= $tab['count'] ?>)
  </a>
  <?php endforeach; ?>
</div>

<!-- EDIT MODAL (inline) -->
<?php if ($editRes): ?>
<div class="admin-card mb-4">
  <div class="admin-card-header" style="border-left:4px solid var(--accent);">
    <h5><i class="fas fa-edit me-2 text-accent"></i>Update Reservation #PCH-<?= str_pad($editRes['id'],5,'0',STR_PAD_LEFT) ?></h5>
    <a href="reservations.php" class="btn-sm-action btn-view">Cancel</a>
  </div>
  <div class="admin-card-body">
    <div class="row">
      <div class="col-md-6 mb-3">
        <div style="background:var(--light);border-radius:var(--radius-sm);padding:16px;">
          <div style="font-size:0.85rem;color:var(--muted);margin-bottom:6px;">Guest Details</div>
          <strong><?= htmlspecialchars($editRes['full_name']) ?></strong><br>
          <span style="color:var(--muted);font-size:0.9rem;"><?= htmlspecialchars($editRes['email']) ?> | <?= htmlspecialchars($editRes['phone']) ?></span>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div style="background:var(--light);border-radius:var(--radius-sm);padding:16px;">
          <div style="font-size:0.85rem;color:var(--muted);margin-bottom:6px;">Booking Details</div>
          <strong><?= htmlspecialchars($editRes['hotel_name']) ?></strong> — <?= htmlspecialchars($editRes['room_type']) ?><br>
          <span style="color:var(--muted);font-size:0.9rem;">
            <?= date('M j', strtotime($editRes['check_in'])) ?> → <?= date('M j, Y', strtotime($editRes['check_out'])) ?>
          </span>
        </div>
      </div>
    </div>
    <form method="POST" action="reservations.php" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
      <input type="hidden" name="res_id" value="<?= $editRes['id'] ?>">
      <label class="form-label mb-0">Update Status:</label>
      <select name="status" class="form-select" style="width:auto;">
        <option value="Pending"   <?= $editRes['status']==='Pending'   ? 'selected' : '' ?>>Pending</option>
        <option value="Confirmed" <?= $editRes['status']==='Confirmed' ? 'selected' : '' ?>>Confirmed</option>
        <option value="Cancelled" <?= $editRes['status']==='Cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
      <button type="submit" class="btn-admin-accent">
        <i class="fas fa-save me-1"></i>Save
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- SEARCH + TABLE -->
<div class="admin-card">
  <div class="admin-card-header">
    <h5><i class="fas fa-calendar-check me-2 text-accent"></i>
      Reservations (<?= $totalCount ?>)
    </h5>
    <form method="GET" style="display:flex;gap:6px;">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
      <input type="text" name="search" class="form-control form-control-sm" style="width:200px;"
        placeholder="Search name, email, hotel..."
        value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-search"></i>
      </button>
      <?php if ($search): ?>
      <a href="reservations.php?status=<?= $statusFilter ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Ref #</th>
          <th>Guest</th>
          <th>Hotel</th>
          <th>Room</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Nights</th>
          <th>Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reservations)): ?>
        <tr>
          <td colspan="10" class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times me-2"></i>No reservations found.
          </td>
        </tr>
        <?php else: ?>
        <?php foreach ($reservations as $res): ?>
        <?php
        $nights = (strtotime($res['check_out']) - strtotime($res['check_in'])) / 86400;
        $total  = $nights * $res['price'];
        ?>
        <tr>
          <td><strong style="color:var(--primary);">#PCH-<?= str_pad($res['id'],5,'0',STR_PAD_LEFT) ?></strong></td>
          <td>
            <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($res['full_name']) ?></div>
            <small style="color:var(--muted);"><?= htmlspecialchars($res['email']) ?></small><br>
            <small style="color:var(--muted);"><?= htmlspecialchars($res['phone']) ?></small>
          </td>
          <td style="font-size:0.88rem;"><?= htmlspecialchars($res['hotel_name']) ?></td>
          <td style="font-size:0.88rem;"><?= htmlspecialchars($res['room_type']) ?></td>
          <td style="font-size:0.88rem;"><?= date('M j, Y', strtotime($res['check_in'])) ?></td>
          <td style="font-size:0.88rem;"><?= date('M j, Y', strtotime($res['check_out'])) ?></td>
          <td style="text-align:center;"><?= $nights ?></td>
          <td style="color:var(--accent);font-weight:700;">₱<?= number_format($total, 2) ?></td>
          <td>
            <span class="status-badge badge-<?= strtolower($res['status']) ?>">
              <?= $res['status'] ?>
            </span>
          </td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:nowrap;">
              <a href="reservations.php?edit=<?= $res['id'] ?>" class="btn-sm-action btn-edit">
                <i class="fas fa-edit"></i>
              </a>
              <a href="reservations.php?delete=<?= $res['id'] ?>"
                 class="btn-sm-action btn-delete btn-confirm-delete">
                <i class="fas fa-trash"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="p-3 border-top d-flex justify-content-between align-items-center">
    <small class="text-muted">
      Showing <?= $offset+1 ?>–<?= min($offset+$perPage, $totalCount) ?> of <?= $totalCount ?> records
    </small>
    <nav>
      <ul class="pagination pagination-sm mb-0">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
          <a class="page-link" href="reservations.php?status=<?= $statusFilter ?>&page=<?= $p ?><?= $search ? '&search='.urlencode($search) : '' ?>">
            <?= $p ?>
          </a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</div>

<?php require_once 'admin_footer.php'; ?>
