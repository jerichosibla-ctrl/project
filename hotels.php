<?php
$pageTitle = 'Manage Hotels';
require_once 'admin_header.php';
require_once '../includes/db.php';

$msg = '';
$msgType = 'success';
$editHotel = null;

// --- DELETE ---
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM hotels WHERE id=?");
    $stmt->execute([$delId]);
    $old = $stmt->fetch();
    if ($old && $old['image'] && $old['image'] !== 'default_hotel.jpg') {
        @unlink('../uploads/hotels/' . $old['image']);
    }
    $pdo->prepare("DELETE FROM hotels WHERE id=?")->execute([$delId]);
    $msg = 'Hotel deleted successfully.';
}

// --- EDIT FETCH ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $editHotel = $stmt->fetch();
}

// --- ADD / UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotel_name  = trim($_POST['hotel_name']  ?? '');
    $description = trim($_POST['description'] ?? '');
    $location    = trim($_POST['location']    ?? '');
    $id          = (int)($_POST['hotel_id']   ?? 0);

    if (!$hotel_name || !$location) {
        $msg = 'Hotel name and location are required.';
        $msgType = 'danger';
    } else {
        // Handle image upload
        $imageName = $_POST['existing_image'] ?? 'default_hotel.jpg';
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $newName = 'hotel_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $uploadDir = '../uploads/hotels/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newName)) {
                    // Delete old image
                    if ($imageName && $imageName !== 'default_hotel.jpg') {
                        @unlink($uploadDir . $imageName);
                    }
                    $imageName = $newName;
                } else {
                    $msg = 'Image upload failed.';
                    $msgType = 'danger';
                }
            } else {
                $msg = 'Invalid image format. Use JPG, PNG, GIF, or WebP.';
                $msgType = 'danger';
            }
        }

        if (!$msg) {
            if ($id) {
                $pdo->prepare("UPDATE hotels SET hotel_name=?, description=?, location=?, image=? WHERE id=?")
                    ->execute([$hotel_name, $description, $location, $imageName, $id]);
                $msg = 'Hotel updated successfully!';
            } else {
                $pdo->prepare("INSERT INTO hotels (hotel_name, description, location, image) VALUES (?,?,?,?)")
                    ->execute([$hotel_name, $description, $location, $imageName]);
                $msg = 'Hotel added successfully!';
            }
            header('Location: hotels.php?msg=' . urlencode($msg));
            exit();
        }
    }
}

if (isset($_GET['msg'])) { $msg = $_GET['msg']; }

// Fetch all hotels
$hotels = $pdo->query("SELECT h.*, COUNT(r.id) as room_count FROM hotels h LEFT JOIN rooms r ON h.id=r.hotel_id GROUP BY h.id ORDER BY h.hotel_name")->fetchAll();
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
        <h5><i class="fas fa-<?= $editHotel ? 'edit' : 'plus-circle' ?> me-2 text-accent"></i>
          <?= $editHotel ? 'Edit Hotel' : 'Add New Hotel' ?>
        </h5>
        <?php if ($editHotel): ?>
        <a href="hotels.php" class="btn-sm-action btn-view">Cancel</a>
        <?php endif; ?>
      </div>
      <div class="admin-card-body">
        <form method="POST" action="hotels.php" enctype="multipart/form-data">
          <?php if ($editHotel): ?>
          <input type="hidden" name="hotel_id" value="<?= $editHotel['id'] ?>">
          <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editHotel['image']) ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Hotel Name *</label>
            <input type="text" class="form-control" name="hotel_name" required
              value="<?= htmlspecialchars($editHotel['hotel_name'] ?? '') ?>"
              placeholder="e.g. Citi Hotel Uno">
          </div>

          <div class="mb-3">
            <label class="form-label">Location *</label>
            <input type="text" class="form-control" name="location" required
              value="<?= htmlspecialchars($editHotel['location'] ?? '') ?>"
              placeholder="e.g. Rizal Ave, Pagadian City">
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4"
              placeholder="Short description of the hotel..."><?= htmlspecialchars($editHotel['description'] ?? '') ?></textarea>
          </div>

          <div class="mb-4">
            <label class="form-label">Hotel Image</label>
            <?php if ($editHotel && $editHotel['image'] && $editHotel['image'] !== 'default_hotel.jpg'): ?>
            <div class="img-preview-wrap mb-2">
              <img src="../uploads/hotels/<?= htmlspecialchars($editHotel['image']) ?>"
                   id="hotelImgPreview" style="max-height:120px;border-radius:6px;">
            </div>
            <?php else: ?>
            <div class="img-preview-wrap mb-2" id="previewWrapper" style="display:none;">
              <img id="hotelImgPreview" style="max-height:120px;">
            </div>
            <?php endif; ?>
            <input type="file" class="form-control img-upload-input" name="image"
              accept="image/*" data-preview="hotelImgPreview">
            <small class="text-muted">JPG, PNG, GIF, or WebP. Max 2MB.</small>
          </div>

          <button type="submit" class="btn-admin-accent w-100 justify-content-center py-2">
            <i class="fas fa-save me-2"></i><?= $editHotel ? 'Update Hotel' : 'Add Hotel' ?>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- HOTELS TABLE -->
  <div class="col-lg-8">
    <div class="admin-card">
      <div class="admin-card-header">
        <h5><i class="fas fa-hotel me-2 text-accent"></i>All Hotels (<?= count($hotels) ?>)</h5>
      </div>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Hotel Name</th>
              <th>Location</th>
              <th>Rooms</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($hotels)): ?>
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">No hotels found. Add your first hotel!</td>
            </tr>
            <?php else: ?>
            <?php foreach ($hotels as $h): ?>
            <tr>
              <td>
                <?php
                $imgPath = '../uploads/hotels/' . $h['image'];
                $showImg = (file_exists($imgPath) && $h['image'] !== 'default_hotel.jpg');
                ?>
                <?php if ($showImg): ?>
                  <img src="../uploads/hotels/<?= htmlspecialchars($h['image']) ?>"
                    style="width:50px;height:38px;object-fit:cover;border-radius:5px;">
                <?php else: ?>
                  <div style="width:50px;height:38px;background:linear-gradient(135deg,#e2d9cc,#ccc5bb);border-radius:5px;display:flex;align-items:center;justify-content:center;color:#999;font-size:1.2rem;">
                    <i class="fas fa-hotel"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($h['hotel_name']) ?></div>
                <small style="color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;max-width:200px;">
                  <?= htmlspecialchars($h['description']) ?>
                </small>
              </td>
              <td style="font-size:0.85rem;color:var(--muted);"><?= htmlspecialchars($h['location']) ?></td>
              <td>
                <a href="rooms.php?hotel_id=<?= $h['id'] ?>" class="btn-sm-action btn-view">
                  <?= $h['room_count'] ?> rooms
                </a>
              </td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="hotels.php?edit=<?= $h['id'] ?>" class="btn-sm-action btn-edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="hotels.php?delete=<?= $h['id'] ?>"
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
