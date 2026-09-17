<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$role = strtolower($_SESSION['role'] ?? 'petugas');
$username = $_SESSION['username'] ?? 'User';
$page = basename($_SERVER['PHP_SELF']);

// Tentukan halaman dashboard sesuai role masing-masing
if ($role === 'admin') {
    $dashboard_link = 'admin.php';
} elseif ($role === 'user') {
    $dashboard_link = 'user.php'; // Sesuaikan dengan nama file halaman utama user Anda
} else {
    $dashboard_link = 'petugas.php';
}

$dashboard_active = in_array($page, ['index.php', 'admin.php', 'user.php', 'petugas.php']);
?>
<aside class="sidebar">
  <div class="brand">
    <div class="brand-logo">P</div>
    <div class="brand-title">Grand Pasar</div>
  </div>

  <div style="padding: 10px 15px; margin-bottom: 10px; background: rgba(212, 175, 55, 0.1); border-radius: 8px;">
    <small style="color: #94a3b8; display: block;">Login Sebagai:</small>
    <strong style="color: #f3e5ab; text-transform: uppercase; font-size: 0.85rem;"><?php echo htmlspecialchars($role); ?></strong>
  </div>

  <ul class="sidebar-menu">
    <!-- DASHBOARD -->
    <li class="menu-item <?php echo $dashboard_active ? 'active' : ''; ?>">
      <a href="<?php echo $dashboard_link; ?>"><span><i class="fa-solid fa-chart-pie"></i></span> <span>Dashboard</span></a>
    </li>

    <!-- OPERASIONAL (Admin & Petugas) -->
    <?php if ($role === 'admin' || $role === 'petugas'): ?>
    <li class="menu-item <?php echo ($page == 'parkir_masuk.php') ? 'active' : ''; ?>">
      <a href="parkir_masuk.php"><span><i class="fa-solid fa-right-to-bracket"></i></span> <span>Parkir Masuk</span></a>
    </li>
    <li class="menu-item <?php echo ($page == 'proses_keluar.php') ? 'active' : ''; ?>">
      <a href="proses_keluar.php"><span><i class="fa-solid fa-square-parking"></i></span> <span>Kendaraan Parkir</span></a>
    </li>
    <?php endif; ?>

    <!-- MENU KHUSUS USER (Jika ada halaman khusus user) -->
    <?php if ($role === 'user'): ?>
    <li class="menu-item <?php echo ($page == 'riwayat_parkir.php') ? 'active' : ''; ?>">
      <a href="riwayat_parkir.php"><span><i class="fa-solid fa-clock-rotate-left"></i></span> <span>Riwayat Parkir</span></a>
    </li>
    <?php endif; ?>

    <!-- MANAJEMEN TARIF (Khusus Admin) -->
    <?php if ($role === 'admin'): ?>
    <li class="menu-item <?php echo ($page == 'tarif_vip.php') ? 'active' : ''; ?>">
      <a href="tarif_vip.php"><span><i class="fa-solid fa-tags"></i></span> <span>Tarif & VIP</span></a>
    </li>
    <?php endif; ?>

    <!-- MANAJEMEN USER (Khusus Admin) -->
    <?php if ($role === 'admin'): ?>
    <li class="menu-item <?php echo ($page == 'kelola_user.php') ? 'active' : ''; ?>">
      <a href="kelola_user.php"><span><i class="fa-solid fa-users-gear"></i></span> <span>Manajemen User</span></a>
    </li>
    <?php endif; ?>

    <!-- LAPORAN KEUANGAN (Admin & Owner) -->
    <?php if ($role === 'admin' || $role === 'owner'): ?>
    <li class="menu-item <?php echo ($page == 'laporan.php') ? 'active' : ''; ?>">
      <a href="laporan.php"><span><i class="fa-solid fa-file-invoice-dollar"></i></span> <span>Laporan Keuangan</span></a>
    </li>
    <?php endif; ?>

    <!-- LOGOUT -->
    <li class="menu-item" style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
      <a href="logout.php" style="color: #ef4444;"><span><i class="fa-solid fa-right-from-bracket"></i></span> <span>Logout</span></a>
    </li>
  </ul>
</aside>