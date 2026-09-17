<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'owner') {
    header("Location: index.php");
    exit();
}

$namaLokasi = "Coffe Sedayu Parking System";
$namaOwner = $_SESSION['username'] ?? 'Owner';

// Deteksi otomatis kolom biaya
$kolomBiaya = 'id_kendaraan';
$resultCols = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_kendaraan");
if ($resultCols) {
    while ($col = mysqli_fetch_assoc($resultCols)) {
        $namaF = strtolower($col['Field']);
        if (strpos($namaF, 'biaya') !== false || strpos($namaF, 'tarif') !== false || strpos($namaF, 'total') !== false || strpos($namaF, 'bayar') !== false) {
            $kolomBiaya = $col['Field'];
            break;
        }
    }
}

$tglMulai = $_GET['tgl_mulai'] ?? '';
$tglSelesai = $_GET['tgl_selesai'] ?? '';
$whereFilter = "WHERE waktu_keluar IS NOT NULL";
if (!empty($tglMulai) && !empty($tglSelesai)) {
    $whereFilter .= " AND DATE(waktu_keluar) BETWEEN '" . mysqli_real_escape_string($koneksi, $tglMulai) . "' AND '" . mysqli_real_escape_string($koneksi, $tglSelesai) . "'";
}

$qRekap = mysqli_query($koneksi, "SELECT k.*, a.nama_area FROM tb_kendaraan k LEFT JOIN tb_area_parkir a ON k.id_area = a.id_area $whereFilter ORDER BY k.waktu_keluar DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekap Transaksi - Coffe Sedayu</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --bg-main: #060911; --bg-sidebar: #0b0f19; --bg-card: rgba(15, 23, 42, 0.8); --border-gold: rgba(212, 175, 55, 0.25); --gold-grad: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%); --text-gold: #f3e5ab; --text-sub: #94a3b8; }
    body { background-color: var(--bg-main); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; min-height: 100vh; }
    sidebar { width: 280px; background-color: var(--bg-sidebar); border-right: 1px solid var(--border-gold); display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; }
    .sidebar-header { padding: 24px 20px; display: flex; align-items: center; gap: 14px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .brand-icon { width: 42px; height: 42px; background: var(--gold-grad); color: #000; font-weight: 800; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .sidebar-menu { padding: 20px 14px; flex: 1; overflow-y: auto; }
    .menu-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; color: #cbd5e1; text-decoration: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; }
    .menu-item:hover, .menu-item.active { background: rgba(212, 175, 55, 0.12); color: var(--text-gold); }
    .menu-item i { width: 20px; color: var(--text-gold); }
    .main-content { margin-left: 280px; flex: 1; padding: 30px; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; background: var(--bg-card); border: 1px solid var(--border-gold); border-radius: 14px; padding: 20px 25px; margin-bottom: 25px; }
    .glass-card { background: var(--bg-card); border: 1px solid var(--border-gold); border-radius: 14px; padding: 20px; backdrop-filter: blur(10px); }
    table { width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: left; }
    th { background: rgba(212, 175, 55, 0.1); color: var(--text-gold); padding: 12px; border-bottom: 1px solid var(--border-gold); }
    td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; }
    .btn-print { background: var(--gold-grad); color: #000; border: none; font-weight: 700; padding: 9px 18px; border-radius: 8px; cursor: pointer; text-decoration: none; }
  </style>
</head>
<body>
  <sidebar>
    <div class="sidebar-header">
      <div class="brand-icon"><i class="fa-solid fa-mug-hot"></i></div>
      <div><h2 style="font-size:0.95rem; background:var(--gold-grad); -webkit-background-clip:text; -webkit-text-fill-color:transparent;">COFFE SEDAYU</h2><span style="font-size:0.7rem; color:var(--text-sub);">Parking System</span></div>
    </div>
    <div class="sidebar-menu">
      <a href="owner.php" class="menu-item"><i class="fa-solid fa-chart-pie"></i> <span>Dashboard</span></a>
      <a href="rekap.php" class="menu-item active"><i class="fa-solid fa-file-invoice-dollar"></i> <span>Rekap Transaksi</span></a>
      <a href="tarif.php" class="menu-item"><i class="fa-solid fa-tags"></i> <span>CRUD Tarif Parkir</span></a>
      <a href="kendaraan.php" class="menu-item"><i class="fa-solid fa-car"></i> <span>CRUD Kendaraan</span></a>
      <a href="area.php" class="menu-item"><i class="fa-solid fa-square-parking"></i> <span>CRUD Area Parkir</span></a>
      <a href="log_aktivitas.php" class="menu-item"><i class="fa-solid fa-clock-rotate-left"></i> <span>Log Aktivitas</span></a>
      <a href="performa_petugas.php" class="menu-item"><i class="fa-solid fa-star-half-stroke"></i> <span>Performa Petugas</span></a>
      <a href="staff.php" class="menu-item"><i class="fa-solid fa-users-gear"></i> <span>Daftar Staff</span></a>
    </div>
  </sidebar>
  <div class="main-content">
    <div class="top-bar">
      <div><h1>Rekap Transaksi</h1><p>Seluruh riwayat transaksi keluar parkir.</p></div>
      <button onclick="window.print()" class="btn-print"><i class="fa-solid fa-print"></i> Cetak</button>
    </div>
    <div class="glass-card">
      <table>
        <thead>
          <tr><th>No</th><th>Plat Nomor</th><th>Jenis</th><th>Area</th><th>Waktu Keluar</th><th>Biaya</th></tr>
        </thead>
        <tbody>
          <?php if ($qRekap && mysqli_num_rows($qRekap) > 0): ?>
            <?php $no = 1; while ($r = mysqli_fetch_assoc($qRekap)): ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><strong><?= htmlspecialchars($r['plat_nomor']); ?></strong></td>
                <td><?= htmlspecialchars($r['jenis_kendaraan']); ?></td>
                <td><?= htmlspecialchars($r['nama_area'] ?? 'Umum'); ?></td>
                <td><?= htmlspecialchars($r['waktu_keluar']); ?></td>
                <td style="color:#10b981; font-weight:700;">Rp <?= number_format($r[$kolomBiaya] ?? 0, 0, ",", "."); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center; color:var(--text-sub);">Belum ada data transaksi.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>