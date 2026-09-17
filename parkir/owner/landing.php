<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Parkir System - Informasi Aplikasi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), 
                        url('https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
        }
        .btn-custom-primary {
            background-color: #2563eb;
            color: white;
        }
        .btn-custom-primary:hover {
            background-color: #1d4ed8;
            color: white;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
                <span class="badge bg-primary p-2 rounded"><i class="fa-solid fa-p"></i></span>
                E-PARKIR SYSTEM
            </a>
            <div class="ms-auto">
                <a href="login.php" class="btn btn-custom-primary rounded-3 px-4 fw-bold btn-sm">
                    <i class="fa-solid fa-right-to-bracket me-1"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center text-md-start">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">Sistem Management Parkir</span>
                    <h1 class="display-5 fw-bold mb-3">Solusi Pengelolaan Parkir Digital & Terpadu</h1>
                    <p class="lead text-light mb-4 opacity-75">Mempermudah pemantauan kapasitas slot parkir, rekapitulasi data kendaraan, dan penghitungan pendapatan secara otomatis dan terintegrasi.</p>
                    <a href="login.php" class="btn btn-warning btn-lg fw-bold px-4 rounded-3 text-dark">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Masuk ke Sistem
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container py-3">
            <div class="text-center mb-5">
                <h3 class="fw-bold">Fitur Utama Sistem</h3>
                <p class="text-muted">Layanan yang tersedia di dalam Dashboard E-Parkir System</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4 rounded-4">
                        <div class="text-primary mb-3"><i class="fa-solid fa-chart-pie fa-3x"></i></div>
                        <h5 class="fw-bold">Monitoring Ringkasan</h5>
                        <p class="text-muted mb-0">Pantau statistik kendaraan masuk hari ini dan persentase slot parkir yang terpakai secara real-time.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4 rounded-4">
                        <div class="text-success mb-3"><i class="fa-solid fa-file-invoice-dollar fa-3x"></i></div>
                        <h5 class="fw-bold">Laporan Keuangan</h5>
                        <p class="text-muted mb-0">Perhitungan total pendapatan parkir harian terekap otomatis dalam sistem tanpa perlu catat manual.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 p-4 rounded-4">
                        <div class="text-warning mb-3"><i class="fa-solid fa-users-gear fa-3x"></i></div>
                        <h5 class="fw-bold">Multi Hak Akses</h5>
                        <p class="text-muted mb-0">Mendukung pengelolaan akun pengguna untuk level Administrator, Owner, dan Petugas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-3">
        <div class="container text-center">
            <small class="text-white-50">&copy; 2026 E-Parkir System. All rights reserved.</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>