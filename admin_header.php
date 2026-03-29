<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/auth.php';
requireAdmin();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Admin - PagadianStay</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-wrapper">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
      <h5><i class="fas fa-hotel me-2"></i>PagadianStay</h5>
      <small>Admin Control Panel</small>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-title">Main</div>
      <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
      </a>

      <div class="nav-section-title">Manage</div>
      <a href="hotels.php" class="<?= $currentPage === 'hotels.php' ? 'active' : '' ?>">
        <i class="fas fa-hotel"></i> Hotels
      </a>
      <a href="rooms.php" class="<?= $currentPage === 'rooms.php' ? 'active' : '' ?>">
        <i class="fas fa-bed"></i> Rooms
      </a>
      <a href="reservations.php" class="<?= $currentPage === 'reservations.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-check"></i> Reservations
      </a>

      <div class="nav-section-title">System</div>
      <a href="../index.php" target="_blank">
        <i class="fas fa-external-link-alt"></i> View Hotel Site
      </a>
      <a href="logout.php" style="color:rgba(255,80,80,0.7);">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </nav>

    <div class="sidebar-footer">
      <small style="color:rgba(255,255,255,0.35);">Logged in as <strong style="color:rgba(255,255,255,0.6)"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong></small>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="admin-content">
    <header class="admin-topbar">
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
        </button>
        <h1 class="topbar-title"><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></h1>
      </div>
      <div class="topbar-user">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></div>
        <span><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        <a href="logout.php" class="btn btn-sm btn-outline-danger ms-2">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </header>

    <div class="admin-body">
