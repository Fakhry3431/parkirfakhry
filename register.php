<?php
/*
  register.php - Form Pendaftaran Sistem Parkir
*/
session_start();
require_once "koneksi.php";

$error = '';
$success = '';

if (isset($_POST['register'])) {
    $nama_lengkap     = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $username         = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Cek apakah username sudah terdaftar
        $checkUser = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username='$username'");
        if (mysqli_num_rows($checkUser) > 0) {
            $error = "Username telah digunakan, silakan pakai username lain!";
        } else {
            $hashPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert data ke tb_user (Termasuk nama_lengkap, role default 'pengunjung', status_aktif 1)
            $query = mysqli_query($koneksi, "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES ('$nama_lengkap', '$username', '$hashPassword', 'pengunjung', 1)");
            
            if ($query) {
                $success = "Pendaftaran akun berhasil! Silakan login untuk melanjutkan.";
            } else {
                $error = "Gagal mendaftar akun: " . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - Sistem Parkir</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: #060911;
      color: #ffffff;
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .register-card {
      background: #0f172a;
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 32px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
      position: relative;
    }
    .btn-close {
      position: absolute;
      top: 16px;
      right: 20px;
      color: #94a3b8;
      font-size: 1.5rem;
      font-weight: 700;
      text-decoration: none;
      line-height: 1;
      transition: color 0.2s;
    }
    .btn-close:hover { color: #ef4444; }
    .card-title {
      font-size: 1.25rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 24px;
      color: #ffffff;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block;
      font-size: 0.85rem;
      color: #94a3b8;
      margin-bottom: 6px;
    }
    .form-control {
      width: 100%;
      padding: 12px 16px;
      background: #0b1120;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      color: #ffffff;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s;
    }
    .form-control:focus { border-color: #d4af37; }
    .btn-submit {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 50%, #aa771c 100%);
      border: none;
      border-radius: 24px;
      color: #000000;
      font-weight: 700;
      font-size: 0.95rem;
      cursor: pointer;
      margin-top: 10px;
      box-shadow: 0 4px 12px rgba(212, 175, 55, 0.25);
      transition: transform 0.2s, opacity 0.2s;
    }
    .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
    .card-footer-text { text-align: center; margin-top: 20px; font-size: 0.85rem; color: #94a3b8; }
    .card-footer-text a { color: #ffffff; font-weight: 700; text-decoration: none; }
    .card-footer-text a:hover { text-decoration: underline; }
    .back-link { display: block; text-align: center; margin-top: 16px; font-size: 0.8rem; color: #64748b; text-decoration: none; }
    .back-link:hover { color: #f3e5ab; }
    
    /* MODAL NOTIFIKASI SYSTEM */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-box {
      background: #0f172a;
      border: 1px solid rgba(212, 175, 55, 0.3);
      border-radius: 16px;
      padding: 24px 28px;
      width: 90%;
      max-width: 360px;
      text-align: center;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.8);
      transform: scale(0.8);
      transition: transform 0.3s ease;
    }
    .modal-overlay.active .modal-box { transform: scale(1); }
    .modal-icon { font-size: 2.5rem; margin-bottom: 12px; }
    .modal-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: #fff; }
    .modal-message { font-size: 0.85rem; color: #94a3b8; margin-bottom: 20px; line-height: 1.4; }
    .btn-modal { width: 100%; padding: 10px; border-radius: 20px; border: none; font-weight: 700; font-size: 0.85rem; cursor: pointer; }
    .btn-modal.success { background: #10b981; color: #ffffff; }
    .btn-modal.error { background: #ef4444; color: #ffffff; }
  </style>
</head>
<body>

  <div class="register-card">
    <a href="index.php" class="btn-close" title="Kembali ke Landing Page">&times;</a>
    <h2 class="card-title">Daftar Akun Baru</h2>

    <form action="" method="POST">
      <div class="form-group">
        <label for="nama_lengkap">Nama Lengkap</label>
        <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Buat username" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Buat password" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Konfirmasi Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Ulangi password" required>
      </div>

      <button type="submit" name="register" class="btn-submit">Daftar Sekarang</button>
    </form>

    <div class="card-footer-text">
      Sudah punya akun? <a href="login.php">Login di sini</a>
    </div>

    <a href="index.php" class="back-link">← Kembali ke Halaman Utama</a>
  </div>

  <?php if (!empty($error)): ?>
    <div class="modal-overlay active" id="notifyModal">
      <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <div class="modal-title">Pendaftaran Gagal</div>
        <div class="modal-message"><?php echo htmlspecialchars($error); ?></div>
        <button class="btn-modal error" onclick="closeModal()">Coba Lagi</button>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="modal-overlay active" id="notifyModal">
      <div class="modal-box">
        <div class="modal-icon">✅</div>
        <div class="modal-title">Berhasil!</div>
        <div class="modal-message"><?php echo htmlspecialchars($success); ?></div>
        <button class="btn-modal success" onclick="redirectToLogin()">Ke Halaman Login</button>
      </div>
    </div>
  <?php endif; ?>

  <script>
    function closeModal() {
      document.getElementById('notifyModal')?.classList.remove('active');
    }
    function redirectToLogin() {
      window.location.href = 'login.php';
    }
  </script>

</body>
</html>