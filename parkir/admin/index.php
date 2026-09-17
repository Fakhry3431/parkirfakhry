<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    require_once 'landingpag.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Parkir</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: #0f172a; color: white; }
        .sidebar a { color: #94a3b8; text-decoration: none; padding: 14px 20px; display: flex; align-items: center; border-radius: 8px; margin: 4px 12px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #1e293b; color: #38bdf8; font-weight: 600; }
        .card-custom { border-radius: 14px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .hero-banner {
            background: linear-gradient(135deg, #2563eb, #1d4ed8), url('https://images.unsplash.com/photo-1590674899484-d5640e854abe?q=80&w=1000&auto=format&fit=crop');
            background-size: cover;
            background-blend-mode: overlay;
            color: white;
            border-radius: 16px;
        }
        .avatar-img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-2 p-0 sidebar">
            <div class="p-3 text-center border-bottom border-secondary">
                <h4 class="m-0 text-warning fw-bold"><i class="fa-solid fa-square-parking"></i> E-Parkir</h4>
                <small class="text-muted">Administrator Area</small>
            </div>
            <div class="py-3">
                <a href="#" class="active"><i class="fa-solid fa-gauge me-3"></i> Dashboard</a>
                <a href="#"><i class="fa-solid fa-users me-3"></i> Kelola User</a>
                <a href="#"><i class="fa-solid fa-car me-3"></i> Data Parkir</a>
                <a href="#"><i class="fa-solid fa-receipt me-3"></i> Laporan Keuangan</a>
                <hr class="border-secondary mx-3">
                <a href="landing.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-3"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <!-- Header Topbar -->
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                <h4 class="m-0 fw-bold text-dark"><i class="fa-solid fa-chart-line text-primary me-2"></i> Sistem Ringkasan</h4>
                <div class="d-flex align-items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama']); ?>&background=0D8ABC&color=fff" class="avatar-img" alt="User Profile">
                    <div>
                        <h6 class="m-0 fw-bold"><?= $_SESSION['nama']; ?></h6>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Online (Admin)</span>
                    </div>
                </div>
            </div>

            <!-- Hero Banner Sambutan -->
            <div class="hero-banner p-4 mb-4 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-2">Selamat Datang Kembali, <?= $_SESSION['nama']; ?>! 👋</h2>
                        <p class="opacity-90 m-0">Sistem parkir berjalan normal. Anda dapat memantau data transaksi dan kapasitas kendaraan secara real-time melalui panel ini.</p>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="fa-solid fa-car-tunnel fa-6x opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- Kartu Info Statistik -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card card-custom bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase text-muted fw-bold">Parkir Masuk Hari Ini</small>
                                <h2 class="m-0 fw-bold text-primary">128 <span class="fs-6 text-muted">Unit</span></h2>
                            </div>
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                                <i class="fa-solid fa-car fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase text-muted fw-bold">Pendapatan Hari Ini</small>
                                <h2 class="m-0 fw-bold text-success">Rp 420.000</h2>
                            </div>
                            <div class="bg-success-subtle text-success p-3 rounded-circle">
                                <i class="fa-solid fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-uppercase text-muted fw-bold">Kapasitas Slot Terpakai</small>
                                <h2 class="m-0 fw-bold text-warning">65%</h2>
                            </div>
                            <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                                <i class="fa-solid fa-square-parking fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>