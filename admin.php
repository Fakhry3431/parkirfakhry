<?php
/*
  admin.php - Dashboard Panel Administrator E-Parkir
*/
session_start();
require_once "koneksi.php";

// Keamanan: Cek apakah user sudah login dan merupakan Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit();
}

$namaLokasi = "Grand Pasar";

// ============================================================================
// DATA STATISTIK DASHBOARD
// ============================================================================

// 1. Kendaraan Terparkir (Masuk & Belum Keluar)
$qTerparkir = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_kendaraan WHERE waktu_keluar IS NULL");
$rowTerparkir = mysqli_fetch_assoc($qTerparkir);
$totalTerparkir = $rowTerparkir['total'] ?? 0;

// 2. Total Pendapatan (Aman dari error jika kolom 'biaya' tidak ada)
$totalPendapatan = 0;
$checkBiaya = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_kendaraan LIKE 'biaya'");
if ($checkBiaya && mysqli_num_rows($checkBiaya) > 0) {
    $qPendapatan = mysqli_query($koneksi, "SELECT SUM(biaya) AS total FROM tb_kendaraan WHERE waktu_keluar IS NOT NULL");
    if ($qPendapatan) {
        $rowPendapatan = mysqli_fetch_assoc($qPendapatan);
        $totalPendapatan = $rowPendapatan['total'] ?? 0;
    }
}

// 3. Total Transaksi (Keseluruhan Kendaraan Masuk)
$qTransaksi = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_kendaraan");
$rowTransaksi = mysqli_fetch_assoc($qTransaksi);
$totalTransaksi = $rowTransaksi['total'] ?? 0;

// 4. Pengguna Terdaftar
$qUser = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_user");
$rowUser = mysqli_fetch_assoc($qUser);
$totalUser = $rowUser['total'] ?? 0;

// 5. Data Grafik Persentase Jenis Kendaraan (Aman dari Unknown Column)
$rMotor = 0;
$rMobil = 0;

// Cek keberadaan kolom secara dinamis
$hasIdTarif = false;
$hasJenis   = false;
$hasJenisK  = false;

$resIdTarif = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_kendaraan LIKE 'id_tarif'");
if ($resIdTarif && mysqli_num_rows($resIdTarif) > 0) { $hasIdTarif = true; }

$resJenis = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_kendaraan LIKE 'jenis'");
if ($resJenis && mysqli_num_rows($resJenis) > 0) { $hasJenis = true; }

$resJenisK = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_kendaraan LIKE 'jenis_kendaraan'");
if ($resJenisK && mysqli_num_rows($resJenisK) > 0) { $hasJenisK = true; }

if ($hasIdTarif) {
    // Jika menggunakan relasi id_tarif ke tb_tarif
    $qMotor = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_kendaraan k JOIN tb_tarif t ON k.id_tarif = t.id_tarif WHERE LOWER(t.jenis_kendaraan) LIKE '%motor%'");
    if ($qMotor) { $rMotor = mysqli_fetch_assoc($qMotor)['total'] ?? 0; }

    $qMobil = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_kendaraan k JOIN tb_tarif t ON k.id_tarif = t.id_tarif WHERE LOWER(t.jenis_kendaraan) LIKE '%mobil%'");
    if ($qMobil) { $rMobil = mysqli_fetch_assoc($qMobil)['total'] ?? 0; }
} else if ($hasJenis || $hasJenisK) {
    // Jika menggunakan kolom jenis atau jenis_kendaraan langsung di tb_kendaraan
    $col = $hasJenis ? "jenis" : "jenis_kendaraan";
    
    $qMotor = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_kendaraan WHERE LOWER($col) LIKE '%motor%'");
    if ($qMotor) { $rMotor = mysqli_fetch_assoc($qMotor)['total'] ?? 0; }

    $qMobil = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_kendaraan WHERE LOWER($col) LIKE '%mobil%'");
    if ($qMobil) { $rMobil = mysqli_fetch_assoc($qMobil)['total'] ?? 0; }
}

// 6. Data Grafik Status Parkir
$qSelesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tb_kendaraan WHERE waktu_keluar IS NOT NULL");
$rSelesai = mysqli_fetch_assoc($qSelesai)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - <?php echo htmlspecialchars($namaLokasi); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-main: #0b1120;
      --bg-sidebar: #060911;
      --bg-card: rgba(15, 23, 42, 0.75);
      --border-gold: rgba(212, 175, 55, 0.25);
      --gold-grad: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%);
      --text-gold: #f3e5ab;
      --text-sub: #94a3b8;
    }

    body {
      background-color: var(--bg-main);
      color: #ffffff;
      font-family: 'Plus Jakarta Sans', sans-serif;
      display: flex;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* SIDEBAR */
    .sidebar {
      width: 260px;
      background-color: var(--bg-sidebar);
      border-right: 1px solid var(--border-gold);
      padding: 24px 16px;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
    }

    .brand-box {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
      padding: 0 8px;
    }

    .brand-icon {
      width: 40px; height: 40px;
      background: var(--gold-grad);
      color: #000; font-weight: 800; font-size: 1.2rem;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
    }

    .brand-name {
      font-size: 1.1rem; font-weight: 800; letter-spacing: 1px;
      background: var(--gold-grad);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .user-badge-box {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 24px;
    }

    .user-badge-title { font-size: 0.75rem; color: var(--text-sub); }
    .user-badge-role { font-weight: 800; color: #ffffff; font-size: 0.9rem; text-transform: uppercase; }

    .nav-menu { display: flex; flex-direction: column; gap: 8px; }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      color: var(--text-sub);
      text-decoration: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 0.88rem;
      transition: all 0.2s;
    }

    .nav-item:hover, .nav-item.active {
      background: rgba(212, 175, 55, 0.1);
      color: var(--text-gold);
      border: 1px solid var(--border-gold);
    }

    /* MAIN CONTENT */
    .main-content {
      flex: 1;
      padding: 32px;
      overflow-y: auto;
    }

    .top-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 28px;
    }

    .header-title h2 {
      font-size: 1.8rem;
      font-weight: 800;
      letter-spacing: 1px;
      background: var(--gold-grad);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .header-title p {
      color: var(--text-sub);
      font-size: 0.9rem;
      margin-top: 4px;
    }

    /* BUTTON LOGOUT */
    .btn-logout {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: #ffffff;
      padding: 10px 20px;
      border-radius: 30px;
      font-size: 0.85rem;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
      transition: all 0.2s ease-in-out;
    }

    .btn-logout:hover {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(239, 68, 68, 0.5);
    }

    /* STATS CARDS */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 32px;
    }

    .stat-card {
      background: var(--bg-card);
      border-radius: 14px;
      padding: 20px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; width: 4px; height: 100%;
    }

    .stat-card.blue::before { background-color: #3b82f6; }
    .stat-card.green::before { background-color: #10b981; }
    .stat-card.gold::before { background-color: #d4af37; }
    .stat-card.cyan::before { background-color: #06b6d4; }

    .stat-label { font-size: 0.8rem; color: var(--text-sub); margin-bottom: 8px; }
    .stat-value { font-size: 1.8rem; font-weight: 800; }

    .stat-card.blue .stat-value { color: #ffffff; }
    .stat-card.green .stat-value { color: #10b981; }
    .stat-card.gold .stat-value { color: #ffffff; }
    .stat-card.cyan .stat-value { color: #ffffff; }

    /* CHARTS GRID */
    .charts-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }

    @media (max-width: 992px) {
      .charts-grid { grid-template-columns: 1fr; }
      body { flex-direction: column; }
      .sidebar { width: 100%; }
    }

    .chart-card {
      background: var(--bg-card);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      padding: 20px;
    }

    .chart-title {
      font-size: 1rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .chart-container {
      position: relative;
      height: 240px;
      width: 100%;
    }
  </style>
</head>
<body>

  <!-- SIDEBAR NAVIGASI -->
  <aside class="sidebar">
    <div class="brand-box">
      <div class="brand-icon">P</div>
      <div class="brand-name">GRAND PASAR</div>
    </div>

    <div class="user-badge-box">
      <div class="user-badge-title">Login Sebagai:</div>
      <div class="user-badge-role"><?php echo htmlspecialchars($_SESSION['username'] ?? 'ADMIN'); ?></div>
    </div>

    <nav class="nav-menu">
      <a href="admin.php" class="nav-item active">📊 Dashboard</a>
      <a href="parkir_masuk.php" class="nav-item">🚗 Parkir Masuk</a>
      <a href="kendaraan_parkir.php" class="nav-item">📋 Kendaraan Parkir</a>
      <a href="tarif.php" class="nav-item">💎 Tarif & VIP</a>
      <a href="user_management.php" class="nav-item">👥 Manajemen User</a>
      <a href="laporan.php" class="nav-item">📈 Laporan Keuangan</a>
    </nav>
  </aside>

  <!-- KONTEN UTAMA -->
  <main class="main-content">

    <!-- HEADER & TOMBOL LOGOUT POJOK KANAN ATAS -->
    <header class="top-header">
      <div class="header-title">
        <h2>PANEL ADMINISTRATOR</h2>
        <p>Selamat Datang, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></strong></p>
      </div>

      <a href="logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
        🚪 Logout
      </a>
    </header>

    <!-- CARD STATISTIK -->
    <section class="stats-grid">
      <div class="stat-card blue">
        <div class="stat-label">Kendaraan Terparkir</div>
        <div class="stat-value"><?php echo number_format($totalTerparkir); ?></div>
      </div>

      <div class="stat-card green">
        <div class="stat-label">Total Pendapatan</div>
        <div class="stat-value">Rp <?php echo number_format($totalPendapatan, 0, ',', '.'); ?></div>
      </div>

      <div class="stat-card gold">
        <div class="stat-label">Total Transaksi</div>
        <div class="stat-value"><?php echo number_format($totalTransaksi); ?></div>
      </div>

      <div class="stat-card cyan">
        <div class="stat-label">Pengguna Terdaftar</div>
        <div class="stat-value"><?php echo number_format($totalUser); ?></div>
      </div>
    </section>

    <!-- GRAFIK STATISTIK -->
    <section class="charts-grid">
      <div class="chart-card">
        <div class="chart-title">📊 Persentase Jenis Kendaraan</div>
        <div class="chart-container">
          <canvas id="chartJenisKendaraan"></canvas>
        </div>
      </div>

      <div class="chart-card">
        <div class="chart-title">🚦 Status Parkir (Masuk / Keluar)</div>
        <div class="chart-container">
          <canvas id="chartStatusParkir"></canvas>
        </div>
      </div>
    </section>

  </main>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // 1. Chart Jenis Kendaraan
      const ctxJenis = document.getElementById('chartJenisKendaraan').getContext('2d');
      new Chart(ctxJenis, {
        type: 'pie',
        data: {
          labels: ['Motor', 'Mobil'],
          datasets: [{
            data: [<?php echo $rMotor; ?>, <?php echo $rMobil; ?>],
            backgroundColor: ['#f59e0b', '#3b82f6'],
            borderColor: '#0b1120',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { color: '#94a3b8' } }
          }
        }
      });

      // 2. Chart Status Parkir
      const ctxStatus = document.getElementById('chartStatusParkir').getContext('2d');
      new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
          labels: ['Masuk / Terparkir', 'Selesai / Keluar'],
          datasets: [{
            data: [<?php echo $totalTerparkir; ?>, <?php echo $rSelesai; ?>],
            backgroundColor: ['#10b981', '#64748b'],
            borderColor: '#0b1120',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { color: '#94a3b8' } }
          },
          cutout: '65%'
        }
      });
    });
  </script>

</body>
</html>