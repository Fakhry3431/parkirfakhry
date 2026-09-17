<?php
require_once "auth_check.php";
require_once "koneksi.php";

batasiAkses(['admin', 'petugas']);

$cetak_id = null;
$plat_sukses = "";

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

    // Ambil tarif sesuai jenis kendaraan
    $qTarif = mysqli_query($koneksi, "SELECT id_tarif FROM tb_tarif WHERE jenis_kendaraan = '$jenis' LIMIT 1");
    $rTarif = mysqli_fetch_assoc($qTarif);
    $id_tarif = $rTarif['id_tarif'] ?? 1;

    // Simpan transaksi parkir masuk
    $stmtT = mysqli_prepare($koneksi, "INSERT INTO tb_transaksi (id_kendaraan, waktu_masuk, id_tarif, status, id_user, id_area) VALUES (?, ?, ?, 'masuk', ?, ?)");
    mysqli_stmt_bind_param($stmtT, "isiii", $id_kendaraan, $masuk, $id_tarif, $id_user, $id_area);
    
    if (mysqli_stmt_execute($stmtT)) {
        $cetak_id = mysqli_insert_id($koneksi);
        $plat_sukses = $plat;
        mysqli_query($koneksi, "UPDATE tb_area_parkir SET terisi = terisi + 1 WHERE id_area = '$id_area'");
        
        if (function_exists('catatLog')) {
            catatLog($koneksi, "Parkir Masuk: $plat di Area ID $id_area");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Parkir Masuk - Grand Pasar Executive Parking</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="app-container">
    <?php include "sidebar.php"; ?>

    <main class="main-content">
      <header class="hero-header">
        <h1><i class="fa-solid fa-right-to-bracket"></i> PARKIR <span>MASUK</span></h1>
        <p>Input data kendaraan yang baru masuk ke area parkir</p>
      </header>

      <?php if ($cetak_id): ?>
      <!-- NOTIFIKASI & TOMBOL CETAK LANGSUNG -->
      <section class="panel" style="max-width: 600px; border: 1px solid #28a745; background: rgba(40, 167, 69, 0.1); margin-bottom: 20px;">
        <div style="text-align: center; padding: 10px;">
          <h3 style="color: #28a745; margin-bottom: 10px;"><i class="fa-solid fa-circle-check"></i> Kendaraan <?php echo htmlspecialchars($plat_sukses); ?> Berhasil Dicatat!</h3>
          <p style="margin-bottom: 15px;">Klik tombol di bawah untuk mencetak struk/tiket parkir:</p>
          <a href="cetak_struk.php?id=<?php echo $cetak_id; ?>" target="_blank" class="btn-luxury" style="display: inline-block; text-decoration: none; width: auto; padding: 10px 25px;">
            <i class="fa-solid fa-print"></i> Cetak Struk Parkir
          </a>
        </div>
      </section>
      <script>
        // Membuka cetak_struk secara otomatis setelah halaman selesai dimuat
        window.open('cetak_struk.php?id=<?php echo $cetak_id; ?>', '_blank');
      </script>
      <?php endif; ?>

      <section class="panel" style="max-width: 600px;">
        <div class="panel-title">Form Registrasi Kendaraan</div>
        <form method="POST" action="parkir_masuk.php">
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
    </main>
  </div>
</body>
</html>