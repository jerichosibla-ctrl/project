<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | PagadianStay</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-login-page">
  <div class="login-card">

    <div class="login-logo">
      <div class="icon"><i class="fas fa-hotel"></i></div>
      <h3 style="font-family:'Playfair Display',serif;color:var(--primary);margin:0;">PagadianStay</h3>
      <p style="color:var(--muted);font-size:0.9rem;margin:4px 0 0;">Admin Management Panel</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2">
      <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label class="form-label" for="username">
          <i class="fas fa-user me-1"></i>Username
        </label>
        <input type="text" class="form-control" id="username" name="username"
          placeholder="Enter username"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          autofocus required>
      </div>

      <div class="mb-4">
        <label class="form-label" for="password">
          <i class="fas fa-lock me-1"></i>Password
        </label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" name="password"
            placeholder="Enter password" required>
          <button type="button" class="btn btn-outline-secondary"
            onclick="togglePass()" id="toggleBtn">
            <i class="fas fa-eye" id="toggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-admin-primary w-100 justify-content-center py-3">
        <i class="fas fa-sign-in-alt me-2"></i>Login to Admin Panel
      </button>
    </form>

    <div class="text-center mt-4">
      <a href="../index.php" style="color:var(--muted);font-size:0.88rem;text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i>Back to Hotel Site
      </a>
    </div>
</div>

<script>
function togglePass() {
  const p = document.getElementById('password');
  const i = document.getElementById('toggleIcon');
  if (p.type === 'password') {
    p.type = 'text';
    i.className = 'fas fa-eye-slash';
  } else {
    p.type = 'password';
    i.className = 'fas fa-eye';
  }
}
</script>
</body>
</html>
