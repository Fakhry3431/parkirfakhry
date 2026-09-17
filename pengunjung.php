<?php
session_start();
require_once "koneksi.php";

// Cek autentikasi dan otorisasi role pengunjung
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pengunjung') {
    echo "<script>alert('Akses Ditolak! Anda harus login sebagai pengunjung.'); window.location='login.php';</script>";
    exit();
}

// Ambil data session
$id_user       = $_SESSION['id_user'] ?? 0;
$nama_lengkap  = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Pengunjung';
$username_user = $_SESSION['username'] ?? '';

// Fitur pencarian riwayat berdasarkan plat nomor
$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = trim($_GET['cari']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pengunjung - E-Parkir Pasar Pundong</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background-color: #121212;
      color: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
      background-color: #1e1e1e !important;
      border-bottom: 1px solid #333;
    }
    .card-custom {
      background: #1e1e1e;
      border: 1px solid #333;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    }
    .text-gold { color: #f1c40f; }
    .btn-gold {
      background-color: #f1c40f;
      color: #000;
      font-weight: bold;
    }
    .btn-gold:hover {
      background-color: #d4ac0d;
      color: #000;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark px-4">
    <div class="container-fluid">
      <a class="navbar-brand text-gold fw-bold" href="#">
        <i class="fa-solid fa-square-parking"></i> E-PARKIR <span class="text-white fs-6">| Portal Pengunjung</span>
      </a>
      <div class="d-flex align-items-center">
        <span class="text-light me-3 d-none d-md-inline">
          <i class="fa-solid fa-user-circle text-gold"></i> Halo, <strong><?php echo htmlspecialchars($nama_lengkap); ?></strong>
        </span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container my-5">
    
    <div class="row mb-4">
      <div class="col-md-12">
        <div class="card card-custom p-4 text-center">
          <h2 class="text-gold mb-2"><i class="fa-solid fa-car-side"></i> Selamat Datang di Pasar Pundong Parking</h2>
          <p class="text-muted mb-0">Pantau status kendaraan, cek tarif resmi, dan lihat informasi zona parkir Anda dengan mudah di sini.</p>
        </div>
      </div>
    </div>

    <!-- Feature Cards -->
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card card-custom h-100 p-3 text-center">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <div class="text-gold fs-1 mb-3"><i class="fa-solid fa-tags"></i></div>
              <h5 class="card-title text-white">Tarif Parkir Resmi</h5>
              <p class="card-text text-muted small">Informasi rincian biaya parkir per jam untuk kendaraan roda dua dan roda empat.</p>
            </div>
            <button class="btn btn-gold btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalTarif">
              <i class="fa-solid fa-eye"></i> Lihat Tarif
            </button>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-custom h-100 p-3 text-center">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <div class="text-gold fs-1 mb-3"><i class="fa-solid fa-map-location-dot"></i></div>
              <h5 class="card-title text-white">Zona Pasar Pundong</h5>
              <p class="card-text text-muted small">Peta pembagian area parkir terstruktur menjadi Kloter A (Mobil) dan Kloter B (Motor).</p>
            </div>
            <button class="btn btn-gold btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalInfo">
              <i class="fa-solid fa-circle-info"></i> Detail Zona
            </button>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-custom h-100 p-3 text-center">
          <div class="card-body d-flex flex-column justify-content-between">
            <div>
              <div class="text-gold fs-1 mb-3"><i class="fa-solid fa-headset"></i></div>
              <h5 class="card-title text-white">Pusat Bantuan</h5>
              <p class="card-text text-muted small">Kendala karcis hilang atau pertanyaan seputar penitipan kendaraan hubungi pos jaga utama.</p>
            </div>
            <button class="btn btn-gold btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalBantuan">
              <i class="fa-solid fa-phone"></i> Hubungi Pos
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Vehicle Records -->
    <div class="row mt-5" id="riwayat">
      <div class="col-md-12">
        <div class="card card-custom p-4">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="text-gold m-0"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat & Status Kendaraan</h4>
            
            <form method="GET" action="" class="d-flex gap-2">
              <input type="text" name="cari" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Cari Plat Nomor..." value="<?php echo htmlspecialchars($keyword); ?>">
              <button type="submit" class="btn btn-gold btn-sm"><i class="fa-solid fa-search"></i></button>
            </form>
          </div>

          <div class="table-responsive">
            <table class="table table-dark table-striped table-hover align-middle border-secondary">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Plat Nomor</th>
                  <th>Jenis Kendaraan</th>
                  <th>Jam Masuk</th>
                  <th>Jam Keluar</th>
                  <th>Biaya</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if (!empty($keyword)) {
                    $keyword_param = "%$keyword%";
                    $stmt_tabel = mysqli_prepare($koneksi, "SELECT * FROM tb_kendaraan WHERE plat_nomor LIKE ? ORDER BY id_kendaraan DESC");
                    mysqli_stmt_bind_param($stmt_tabel, "s", $keyword_param);
                    mysqli_stmt_execute($stmt_tabel);
                    $result_tabel = mysqli_stmt_get_result($stmt_tabel);
                } else {
                    $result_tabel = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan ORDER BY id_kendaraan DESC LIMIT 5");
                }

                if ($result_tabel && mysqli_num_rows($result_tabel) > 0) {
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($result_tabel)) {
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td><strong>" . htmlspecialchars($data['plat_nomor'] ?? '-') . "</strong></td>";
                        echo "<td>" . htmlspecialchars($data['jenis_kendaraan'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($data['jam_masuk'] ?? '-') . "</td>";
                        echo "<td>" . (!empty($data['jam_keluar']) ? htmlspecialchars($data['jam_keluar']) : '-') . "</td>";
                        echo "<td>" . (!empty($data['biaya']) ? 'Rp ' . number_format($data['biaya'], 0, ',', '.') : '-') . "</td>";
                        echo "<td>";
                        if (empty($data['jam_keluar'])) {
                            echo '<span class="badge bg-warning text-dark">Sedang Parkir</span>';
                        } else {
                            echo '<span class="badge bg-success">Selesai</span>';
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted py-3'>Belum ada data riwayat kendaraan ditemukan.</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Modals -->
  <div class="modal fade" id="modalTarif" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white border border-secondary">
        <div class="modal-header border-secondary">
          <h5 class="modal-title text-gold"><i class="fa-solid fa-tags"></i> Daftar Tarif Resmi</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <ul class="list-group list-group-flush bg-transparent">
            <li class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
              <span><i class="fa-solid fa-motorcycle text-gold me-2"></i> Roda Dua (Kloter B)</span>
              <strong>Rp 2.000 / Jam</strong>
            </li>
            <li class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-center">
              <span><i class="fa-solid fa-car text-gold me-2"></i> Roda Empat (Kloter A)</span>
              <strong>Rp 5.000 / Jam</strong>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalInfo" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white border border-secondary">
        <div class="modal-header border-secondary">
          <h5 class="modal-title text-gold"><i class="fa-solid fa-map-location-dot"></i> Zonasi Parkir Pasar Pundong</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted">Area parkir Pasar Pundong terbagi menjadi:</p>
          <ul class="mb-0">
            <li><strong>Kloter A:</strong> Zona parkir kendaraan roda empat (Mobil).</li>
            <li><strong>Kloter B:</strong> Zona parkir kendaraan roda dua (Motor).</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="modalBantuan" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white border border-secondary">
        <div class="modal-header border-secondary">
          <h5 class="modal-title text-gold"><i class="fa-solid fa-headset"></i> Pusat Bantuan</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small">Jika Anda mengalami kendala karcis hilang atau konfirmasi kendaraan keluar, silakan hubungi:</p>
          <p class="mb-1"><i class="fa-solid fa-phone-volume text-gold me-2"></i> Pos Jaga Utama: <strong>(0274) 123456</strong></p>
          <p class="mb-0"><i class="fa-solid fa-envelope text-gold me-2"></i> Email: <strong>support@eparkir-pundong.com</strong></p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>