<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'owner') {
    header("Location: index.php");
    exit();
}

// Proses Tambah / Update Tarif jika form disubmit
$pesan = "";
if (isset($_POST['simpan_tarif'])) {
    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $tarif = floatval($_POST['tarif']);
    
    // Cek apakah tabel tarif menggunakan id atau jenis sebagai primary
    $cek = mysqli_query($koneksi, "SELECT * FROM tb_tarif WHERE jenis_kendaraan = '$jenis'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "UPDATE tb_tarif SET tarif = '$tarif' WHERE jenis_kendaraan = '$jenis'");
        $pesan = "Tarif berhasil diperbarui!";
    } else {
        mysqli_query($koneksi, "INSERT INTO tb_tarif (jenis_kendaraan, tarif) VALUES ('$jenis', '$tarif')");
        $pesan = "Tarif baru berhasil ditambahkan!";
    }
}

// Proses Hapus Tarif
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM tb_tarif WHERE id_tarif = '$id' OR id = '$id'");
    header("Location: tarif.php");
    exit();
}

$queryTarif = mysqli_query($koneksi, "SELECT * FROM tb_tarif");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>CRUD Tarif Parkir - Coffe Sedayu</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { background: #060911; color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; padding: 30px; }
    .container { max-width: 900px; margin: 0 auto; background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(212, 175, 55, 0.25); border-radius: 14px; padding: 25px; }
    h2 { color: #f3e5ab; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: left; }
    th { background: rgba(212, 175, 55, 0.1); color: #f3e5ab; }
    .btn { background: linear-gradient(135deg, #bf953f, #b38728); color: #000; padding: 8px 16px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn-danger { background: #ef4444; color: #fff; }
    input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: #fff; padding: 8px 12px; border-radius: 6px; width: 100%; margin-bottom: 10px; }
    label { font-size: 0.85rem; color: #94a3b8; }
  </style>
</head>
<body>
<div class="container">
  <a href="owner.php" class="btn" style="margin-bottom: 20px; font-size: 0.8rem;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
  <h2><i class="fa-solid fa-tags"></i> Pengaturan CRUD Tarif Parkir</h2>
  
  <?php if($pesan): ?><div style="background: rgba(16,185,129,0.2); color: #10b981; padding: 10px; border-radius: 8px; margin-bottom: 15px;"><?php echo $pesan; ?></div><?php endif; ?>

  <form method="POST" style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 25px;">
    <label>Jenis Kendaraan:</label>
    <input type="text" name="jenis_kendaraan" placeholder="Contoh: Mobil / Motor" required>
    <label>Nominal Tarif (Rp):</label>
    <input type="number" name="tarif" placeholder="Contoh: 5000" required>
    <button type="submit" name="simpan_tarif" class="btn"><i class="fa-solid fa-save"></i> Simpan Tarif</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Jenis Kendaraan</th>
        <th>Tarif</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($t = mysqli_fetch_assoc($queryTarif)): ?>
      <tr>
        <td><?php echo $no++; ?></td>
        <td><strong><?php echo htmlspecialchars($t['jenis_kendaraan']); ?></strong></td>
        <td style="color: #10b981; font-weight: 700;">Rp <?php echo number_format($t['tarif'], 0, ',', '.'); ?></td>
        <td>
          <a href="tarif.php?hapus=<?php echo $t['id_tarif'] ?? $t['id'] ?? 0; ?>" class="btn btn-danger" onclick="return confirm('Hapus tarif ini?')"><i class="fa-solid fa-trash"></i></a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>