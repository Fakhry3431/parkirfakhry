<?php
session_start();
require_once "koneksi.php";

$loginSuccess = false;
$redirectTarget = "";
$error = "";

// Proses autentikasi form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Mengambil data user berdasarkan username
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM tb_user WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Cek status aktif akun terlebih dahulu
        if (isset($row['status_aktif']) && (int)$row['status_aktif'] !== 1) {
            $error = "Akun Anda dinonaktifkan! Silakan hubungi admin.";
        } 
        // Verifikasi password (memeriksa format hash maupun plain text)
        else if (password_verify($password, $row['password']) || $password === $row['password']) {
            
            // Hapus session lama yang tersimpan agar tidak menyebabkan loop redirect
            session_unset();
            
            // Simpan variabel penting ke dalam session
            $_SESSION['id_user']      = $row['id_user'];
            $_SESSION['user_id']      = $row['id_user'];
            $_SESSION['username']     = $row['username'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'] ?? $row['username'];
            $_SESSION['role']         = $row['role'];

            // Normalisasi string role (mengubah ke huruf kecil & hapus spasi)
            $roleDb = strtolower(trim($row['role']));
            $userDb = strtolower(trim($row['username']));

            // Penentuan halaman tujuan berdasarkan role / username
            if (strpos($roleDb, 'petugas') !== false || $userDb === 'petugas') {
                $redirectTarget = "petugas.php";
            } else if (strpos($roleDb, 'admin') !== false || $userDb === 'admin') {
                $redirectTarget = "admin.php";
            } else if (strpos($roleDb, 'owner') !== false || $userDb === 'owner') {
                $redirectTarget = "owner.php";
            } else if (strpos($roleDb, 'pengunjung') !== false || strpos($roleDb, 'user') !== false) {
                $redirectTarget = "pengunjung.php"; 
            } else {
                // Default jika role di DB tidak terdefinisi
                $redirectTarget = "petugas.php";
            }
            
            $loginSuccess = true;

        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak terdaftar dalam sistem!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - E-Parkir Pasar Pundong</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    
    body.login-body {
      background-color: #060911;
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 20px;
    }

    .login-card {
      background: #0f172a;
      padding: 32px;
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.6);
      width: 100%;
      max-width: 400px;
      border: 1px solid rgba(255, 255, 255, 0.1);
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

    .login-header {
      text-align: center;
      margin-bottom: 25px;
    }
    .login-header h2 {
      color: #f1c40f;
      font-weight: 700;
      margin-bottom: 6px;
    }
    .login-header p {
      color: #94a3b8;
      font-size: 0.85rem;
    }

    .form-group {
      margin-bottom: 18px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.85rem;
      color: #94a3b8;
    }
    .form-control {
      width: 100%;
      padding: 12px 16px;
      background: #0b1120;
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #fff;
      border-radius: 8px;
      outline: none;
      font-size: 0.95rem;
      transition: border-color 0.2s;
    }
    .form-control:focus {
      border-color: #f1c40f;
    }

    .btn-luxury {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 50%, #aa771c 100%);
      border: none;
      font-weight: 700;
      border-radius: 24px;
      cursor: pointer;
      color: #000;
      font-size: 0.95rem;
      box-shadow: 0 4px 12px rgba(212, 175, 55, 0.25);
      transition: transform 0.2s, opacity 0.2s;
      margin-top: 10px;
    }
    .btn-luxury:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }

    .login-footer {
      text-align: center;
      margin-top: 20px;
      font-size: 0.85rem;
      color: #94a3b8;
    }
    .login-footer a {
      color: #fff;
      font-weight: 700;
      text-decoration: none;
    }
    .login-footer a:hover { text-decoration: underline; }

    /* MODAL NOTIFIKASI */
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
<body class="login-body">

  <audio id="notifSound" src="assets/sound.mp3" preload="auto"></audio>

  <div class="login-card">
    <a href="index.php" class="btn-close" title="Kembali ke Landing Page">&times;</a>

    <div class="login-header">
      <h2><i class="fa-solid fa-square-parking"></i> E-PARKIR</h2>
      <p>Pasar Pundong Executive Parking System</p>
    </div>

    <form method="POST" action="">
      <div class="form-group">
        <label for="username"><i class="fa-solid fa-user me-1"></i> Username</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
      </div>
      
      <div class="form-group">
        <label for="password"><i class="fa-solid fa-lock me-1"></i> Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required>
      </div>

      <button type="submit" class="btn-luxury">
        <i class="fa-solid fa-right-to-bracket"></i> Masuk Sistem
      </button>
    </form>

    <div class="login-footer">
      Belum punya akun? <a href="register.php">Daftar sekarang</a>
    </div>
  </div>

  <!-- Modal Error -->
  <?php if (!empty($error)): ?>
    <div class="modal-overlay active" id="notifyModal">
      <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <div class="modal-title">Gagal Masuk</div>
        <div class="modal-message"><?php echo htmlspecialchars($error); ?></div>
        <button class="btn-modal error" onclick="closeModal()">Coba Lagi</button>
      </div>
    </div>
  <?php endif; ?>

  <!-- Modal Sukses -->
  <?php if ($loginSuccess): ?>
    <div class="modal-overlay active" id="notifyModal">
      <div class="modal-box">
        <div class="modal-icon">✅</div>
        <div class="modal-title">Login Berhasil</div>
        <div class="modal-message">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']); ?></strong>! Mengalihkan ke sistem...</div>
      </div>
    </div>
  <?php endif; ?>

  <script>
    function playNotificationSound() {
      const audio = document.getElementById('notifSound');
      if (audio) {
        audio.currentTime = 0;
        audio.play().catch(function(err) {
          console.log("Autoplay audio diblokir oleh browser:", err);
        });
      }
    }

    function closeModal() {
      document.getElementById('notifyModal')?.classList.remove('active');
    }

    document.addEventListener("DOMContentLoaded", function () {
      <?php if (!empty($error)): ?>
        playNotificationSound();
      <?php endif; ?>

      <?php if ($loginSuccess): ?>
        playNotificationSound();
        setTimeout(function() {
          window.location.href = "<?php echo $redirectTarget; ?>";
        }, 1200);
      <?php endif; ?>
    });
  </script>
</body>
</html>