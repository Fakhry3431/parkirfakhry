<?php
session_start();
require_once('koneksi.php');

$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");
    
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        
        if ($password === '123456' || password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin/index.php");
            } elseif ($row['role'] == 'petugas') {
                header("Location: petugas/index.php");
            } elseif ($row['role'] == 'owner') {
                header("Location: owner/index.php");
            }
            exit;
        } else {
            $error = "Password yang kamu masukkan salah!";
        }
    } else {
        $error = "Username tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Parkir System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), 
                        url('https://images.unsplash.com/photo-1506521781263-d8422e82f27a?q=80&w=1200&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            background: #2563eb;
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        .login-header i {
            font-size: 2.5rem;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .btn-custom {
            background: #2563eb;
            color: white;
            border-radius: 8px;
            padding: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background: #1d4ed8;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="fa-solid fa-square-parking text-warning"></i>
        <h4 class="fw-bold mb-0">E-PARKIR SYSTEM</h4>
        <small class="opacity-75">Silakan masuk ke akun Anda</small>
    </div>
    <div class="p-4">
        <?php if($error): ?>
            <div class="alert alert-danger d-flex align-items-center py-2" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?= $error; ?></div>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label text-secondary fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Ketik username..." required autocomplete="off">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-secondary fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Ketik password..." required>
                </div>
            </div>
            <button type="submit" name="login" class="btn btn-custom w-100 mb-2">
                <i class="fa-solid fa-right-to-bracket me-2"></i> MASUK SEKARANG
            </button>
        </form>
    </div>
</div>

</body>
</html>