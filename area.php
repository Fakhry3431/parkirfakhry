<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'owner') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['tambah_area'])) {
    $namaArea = mysqli_real_escape_string($koneksi, $_POST['nama_area']);
    $kapasitas = intval($_POST['kapasitas']);
    mysqli_query($koneksi, "INSERT INTO tb_area_parkir (nama_area, kapasitas) VALUES ('$namaArea', '$kapasitas')");
    header("Location: area.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM tb_area_parkir WHERE id_area = '$id'");
    header("Location: area.php");
    exit();
}

$queryArea = mysqli_query($koneksi, "SELECT * FROM tb_area_parkir");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>CRUD Area Parkir - Coffe Sedayu</title>
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
  <h2><i class="fa-solid fa-square-parking"></i> Pengaturan Area Parkir</h2>

  <form method="POST" style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; margin-bottom: 25px;">
    <label>Nama Area / Zona:</label>
    <input type="text" name="nama_area" placeholder="Contoh: Zona A (Mobil VIP)" required>
    <label>Kapasitas Maksimal:</label>
    <input type="number" name="kapasitas" placeholder="Contoh: 50" required>
    <button type="submit" name="tambah_area" class="btn"><i class="fa-solid fa-plus"></i> Tambah Area</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Area</th>
        <th>Kapasitas</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($a = mysqli_fetch_assoc($queryArea)): ?>
      <tr>
        <td><?php echo $no++; ?></td>
        <td><strong><?php echo htmlspecialchars($a['nama_area']); ?></strong></td>
        <td><?php echo htmlspecialchars($a['kapasitas'] ?? '-'); ?> Kendaraan</td>
        <td>
          <a href="area.php?hapus=<?php echo $a['id_area']; ?>" class="btn btn-danger" onclick="return confirm('Hapus area ini?')"><i class="fa-solid fa-trash"></i></a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>