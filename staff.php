<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'owner') {
    header("Location: index.php");
    exit();
}

$queryStaff = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE LOWER(role) != 'owner'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Staff - Coffe Sedayu</title>
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
    .badge-role { background: rgba(56, 189, 248, 0.15); color: #38bdf8; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
  </style>
</head>
<body>
<div class="container">
  <a href="owner.php" class="btn" style="margin-bottom: 20px; font-size: 0.8rem;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
  <h2><i class="fa-solid fa-users-gear"></i> Daftar Staff / Petugas Parkir</h2>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Username</th>
        <th>Nama Lengkap / Role</th>
        <th>Status Akun</th>
      </tr>
    </thead>
    <tbody>
      <?php $no=1; while($s = mysqli_fetch_assoc($queryStaff)): ?>
      <tr>
        <td><?php echo $no++; ?></td>
        <td><strong><?php echo htmlspecialchars($s['username']); ?></strong></td>
        <td><span class="badge-role"><?php echo htmlspecialchars($s['role'] ?? 'Petugas'); ?></span></td>
        <td style="color: #10b981; font-weight: 600;"><i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> Aktif</td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>