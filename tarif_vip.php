<?php
require_once "auth_check.php";
require_once "koneksi.php";
batasiAkses(['admin']);

// Edit Tarif
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tarif'])) {
    $id_tarif = intval($_POST['id_tarif']);
    $tarif    = floatval($_POST['tarif_per_jam']);
    
    $stmt = mysqli_prepare($koneksi, "UPDATE tb_tarif SET tarif_per_jam = ? WHERE id_tarif = ?");
    mysqli_stmt_bind_param($stmt, "di", $tarif, $id_tarif);
    if (mysqli_stmt_execute($stmt)) {
        catatLog($koneksi, "Mengubah tarif ID: $id_tarif menjadi Rp $tarif");
        echo "<script>alert('Tarif berhasil diperbarui!'); window.location.href='tarif_vip.php';</script>";
    }
}

$resTarif = mysqli_query($koneksi, "SELECT * FROM tb_tarif");
$resArea  = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tarif & Area Parkir - Grand Pasar</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="app-container">
    <?php include "sidebar.php"; ?>
    <main class="main-content">
      <header class="hero-header">
        <h1>KELOLA <span>TARIF & AREA</span></h1>
        <p>Manajemen Biaya Parkir & Kapasitas Area</p>
      </header>

      <div class="main-grid">
        <section class="panel">
          <div class="panel-title">Pengaturan Tarif Parkir</div>
          <table class="parking-table">
            <thead>
              <tr>
                <th>Jenis Kendaraan</th>
                <th>Tarif / Jam</th>
                <th>Update</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($t = mysqli_fetch_assoc($resTarif)): ?>
              <tr>
                <form method="POST" action="tarif_vip.php">
                  <td><strong><?php echo strtoupper($t['jenis_kendaraan']); ?></strong></td>
                  <td>
                    <input type="hidden" name="id_tarif" value="<?php echo $t['id_tarif']; ?>">
                    <input type="number" name="tarif_per_jam" class="form-control" value="<?php echo $t['tarif_per_jam']; ?>" required>
                  </td>
                  <td><button type="submit" name="update_tarif" class="btn-action-sm">Simpan</button></td>
                </form>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </section>

        <section class="panel">
          <div class="panel-title">Kapasitas Area Parkir</div>
          <table class="parking-table">
            <thead>
              <tr>
                <th>Nama Area</th>
                <th>Kapasitas</th>
                <th>Terisi</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($a = mysqli_fetch_assoc($resArea)): ?>
              <tr>
                <td><?php echo htmlspecialchars($a['nama_area']); ?></td>
                <td><?php echo $a['kapasitas']; ?> Slot</td>
                <td><span class="slot-tag"><?php echo $a['terisi']; ?> Slot</span></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </section>
      </div>
    </main>
  </div>
</body>
</html>