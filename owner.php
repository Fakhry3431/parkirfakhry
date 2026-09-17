<?php
/*
  owner.php - Dashboard Eksekutif Owner dengan Sidebar Menu
*/
session_start();
require_once "koneksi.php";

// 1. Validasi Akses: Hanya role 'owner' yang boleh masuk
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'owner') {
    header("Location: index.php");
    exit();
}

$namaLokasi = "Coffe Sedayu Parking System";
$namaOwner = $_SESSION['username'] ?? 'Owner';

// 2. Deteksi otomatis kolom angka pertama dari tb_kendaraan (Anti-Error)
$kolomBiaya = '';
$resultCols = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_kendaraan");
if ($resultCols) {
    while ($col = mysqli_fetch_assoc($resultCols)) {
        $namaF = strtolower($col['Field']);
        $tipeF = strtolower($col['Type']);
        if (strpos($namaF, 'biaya') !== false || strpos($namaF, 'tarif') !== false || strpos($namaF, 'harga') !== false || strpos($namaF, 'total') !== false || strpos($namaF, 'bayar') !== false || strpos($namaF, 'jumlah') !== false) {
            $kolomBiaya = $col['Field'];
            break;
        }
    }
    if (empty($kolomBiaya)) {
        mysqli_data_seek($resultCols, 0);
        while ($col = mysqli_fetch_assoc($resultCols)) {
            $tipeF = strtolower($col['Type']);
            if (strpos($tipeF, 'int') !== false || strpos($tipeF, 'decimal') !== false || strpos($tipeF, 'float') !== false || strpos($tipeF, 'double') !== false) {
                if ($col['Key'] != 'PRI') {
                    $kolomBiaya = $col['Field'];
                    break;
                }
            }
        }
    }
}
if (empty($kolomBiaya)) { $kolomBiaya = 'id_kendaraan'; }
$selectBiayaSql = "`$kolomBiaya`";

// 3. Filter Tanggal untuk Laporan
$tglMulai = $_GET['tgl_mulai'] ?? '';
$tglSelesai = $_GET['tgl_selesai'] ?? '';
$whereFilter = "WHERE waktu_keluar IS NOT NULL";
if (!empty($tglMulai) && !empty($tglSelesai)) {
    $whereFilter .= " AND DATE(waktu_keluar) BETWEEN '" . mysqli_real_escape_string($koneksi, $tglMulai) . "' AND '" . mysqli_real_escape_string($koneksi, $tglSelesai) . "'";
}

// 4. Ambil Data Statistik Keuangan
$qTotal = mysqli_query($koneksi, "SELECT SUM($selectBiayaSql) AS total_pendapatan, COUNT(*) AS total_transaksi FROM tb_kendaraan $whereFilter");
$dTotal = $qTotal ? mysqli_fetch_assoc($qTotal) : [];
$totalPendapatan = $dTotal['total_pendapatan'] ?? 0;
$totalTransaksi = $dTotal['total_transaksi'] ?? 0;

$qHariIni = mysqli_query($koneksi, "SELECT SUM($selectBiayaSql) AS pend_hari_ini FROM tb_kendaraan WHERE DATE(waktu_keluar) = CURDATE()");
$dHariIni = $qHariIni ? mysqli_fetch_assoc($qHariIni) : [];
$pendapatanHariIni = $dHariIni['pend_hari_ini'] ?? 0;

$qJenis = mysqli_query($koneksi, "SELECT jenis_kendaraan, COUNT(*) as jml, SUM($selectBiayaSql) as total FROM tb_kendaraan $whereFilter GROUP BY jenis_kendaraan");
$qRiwayat = mysqli_query($koneksi, "SELECT k.*, a.nama_area FROM tb_kendaraan k LEFT JOIN tb_area_parkir a ON k.id_area = a.id_area $whereFilter ORDER BY k.waktu_keluar DESC LIMIT 15");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Owner - Coffe Sedayu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-main: #060911;
      --bg-sidebar: #0b0f19;
      --bg-card: rgba(15, 23, 42, 0.8);
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
    }

    /* Sidebar Styling */
    sidebar {
      width: 280px;
      background-color: var(--bg-sidebar);
      border-right: 1px solid var(--border-gold);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; bottom: 0; left: 0;
      z-index: 1000;
      overflow-y: auto;
    }

    .sidebar-header {
      padding: 24px 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .brand-icon {
      width: 42px; height: 42px;
      background: var(--gold-grad);
      color: #000; font-weight: 800; font-size: 1.2rem;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
    }

    .brand-title h2 { font-size: 0.95rem; font-weight: 800; letter-spacing: 0.5px; background: var(--gold-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .brand-title span { font-size: 0.7rem; color: var(--text-sub); text-transform: uppercase; letter-spacing: 1px; }

    .sidebar-menu { padding: 20px 14px; flex: 1; }
    .menu-category { font-size: 0.7rem; text-transform: uppercase; color: var(--text-sub); letter-spacing: 1px; margin: 15px 10px 8px; font-weight: 700; }

    .menu-item {
      display: flex; align-items: center; gap: 12px;
      padding: 10px 14px; color: #cbd5e1; text-decoration: none;
      border-radius: 10px; font-size: 0.85rem; font-weight: 600;
      transition: all 0.2s; margin-bottom: 4px;
    }
    .menu-item:hover, .menu-item.active {
      background: rgba(212, 175, 55, 0.12);
      color: var(--text-gold);
    }
    .menu-item i { width: 20px; text-align: center; color: var(--text-gold); }

    .sidebar-footer {
      padding: 16px 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
      background: rgba(0, 0, 0, 0.2);
    }

    .user-profile { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--gold-grad); color: #000; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .user-info h4 { font-size: 0.85rem; font-weight: 700; }
    .user-info span { font-size: 0.65rem; color: var(--text-gold); font-weight: 800; letter-spacing: 0.5px; }

    .btn-logout {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      width: 100%; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444;
      color: #ef4444; padding: 8px; border-radius: 8px; font-weight: 700; font-size: 0.8rem;
      text-decoration: none; transition: all 0.2s;
    }
    .btn-logout:hover { background: #ef4444; color: #fff; }

    /* Main Content Area */
    .main-content {
      margin-left: 280px;
      flex: 1;
      padding: 30px;
    }

    .top-bar {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 25px; background: var(--bg-card);
      border: 1px solid var(--border-gold); border-radius: 14px; padding: 20px 25px;
      backdrop-filter: blur(10px);
    }
    .top-bar h1 { font-size: 1.4rem; font-weight: 800; }
    .top-bar h1 span { background: var(--gold-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .top-bar p { color: var(--text-sub); font-size: 0.85rem; }

    .btn-print {
      background: var(--gold-grad); color: #000; border: none; font-weight: 700;
      padding: 9px 18px; border-radius: 8px; cursor: pointer; font-size: 0.85rem;
      display: flex; align-items: center; gap: 8px; text-decoration: none;
    }

    /* Filter Tanggal */
    .filter-box {
      background: var(--bg-card); border: 1px solid var(--border-gold);
      border-radius: 14px; padding: 16px 20px; margin-bottom: 25px;
      display: flex; gap: 15px; align-items: center; flex-wrap: wrap;
    }
    .filter-box label { font-size: 0.85rem; color: var(--text-sub); font-weight: 600; }
    .filter-box input {
      background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15);
      color: #fff; padding: 6px 12px; border-radius: 8px; font-size: 0.85rem;
    }
    .filter-box button {
      background: var(--gold-grad); color: #000; border: none; font-weight: 700;
      padding: 7px 16px; border-radius: 8px; cursor: pointer; font-size: 0.85rem;
    }

    .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 25px; }
    .glass-card { background: var(--bg-card); border: 1px solid var(--border-gold); border-radius: 14px; padding: 20px; backdrop-filter: blur(10px); }
    .card-title { color: var(--text-sub); font-size: 0.85rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .card-value { font-size: 1.7rem; font-weight: 800; margin-top: 8px; background: var(--gold-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .grid-section { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; }
    @media(max-width: 1024px) { .grid-section { grid-template-columns: 1fr; } sidebar { width: 70px; } sidebar .brand-title, sidebar .menu-category, sidebar .menu-item span, sidebar .user-info { display: none; } .main-content { margin-left: 70px; } }

    .sec-head { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
    .sec-bar { width: 4px; height: 18px; background: var(--gold-grad); border-radius: 2px; }
    .sec-title { font-size: 1.05rem; font-weight: 700; color: var(--text-gold); }

    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem; }
    th { background: rgba(212, 175, 55, 0.1); color: var(--text-gold); padding: 12px; border-bottom: 1px solid var(--border-gold); font-weight: 700; }
    td { padding: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); color: #cbd5e1; }
    tr:hover td { background: rgba(255, 255, 255, 0.02); }

    .badge-kendaraan { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }

    footer { text-align: center; padding: 20px; color: var(--text-sub); font-size: 0.8rem; border-top: 1px solid rgba(255, 255, 255, 0.05); margin-top: 40px; }
  </style>
</head>
<body>

  <!-- Sidebar Menu Lengkap -->
  <sidebar>
    <div class="sidebar-header">
      <div class="brand-icon"><i class="fa-solid fa-mug-hot"></i></div>
      <div class="brand-title">
        <h2>COFFE SEDAYU</h2>
        <span>Parking System</span>
      </div>
    </div>

    <div class="sidebar-menu">
      <div class="menu-category">Menu Utama</div>
      <a href="owner.php" class="menu-item active"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a>
      <a href="rekap.php" class="menu-item"><i class="fa-solid fa-file-invoice-dollar"></i> <span>Rekap Transaksi</span></a>

      <div class="menu-category">Pengaturan & CRUD</div>
      <a href="tarif.php" class="menu-item"><i class="fa-solid fa-tags"></i> <span>CRUD Tarif Parkir</span></a>
      <a href="kendaraan.php" class="menu-item"><i class="fa-solid fa-car"></i> <span>CRUD Kendaraan</span></a>
      <a href="area.php" class="menu-item"><i class="fa-solid fa-square-parking"></i> <span>CRUD Area Parkir</span></a>
      <a href="log_aktivitas.php" class="menu-item"><i class="fa-solid fa-clock-rotate-left"></i> <span>Log Aktivitas</span></a>
      <a href="performa_petugas.php" class="menu-item"><i class="fa-solid fa-star-half-stroke"></i> <span>Performa Petugas</span></a>
      <a href="staff.php" class="menu-item"><i class="fa-solid fa-users-gear"></i> <span>Daftar Staff</span></a>

      <div class="menu-category">Sistem</div>
      <a href="#" class="menu-item"><i class="fa-solid fa-circle-question"></i> <span>Bantuan</span></a>
      <a href="#" class="menu-item"><i class="fa-solid fa-user-gear"></i> <span>Profil Saya</span></a>
    </div>

    <div class="sidebar-footer">
      <div class="user-profile">
        <div class="user-avatar"><?php echo strtoupper(substr($namaOwner, 0, 1)); ?></div>
        <div class="user-info">
          <h4><?php echo htmlspecialchars($namaOwner); ?></h4>
          <span>OWNER</span>
        </div>
      </div>
      <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </sidebar>

  <!-- Main Content -->
  <div class="main-content">
    
    <div class="top-bar">
      <div>
        <h1>Dashboard <span>Owner</span></h1>
        <p>Ringkasan analitik dan laporan keuangan real-time sistem parkir <?php echo htmlspecialchars($namaLokasi); ?>.</p>
      </div>
      <div>
        <button onclick="window.print()" class="btn-print"><i class="fa-solid fa-print"></i> Cetak Laporan</button>
      </div>
    </div>

    <!-- Filter Tanggal -->
    <form method="GET" class="filter-box">
      <div><i class="fa-solid fa-filter" style="color: var(--text-gold); margin-right: 6px;"></i> <label>Filter Tanggal:</label></div>
      <div><input type="date" name="tgl_mulai" value="<?php echo htmlspecialchars($tglMulai); ?>"></div>
      <div><label>s/d</label> <input type="date" name="tgl_selesai" value="<?php echo htmlspecialchars($tglSelesai); ?>"></div>
      <div>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Terapkan</button>
        <?php if (!empty($tglMulai)): ?>
          <a href="owner.php" style="color: #f87171; font-size: 0.85rem; text-decoration: none; margin-left: 10px; font-weight: 600;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Kartu Statistik -->
    <div class="card-grid">
      <div class="glass-card">
        <div class="card-title">Total Pendapatan (Filtered) <i class="fa-solid fa-wallet" style="color: var(--text-gold);"></i></div>
        <div class="card-value">Rp <?php echo number_format($totalPendapatan, 0, ",", "."); ?></div>
      </div>
      <div class="glass-card">
        <div class="card-title">Pendapatan Hari Ini <i class="fa-solid fa-chart-line" style="color: #10b981;"></i></div>
        <div class="card-value">Rp <?php echo number_format($pendapatanHariIni, 0, ",", "."); ?></div>
      </div>
      <div class="glass-card">
        <div class="card-title">Total Transaksi Keluar <i class="fa-solid fa-car" style="color: #38bdf8;"></i></div>
        <div class="card-value" style="color: #38bdf8; background: none; -webkit-text-fill-color: #38bdf8;"><?php echo number_format($totalTransaksi, 0, ",", "."); ?> <span style="font-size: 1rem; color: var(--text-sub);">Kendaraan</span></div>
      </div>
    </div>

    <!-- Tabel Grid Section -->
    <div class="grid-section">
      
      <!-- Tabel Riwayat Transaksi -->
      <div>
        <div class="sec-head">
          <div class="sec-bar"></div>
          <div class="sec-title">Riwayat 15 Transaksi Pendapatan Terbaru</div>
        </div>
        
        <div class="glass-card" style="padding: 10px;">
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>No</th>
                  <th>Plat Nomor</th>
                  <th>Jenis</th>
                  <th>Area Parkir</th>
                  <th>Waktu Keluar</th>
                  <th>Biaya</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($qRiwayat && mysqli_num_rows($qRiwayat) > 0): ?>
                  <?php $no = 1; while ($r = mysqli_fetch_assoc($qRiwayat)): ?>
                    <tr>
                      <td><?php echo $no++; ?></td>
                      <td><strong><?php echo htmlspecialchars($r['plat_nomor']); ?></strong></td>
                      <td><span class="badge-kendaraan"><?php echo htmlspecialchars($r['jenis_kendaraan']); ?></span></td>
                      <td><?php echo htmlspecialchars($r['nama_area'] ?? 'Area Umum'); ?></td>
                      <td style="font-size: 0.8rem; color: var(--text-sub);"><?php echo htmlspecialchars($r['waktu_keluar']); ?></td>
                      <td style="color: #10b981; font-weight: 700;">Rp <?php echo number_format($r[$kolomBiaya] ?? 0, 0, ",", "."); ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-sub); padding: 25px;">Belum ada data transaksi kendaraan keluar.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Statistik Per Jenis Kendaraan -->
      <div>
        <div class="sec-head">
          <div class="sec-bar"></div>
          <div class="sec-title">Statistik Jenis Kendaraan</div>
        </div>
        
        <div class="glass-card">
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Kendaraan</th>
                  <th style="text-align: center;">Jml</th>
                  <th style="text-align: right;">Total Pendapatan</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($qJenis && mysqli_num_rows($qJenis) > 0): ?>
                  <?php while ($j = mysqli_fetch_assoc($qJenis)): ?>
                    <tr>
                      <td><span class="badge-kendaraan"><?php echo htmlspecialchars($j['jenis_kendaraan']); ?></span></td>
                      <td style="text-align: center; font-weight: 700;"><?php echo number_format($j['jml'], 0, ",", "."); ?></td>
                      <td style="text-align: right; color: #10b981; font-weight: 700;">Rp <?php echo number_format($j['total'] ?? 0, 0, ",", "."); ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" style="text-align: center; color: var(--text-sub); padding: 20px;">Belum ada data statistik.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <footer>
      &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($namaLokasi); ?>. Executive Financial Dashboard.
    </footer>

  </div>

</body>
</html>