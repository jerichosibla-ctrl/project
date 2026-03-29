<?php
$pageTitle = 'Manage Rooms';
require_once 'admin_header.php';
require_once '../includes/db.php';

$msg = '';
$msgType = 'success';
$editRoom = null;

$filterHotel = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;

// --- DELETE ---
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM rooms WHERE id=?");
    $stmt->execute([$delId]);
    $old = $stmt->fetch();
    if ($old && $old['image'] && $old['image'] !== 'default_room.jpg') {
        @unlink('../uploads/rooms/' . $old['image']);
    }
    $pdo->prepare("DELETE FROM rooms WHERE id=?")->execute([$delId]);
    $msg = 'Room deleted successfully.';
}

// --- EDIT FETCH ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editRoom = $stmt->fetch();
}

// --- ADD / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel_id    = (int)($_POST['hotel_id']   ?? 0);
    $room_type   = trim($_POST['room_type']   ?? '');
    $price       = (float)($_POST['price']    ?? 0);
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status'] === 'Available' ? 'Available' : 'Unavailable';
    $id          = (int)($_POST['room_id']    ?? 0);

    if (!$hotel_id || !$room_type || $price <= 0) {
        $msg = 'Hotel, room type, and price are required.';
        $msgType = 'danger';
    } else {
        $imageName = $_POST['existing_image'] ?? 'default_room.jpg';
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $newName = 'room_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $uploadDir = '../uploads/rooms/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                    if ($imageName && $imageName !== 'default_room.jpg') {
                        @unlink($uploadDir . $imageName);
                    }
                    $imageName = $newName;
                } else {
                    $msg = 'Image upload failed.';
                    $msgType = 'danger';
                }
            } else {
                $msg = 'Invalid image format.';
                $msgType = 'danger';
            }
        }

        if (!$msg) {
            if ($id) {
                $pdo->prepare("UPDATE rooms SET hotel_id=?, room_type=?, price=?, description=?, image=?, status=? WHERE id=?")
                    ->execute([$hotel_id, $room_type, $price, $description, $imageName, $status, $id]);
                $msg = 'Room updated successfully!';
            } else {
                $pdo->prepare("INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES (?,?,?,?,?,?)")
                    ->execute([$hotel_id, $room_type, $price, $description, $imageName, $status]);
                $msg = 'Room added successfully!';
            }
            header('Location: rooms.php?msg=' . urlencode($msg) . ($filterHotel ? '&hotel_id='.$filterHotel : ''));
            exit();
        }
    }
}

if (isset($_GET['msg'])) { $msg = $_GET['msg']; }

// Fetch hotels for dropdown
$hotels = $pdo->query("SELECT id, hotel_name FROM hotels ORDER BY hotel_name")->fetchAll();

// Fetch rooms
if ($filterHotel) {
    $stmt = $pdo->prepare("SELECT r.*, h.hotel_name FROM rooms r JOIN hotels h ON r.hotel_id=h.id WHERE r.hotel_id=? ORDER BY h.hotel_name, r.price");
    $stmt->execute([$filterHotel]);
} else {
    $stmt = $pdo->query("SELECT r.*, h.hotel_name FROM rooms r JOIN hotels h ON r.hotel_id=h.id ORDER BY h.hotel_name, r.price");
}
$rooms = $stmt->fetchAll();
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?> alert-dismissible fade show auto-dismiss mb-4">
  <i class="fas fa-<?= $msgType==='success'?'check-circle':'exclamation-circle' ?> me-2"></i>
  <?= htmlspecialchars($msg) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

  <!-- ADD / EDIT FORM -->
  <div class="col-lg-4">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><i class="fas fa-<?= $editRoom ? 'edit' : 'plus-circle' ?> me-2 text-accent"></i>
          <?= $editRoom ? 'Edit Room' : 'Add New Room' ?>
        </h5>
        <?php if ($editRoom): ?>
        <a href="rooms.php" class="btn-sm-action btn-view">Cancel</a>
        <?php endif; ?>
      </div>
      <div class="admin-card-body">
        <form method="POST" action="rooms.php" enctype="multipart/form-data">
          <?php if ($editRoom): ?>
          <input type="hidden" name="room_id" value="<?= $editRoom['id'] ?>">
          <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editRoom['image']) ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Hotel *</label>
            <select class="form-select" name="hotel_id" required>
              <option value="">-- Select Hotel --</option>
              <?php foreach ($hotels as $h): ?>
              <option value="<?= $h['id'] ?>"
                <?= (($editRoom['hotel_id'] ?? $filterHotel) == $h['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($h['hotel_name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Room Type *</label>
            <input type="text" class="form-control" name="room_type" required
              value="<?= htmlspecialchars($editRoom['room_type'] ?? '') ?>"
              placeholder="e.g. Standard Room, Deluxe Room">
          </div>

          <div class="mb-3">
            <label class="form-label">Price per Night (₱) *</label>
            <input type="number" class="form-control" name="price" required
              min="1" step="0.01"
              value="<?= $editRoom['price'] ?? '' ?>"
              placeholder="e.g. 1500.00">
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"
              placeholder="Room description..."><?= htmlspecialchars($editRoom['description'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="Available"  <?= (($editRoom['status'] ?? 'Available') === 'Available')   ? 'selected' : '' ?>>Available</option>
              <option value="Unavailable" <?= (($editRoom['status'] ?? '') === 'Unavailable') ? 'selected' : '' ?>>Unavailable</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label">Room Image</label>
            <?php if ($editRoom && $editRoom['image'] && $editRoom['image'] !== 'default_room.jpg'): ?>
            <div class="img-preview-wrap mb-2">
              <img src="../uploads/rooms/<?= htmlspecialchars($editRoom['image']) ?>"
                id="roomImgPreview" style="max-height:100px;border-radius:5px;">
            </div>
            <?php else: ?>
            <div class="img-preview-wrap mb-2" style="display:none;" id="previewWrapper">
              <img id="roomImgPreview" style="max-height:100px;">
            </div>
            <?php endif; ?>
            <input type="file" class="form-control img-upload-input" name="image"
              accept="image/*" data-preview="roomImgPreview">
          </div>

          <button type="submit" class="btn-admin-accent w-100 justify-content-center py-2">
            <i class="fas fa-save me-2"></i><?= $editRoom ? 'Update Room' : 'Add Room' ?>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ROOMS TABLE -->
  <div class="col-lg-8">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><i class="fas fa-bed me-2 text-accent"></i>All Rooms (<?= count($rooms) ?>)</h5>
        <div style="display:flex;gap:8px;align-items:center;">
          <form method="GET" style="display:flex;gap:6px;">
            <select name="hotel_id" class="form-select form-select-sm" style="width:auto;">
              <option value="">All Hotels</option>
              <?php foreach ($hotels as $h): ?>
              <option value="<?= $h['id'] ?>" <?= $filterHotel == $h['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($h['hotel_name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
          </form>
        </div>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Img</th>
              <th>Hotel</th>
              <th>Room Type</th>
              <th>Price/Night</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rooms)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">No rooms found.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($rooms as $rm): ?>
            <tr>
              <td>
                <?php
                $rImg = '../uploads/rooms/' . $rm['image'];
                $showRImg = (file_exists($rImg) && $rm['image'] !== 'default_room.jpg');
                ?>
                <?php if ($showRImg): ?>
                  <img src="../uploads/rooms/<?= htmlspecialchars($rm['image']) ?>"
                    style="width:46px;height:34px;object-fit:cover;border-radius:4px;">
                <?php else: ?>
                  <div style="width:46px;height:34px;background:linear-gradient(135deg,#e2d9cc,#ccc5bb);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#999;">
                    <i class="fas fa-bed"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td style="font-size:0.85rem;"><?= htmlspecialchars($rm['hotel_name']) ?></td>
              <td style="font-weight:600;"><?= htmlspecialchars($rm['room_type']) ?></td>
              <td style="color:var(--accent);font-weight:700;">₱<?= number_format($rm['price'], 2) ?></td>
              <td>
                <span class="status-badge badge-<?= strtolower($rm['status']) ?>">
                  <?= $rm['status'] ?>
                </span>
              </td>
              <td>
                <div style="display:flex;gap:5px;">
                  <a href="rooms.php?edit=<?= $rm['id'] ?>" class="btn-sm-action btn-edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="rooms.php?delete=<?= $rm['id'] ?>"
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
    </div>
  </div>

</div>

<?php require_once 'admin_footer.php'; ?>
