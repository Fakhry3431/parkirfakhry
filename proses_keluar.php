<?php
require_once "auth_check.php";
require_once "koneksi.php";

batasiAkses(['admin', 'petugas']);

// Transaksi Kendaraan Keluar
if (isset($_GET['aksi']) && $_GET['aksi'] === 'keluar' && isset($_GET['id'])) {
    $id_parkir    = intval($_GET['id']);
    $waktu_keluar = date('Y-m-d H:i:s');

    $qTx = mysqli_query($koneksi, "
        SELECT t.*, tr.tarif_per_jam, k.plat_nomor 
        FROM tb_transaksi t 
        JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
        JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
        WHERE t.id_parkir = '$id_parkir' AND t.status = 'masuk'
    ");

    if ($rTx = mysqli_fetch_assoc($qTx)) {
        $durasi = ceil((strtotime($waktu_keluar) - strtotime($rTx['waktu_masuk'])) / 3600);
        $durasi = max(1, $durasi);
        $biaya  = $durasi * $rTx['tarif_per_jam'];

        $uStmt = mysqli_prepare($koneksi, "UPDATE tb_transaksi SET waktu_keluar = ?, durasi_jam = ?, biaya_total = ?, status = 'keluar' WHERE id_parkir = ?");
        mysqli_stmt_bind_param($uStmt, "sidi", $waktu_keluar, $durasi, $biaya, $id_parkir);
        
        if (mysqli_stmt_execute($uStmt)) {
            mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = GREATEST(0, terisi - 1) WHERE id_area = '{$rTx['id_area']}'");
            catatLog($koneksi, "Parkir Keluar: {$rTx['plat_nomor']} (Total Rp " . number_format($biaya) . ")");
            
            echo "<script>alert('Pembayaran Berhasil!\\nPlat: {$rTx['plat_nomor']}\\nDurasi: $durasi Jam\\nTotal Biaya: Rp " . number_format($biaya) . "'); window.open('cetak_struk.php?id=$id_parkir', '_blank'); window.location.href='proses_keluar.php';</script>";
        }
    }
}

// Data Kendaraan Masih Terparkir
$qAktif = mysqli_query($koneksi, "
    SELECT t.id_parkir, t.waktu_masuk, k.plat_nomor, k.jenis_kendaraan, a.nama_area 
    FROM tb_transaksi t 
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan 
    JOIN tb_area_parkir a ON t.id_area = a.id_area 
    WHERE t.status = 'masuk' 
    ORDER BY t.waktu_masuk DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kendaraan Parkir - Grand Pasar Executive Parking</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="app-container">
    <?php include "sidebar.php"; ?>

    <main class="main-content">
      <header class="hero-header">
        <h1><i class="fa-solid fa-square-parking"></i> KENDARAAN <span>PARKIR</span></h1>
        <p>Daftar kendaraan aktif di dalam area & transaksi keluar</p>
      </header>

      <section class="panel">
        <div class="panel-title">Daftar Kendaraan Terparkir</div>
        <div class="parking-table-wrapper">
          <table class="parking-table">
            <thead>
              <tr>
                <th>Plat Nomor</th>
                <th>Jenis</th>
                <th>Area</th>
                <th>Waktu Masuk</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($qAktif) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($qAktif)): ?>
                <tr>
                  <td><span class="plate-badge"><?php echo htmlspecialchars($row['plat_nomor']); ?></span></td>
                  <td><?php echo strtoupper($row['jenis_kendaraan']); ?></td>
                  <td><span class="slot-tag"><?php echo htmlspecialchars($row['nama_area']); ?></span></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                  <td>
                    <a href="proses_keluar.php?aksi=keluar&id=<?php echo $row['id_parkir']; ?>" 
                       onclick="return confirm('Proses transaksi keluar untuk plat <?php echo $row['plat_nomor']; ?>?')" 
                       class="btn-action-sm">
                       <i class="fa-solid fa-receipt"></i> Keluar & Bayar
                    </a>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" style="text-align:center; color: var(--text-muted);">Tidak ada kendaraan aktif di area parkir.</td>
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