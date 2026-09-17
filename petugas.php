<?php
require_once "auth_check.php";
require_once "koneksi.php";

// ============================================================================
// OTOMATISASI: CEK & BUAT AKUN PETUGAS (JIKA BELUM ADA)
// ============================================================================
$uPetugas = "petugas";
$pPetugas = "123";
$nPetugas = "Petugas Parkir";
$rPetugas = "petugas";

$qCekPetugas = mysqli_query($koneksi, "SELECT id_user FROM tb_user WHERE username = '$uPetugas'");
if (mysqli_num_rows($qCekPetugas) == 0) {
    $passHash = password_hash($pPetugas, PASSWORD_DEFAULT);
    $stmtBuat = mysqli_prepare($koneksi, "INSERT INTO tb_user (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtBuat, "ssss", $uPetugas, $passHash, $nPetugas, $rPetugas);
    mysqli_stmt_execute($stmtBuat);
}

// Kunci halaman: Hanya Petugas dan Admin yang bisa akses
batasiAkses(['petugas', 'admin']);

// 1. PROSES PARKIR MASUK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_masuk'])) {
    $plat    = strtoupper(trim($_POST['plat_nomor']));
    $jenis   = $_POST['jenis_kendaraan'];
    $warna   = trim($_POST['warna']);
    $id_area = intval($_POST['id_area']);
    $id_user = $_SESSION['id_user'];
    $masuk   = date('Y-m-d H:i:s');

    // Cek atau buat data kendaraan
    $qKen = mysqli_query($koneksi, "SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = '$plat'");
    if (mysqli_num_rows($qKen) > 0) {
        $rKen = mysqli_fetch_assoc($qKen);
        $id_kendaraan = $rKen['id_kendaraan'];
    } else {
        $stmtK = mysqli_prepare($koneksi, "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, warna, id_user) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtK, "sssi", $plat, $jenis, $warna, $id_user);
        mysqli_stmt_execute($stmtK);
        $id_kendaraan = mysqli_insert_id($koneksi);
    }

    // Ambil id_tarif sesuai jenis kendaraan
    $qTarif = mysqli_query($koneksi, "SELECT id_tarif FROM tb_tarif WHERE jenis_kendaraan = '$jenis' LIMIT 1");
    $rTarif = mysqli_fetch_assoc($qTarif);
    $id_tarif = isset($rTarif['id_tarif']) ? $rTarif['id_tarif'] : 1;

    // Simpan Transaksi Masuk
    $stmtT = mysqli_prepare($koneksi, "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area) VALUES (?, ?, ?, 'masuk', ?, ?)");
    mysqli_stmt_bind_param($stmtT, "isiii", $id_kendaraan, $masuk, $id_tarif, $id_user, $id_area);
    
    if (mysqli_stmt_execute($stmtT)) {
        $id_parkir_baru = mysqli_insert_id($koneksi);
        // Tambah jumlah terisi pada area parkir
        mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = '$id_area'");
        if (function_exists('catatLog')) {
            catatLog($koneksi, "Petugas menginput Parkir Masuk: $plat");
        }
        
        echo "<script>alert('Kendaraan $plat berhasil masuk!'); window.open('cetak_struk.php?id=$id_parkir_baru', '_blank'); window.location.href='petugas.php';</script>";
    }
}

// 2. PROSES PARKIR KELUAR & HITUNG BIAYA
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
        // Hitung durasi pembulatan ke atas per jam
        $durasi = ceil((strtotime($waktu_keluar) - strtotime($rTx['waktu_masuk'])) / 3600);
        $durasi = max(1, $durasi);
        $biaya  = $durasi * $rTx['tarif_per_jam'];

        $uStmt = mysqli_prepare($koneksi, "UPDATE tb_transaksi SET waktu_keluar = ?, durasi_jam = ?, biaya_total = ?, status = 'keluar' WHERE id_parkir = ?");
        mysqli_stmt_bind_param($uStmt, "sidi", $waktu_keluar, $durasi, $biaya, $id_parkir);
        
        if (mysqli_stmt_execute($uStmt)) {
            // Kurangi jumlah terisi di area parkir
            mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = GREATEST(0, terisi - 1) WHERE id_area = '{$rTx['id_area']}'");
            if (function_exists('catatLog')) {
                catatLog($koneksi, "Petugas memproses Parkir Keluar: {$rTx['plat_nomor']} (Rp " . number_format($biaya) . ")");
            }
            
            echo "<script>alert('Pembayaran Berhasil!\\nPlat: {$rTx['plat_nomor']}\\nDurasi: $durasi Jam\\nTotal Biaya: Rp " . number_format($biaya) . "'); window.open('cetak_struk.php?id=$id_parkir', '_blank'); window.location.href='petugas.php';</script>";
        }
    }
}

// Ambil list kendaraan yang masih terparkir
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Petugas - Grand Pasar Executive Parking</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <div class="app-container">
    <?php if (file_exists("sidebar.php")) include "sidebar.php"; ?>

    <main class="main-content">
      <header class="hero-header">
        <h1><i class="fa-solid fa-square-parking"></i> OPERASIONAL <span>PETUGAS</span></h1>
        <p>Selamat Datang, <strong><?php echo htmlspecialchars(isset($_SESSION['username']) ? $_SESSION['username'] : 'Petugas'); ?></strong></p>
      </header>

      <div class="main-grid">
        <!-- FORM INPUT PARKIR MASUK -->
        <section class="panel">
          <div class="panel-title"><i class="fa-solid fa-car-side"></i> Transaksi Parkir Masuk</div>
          <form method="POST" action="petugas.php">
            <div class="form-group">
              <label>Plat Nomor Kendaraan</label>
              <input type="text" name="plat_nomor" class="form-control" placeholder="Contoh: AB 1234 CD" required autofocus>
            </div>
            <div class="form-group">
              <label>Jenis Kendaraan</label>
              <select name="jenis_kendaraan" class="form-control" required>
                <option value="motor">Motor</option>
                <option value="mobil">Mobil</option>
                <option value="lainnya">Lainnya</option>
              </select>
            </div>
            <div class="form-group">
              <label>Warna Kendaraan</label>
              <input type="text" name="warna" class="form-control" placeholder="Contoh: Hitam / Merah">
            </div>
            <div class="form-group">
              <label>Pilih Area Parkir</label>
              <select name="id_area" class="form-control" required>
                <?php
                $qArea = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir");
                while ($a = mysqli_fetch_assoc($qArea)) {
                    $sisa = $a['kapasitas'] - $a['terisi'];
                    echo "<option value='{$a['id_area']}'>{$a['nama_area']} (Sisa Slot: $sisa)</option>";
                }
                ?>
              </select>
            </div>
            <button type="submit" name="proses_masuk" class="btn-luxury">
              <i class="fa-solid fa-ticket"></i> Masuk & Cetak Tiket
            </button>
          </form>
        </section>

        <!-- TABEL DAFTAR KENDARAAN TERPARKIR & KELUAR -->
        <section class="panel">
          <div class="panel-title"><i class="fa-solid fa-list-check"></i> Kendaraan Aktif Parkir</div>
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
                <?php 
                if ($qAktif && mysqli_num_rows($qAktif) > 0) {
                    while ($row = mysqli_fetch_assoc($qAktif)) {
                ?>
                  <tr>
                    <td><span class="plate-badge"><?php echo htmlspecialchars($row['plat_nomor']); ?></span></td>
                    <td><?php echo strtoupper($row['jenis_kendaraan']); ?></td>
                    <td><span class="slot-tag"><?php echo htmlspecialchars($row['nama_area']); ?></span></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['waktu_masuk'])); ?></td>
                    <td>
                      <a href="petugas.php?aksi=keluar&id=<?php echo $row['id_parkir']; ?>" 
                         onclick="return confirm('Proses pembayaran & keluar untuk kendaraan <?php echo $row['plat_nomor']; ?>?')" 
                         class="btn-action-sm">
                         <i class="fa-solid fa-receipt"></i> Keluar / Bayar
                      </a>
                    </td>
                  </tr>
                <?php 
                    }
                } else { 
                ?>
                  <tr>
                    <td colspan="5" style="text-align:center; color: var(--text-muted);">Tidak ada kendaraan di dalam area parkir.</td>
                  </tr>
                <?php 
                } 
                ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  </div>

</body>
</html>