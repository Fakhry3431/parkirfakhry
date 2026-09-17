<?php
require_once "auth_check.php";
require_once "koneksi.php";

// Hanya Admin dan Owner/Petugas yang diizinkan melihat laporan
batasiAkses(['admin', 'owner', 'petugas']);

// Filter Tanggal (Default: Bulan Ini)
$tgl_mulai   = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

// Query Ambil Laporan Keuangan Parkir
$query = "
    SELECT t.id_parkir, t.waktu_masuk, t.waktu_keluar, t.durasi_jam, t.biaya_total, t.status,
           k.plat_nomor, k.jenis_kendaraan, a.nama_area, u.nama_lengkap as nama_petugas
    FROM tb_transaksi t
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
    JOIN tb_area_parkir a ON t.id_area = a.id_area
    LEFT JOIN tb_user u ON t.id_user = u.id_user
    WHERE DATE(t.waktu_masuk) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
    ORDER BY t.id_parkir DESC
";

$result = mysqli_query($koneksi, $query);

// Hitung Total Pendapatan & Kendaraan Keluar
$total_pendapatan = 0;
$total_kendaraan  = 0;

$data_laporan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data_laporan[] = $row;
    if ($row['status'] === 'keluar') {
        $total_pendapatan += $row['biaya_total'];
        $total_kendaraan++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Keuangan Parkir - Grand Pasar</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @media print {
      .no-print, sidebar, .sidebar { display: none !important; }
      .main-content { margin: 0 !important; padding: 0 !important; }
      body { background: #fff !important; color: #000 !important; }
      .panel { border: none !important; box-shadow: none !important; background: transparent !important; }
      .parking-table th, .parking-table td { border: 1px solid #333 !important; color: #000 !important; }
    }
  </style>
</head>
<body>

  <div class="app-container">
    <div class="no-print">
      <?php if (file_exists("sidebar.php")) include "sidebar.php"; ?>
    </div>

    <main class="main-content" style="padding: 30px;">
      <header class="hero-header" style="margin-bottom: 25px;">
        <h1><i class="fa-solid fa-file-invoice-dollar"></i> LAPORAN <span>KEUANGAN</span></h1>
        <p>Rekapitulasi transaksi dan pendapatan operasional parkir</p>
      </header>

      <!-- FILTER TANGGAL -->
      <section class="panel no-print" style="margin-bottom: 25px;">
        <form method="GET" action="laporan.php" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 180px;">
            <label style="display: block; margin-bottom: 5px; font-size: 12px;">Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" class="form-control" value="<?php echo $tgl_mulai; ?>" required>
          </div>
          <div style="flex: 1; min-width: 180px;">
            <label style="display: block; margin-bottom: 5px; font-size: 12px;">Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" class="form-control" value="<?php echo $tgl_selesai; ?>" required>
          </div>
          <button type="submit" class="btn-luxury" style="width: auto; padding: 10px 20px;">
            <i class="fa-solid fa-filter"></i> Filter
          </button>
          <button type="button" onclick="window.print()" class="btn-luxury" style="width: auto; padding: 10px 20px; background: #28a745;">
            <i class="fa-solid fa-print"></i> Cetak Laporan
          </button>
        </form>
      </section>

      <!-- CARD SUMMARY PENDAPATAN -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
        <div class="panel" style="border-left: 4px solid #28a745;">
          <div style="font-size: 12px; color: #888;">TOTAL PENDAPATAN (SELESAI)</div>
          <div style="font-size: 26px; font-weight: bold; color: #28a745; margin-top: 5px;">
            Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?>
          </div>
        </div>
        <div class="panel" style="border-left: 4px solid #007bff;">
          <div style="font-size: 12px; color: #888;">TOTAL KENDARAAN KELUAR</div>
          <div style="font-size: 26px; font-weight: bold; margin-top: 5px;">
            <?php echo number_format($total_kendaraan); ?> Kendaraan
          </div>
        </div>
      </div>

      <!-- TABEL LAPORAN -->
      <section class="panel">
        <div class="panel-title" style="margin-bottom: 15px;">
          Data Transaksi Periode: <?php echo date('d/m/Y', strtotime($tgl_mulai)); ?> s/d <?php echo date('d/m/Y', strtotime($tgl_selesai)); ?>
        </div>
        
        <div class="parking-table-wrapper">
          <table class="parking-table">
            <thead>
              <tr>
                <th>No. Tiket</th>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Area</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Durasi</th>
                <th>Total Biaya</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($data_laporan) > 0): ?>
                <?php foreach ($data_laporan as $row): ?>
                <tr>
                  <td>TKT-<?php echo str_pad($row['id_parkir'], 6, '0', STR_PAD_LEFT); ?></td>
                  <td><span class="plate-badge"><?php echo htmlspecialchars($row['plat_nomor']); ?></span></td>
                  <td><?php echo strtoupper($row['jenis_kendaraan']); ?></td>
                  <td><?php echo htmlspecialchars($row['nama_area']); ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                  <td><?php echo $row['waktu_keluar'] ? date('d/m/Y H:i', strtotime($row['waktu_keluar'])) : '-'; ?></td>
                  <td><?php echo $row['durasi_jam'] ? $row['durasi_jam'] . ' Jam' : '-'; ?></td>
                  <td><strong>Rp <?php echo number_format($row['biaya_total'] ?? 0, 0, ',', '.'); ?></strong></td>
                  <td>
                    <span style="padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: <?php echo $row['status'] == 'masuk' ? '#28a745' : '#6c757d'; ?>; color: #fff;">
                      <?php echo strtoupper($row['status']); ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" style="text-align:center; color: var(--text-muted);">Tidak ada transaksi pada periode tanggal ini.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

</body>
</html>