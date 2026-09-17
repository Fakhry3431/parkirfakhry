<?php
/*
  index.php - Landing Page (Navigasi ke login.php & register.php)
*/
session_start();
require_once "koneksi.php";

$namaLokasi = "Pasar Modern Grand Central";

// 1. SIMPAN ULASAN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_review"])) {
    $nama = trim($_POST["nama"] ?? "");
    $rating = (int)($_POST["rating"] ?? 5);
    $komentar = trim($_POST["komentar"] ?? "");

    if (!empty($nama) && !empty($komentar) && $rating >= 1 && $rating <= 5) {
        $sqlCreate = "CREATE TABLE IF NOT EXISTS tb_ulasan (
            id_ulasan INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            rating INT NOT NULL,
            komentar TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($koneksi, $sqlCreate);

        $stmtIns = mysqli_prepare($koneksi, "INSERT INTO tb_ulasan (nama, rating, komentar) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmtIns, "sis", $nama, $rating, $komentar);
        mysqli_stmt_execute($stmtIns);
        
        header("Location: index.php?status=success");
        exit();
    }
}

// 2. DATA KAPASITAS REAL-TIME
$statsPublic = array();
$chartLabels = array();
$chartDataTerisi = array();

$resAreaPublic = mysqli_query($koneksi, "SELECT id_area, nama_area, kapasitas FROM tb_area_parkir");
if ($resAreaPublic) {
    while ($area = mysqli_fetch_assoc($resAreaPublic)) {
        $stmtCount = mysqli_prepare($koneksi, "SELECT COUNT(*) AS terisi FROM tb_kendaraan WHERE id_area = ? AND waktu_keluar IS NULL");
        mysqli_stmt_bind_param($stmtCount, "i", $area["id_area"]);
        mysqli_stmt_execute($stmtCount);
        $resCount = mysqli_stmt_get_result($stmtCount);
        $rowTerisi = mysqli_fetch_assoc($resCount);
        $terisi = (int)($rowTerisi["terisi"] ?? 0);

        $statsPublic[] = array(
            "id_area"   => $area["id_area"],
            "nama_area" => $area["nama_area"],
            "terisi"    => $terisi,
            "sisa"      => max(0, (int)$area["kapasitas"] - $terisi),
            "kapasitas" => (int)$area["kapasitas"]
        );

        $chartLabels[] = $area["nama_area"];
        $chartDataTerisi[] = $terisi;
    }
}

// 3. TARIF & ULASAN
$resTarifPublic = mysqli_query($koneksi, "SELECT * FROM tb_tarif");
$resUlasan = mysqli_query($koneksi, "SHOW TABLES LIKE 'tb_ulasan'");
$listUlasan = array();
if ($resUlasan && mysqli_num_rows($resUlasan) > 0) {
    $qReview = mysqli_query($koneksi, "SELECT * FROM tb_ulasan ORDER BY id_ulasan DESC LIMIT 6");
    if ($qReview) {
        while ($r = mysqli_fetch_assoc($qReview)) {
            $listUlasan[] = $r;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Executive Parking System - <?php echo htmlspecialchars($namaLokasi); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-main: #060911;
      --bg-card: rgba(15, 23, 42, 0.75);
      --border-gold: rgba(212, 175, 55, 0.25);
      --gold-grad: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%);
      --text-gold: #f3e5ab;
      --text-sub: #94a3b8;
    }

    body {
      background-color: var(--bg-main);
      color: #ffffff;
      font-family: 'Plus Jakarta Sans', sans-serif;
      line-height: 1.5;
      overflow-x: hidden;
    }

    .app-header {
      width: 100%;
      background: rgba(6, 9, 17, 0.9);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border-gold);
      position: sticky;
      top: 0;
      z-index: 999;
    }

    .app-nav {
      max-width: 1200px;
      margin: 0 auto;
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .brand-box { display: flex; align-items: center; gap: 12px; }

    .brand-icon {
      width: 38px; height: 38px;
      background: var(--gold-grad);
      color: #000; font-weight: 800; font-size: 1.1rem;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 0 12px rgba(212, 175, 55, 0.3);
    }

    .brand-name {
      font-size: 1.1rem; font-weight: 800; letter-spacing: 1px;
      background: var(--gold-grad);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .nav-actions { display: flex; gap: 10px; align-items: center; }

    .btn-gold {
      background: var(--gold-grad); color: #060911;
      padding: 8px 20px; border-radius: 30px;
      font-weight: 700; font-size: 0.85rem;
      text-decoration: none; border: none; cursor: pointer;
      display: inline-block; transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-gold:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
    }

    .btn-outline {
      background: transparent; color: #fff;
      border: 1px solid var(--border-gold);
      padding: 8px 18px; border-radius: 30px;
      font-weight: 600; font-size: 0.85rem;
      text-decoration: none; cursor: pointer; transition: all 0.2s;
      display: inline-block;
    }

    .btn-outline:hover { border-color: #d4af37; color: #d4af37; }

    .hero-container { position: relative; text-align: center; padding: 90px 20px 60px; overflow: hidden; min-height: 340px; display: flex; flex-direction: column; justify-content: center; }
    .hero-bg-video {
      position: absolute; top: 0; left: 0; width: 100%; height: 100%;
      object-fit: cover; z-index: 0;
    }
    .hero-bg-overlay {
      position: absolute; top: 0; left: 0; width: 100%; height: 100%;
      background: linear-gradient(180deg, rgba(6,9,17,0.55) 0%, rgba(6,9,17,0.85) 85%, var(--bg-main) 100%);
      z-index: 1;
    }
    .hero-container > * { position: relative; z-index: 2; }
    .hero-badge {
      display: inline-block; padding: 4px 14px; border-radius: 20px;
      background: rgba(212, 175, 55, 0.1); border: 1px solid rgba(212, 175, 55, 0.3);
      color: var(--text-gold); font-size: 0.7rem; font-weight: 700;
      letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;
    }
    .hero-title { font-size: 2.2rem; font-weight: 800; line-height: 1.2; }
    .hero-title span { background: var(--gold-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .hero-desc { color: var(--text-sub); font-size: 0.9rem; margin-top: 8px; }

    .main-wrapper { max-width: 1200px; margin: 0 auto; padding: 30px 24px; }
    .sec-head { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
    .sec-bar { width: 4px; height: 20px; background: var(--gold-grad); border-radius: 2px; }
    .sec-title { font-size: 1.1rem; font-weight: 700; color: var(--text-gold); }

    .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 35px; }
    .glass-card { background: var(--bg-card); border: 1px solid var(--border-gold); border-radius: 14px; padding: 20px; backdrop-filter: blur(10px); }
    .slot-num { font-size: 2.2rem; font-weight: 800; color: #10b981; margin-right: 6px; }

    .layout-split { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 35px; }
    @media (max-width: 820px) { .layout-split { grid-template-columns: 1fr; } }

    .chart-wrapper { position: relative; height: 220px; width: 100%; }
    .rate-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed rgba(255, 255, 255, 0.1); }
    .rate-price { font-weight: 700; background: var(--gold-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

    .form-input {
      width: 100%; padding: 10px 12px; background: rgba(6, 9, 17, 0.8);
      border: 1px solid var(--border-gold); border-radius: 8px;
      color: #fff; font-size: 0.85rem; margin-bottom: 10px; outline: none;
    }

    .star-select { display: flex; gap: 6px; font-size: 1.4rem; cursor: pointer; color: #475569; margin-bottom: 10px; }
    .star-select span.active { color: #fbbf24; }
    .review-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; padding: 14px; margin-bottom: 12px; }

    .alert { padding: 8px 12px; border-radius: 6px; font-size: 0.8rem; margin-bottom: 12px; }
    .alert.success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; }
    .alert.error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #ef4444; }

    footer { text-align: center; padding: 20px; color: var(--text-sub); font-size: 0.8rem; border-top: 1px solid rgba(255, 255, 255, 0.05); margin-top: 40px; }
  </style>
</head>
<body>

  <header class="app-header">
    <div class="app-nav">
      <div class="brand-box">
        <div class="brand-icon">P</div>
        <div class="brand-name">GRAND PASAR</div>
      </div>
      <div class="nav-actions">
        <a href="register.php" class="btn-outline">Daftar Account</a>
        <a href="login.php" class="btn-gold">Login</a>
      </div>
    </div>
  </header>

  <section class="hero-container">
    <video class="hero-bg-video" autoplay muted loop playsinline poster="assets/foto-parkiran-pasar-pundong.jpg">
      <source src="assets/video-parkiran-pasar-pundong.mp4.mp4" type="video/mp4">
      <img src="assets/foto-parkiran-pasar-pundong.jpg.jpg" alt="Background Parkir" style="width: 100%; height: 100%; object-fit: cover;">
    </video>
    <div class="hero-bg-overlay"></div>
    <div class="hero-badge">✦ Executive Parking System ✦</div>
    <h1 class="hero-title"><?php echo htmlspecialchars($namaLokasi); ?><br><span>EXECUTIVE PARKING</span></h1>
    <p class="hero-desc">Monitoring visual kapasitas kendaraan, informasi tarif resmi, dan ulasan pengunjung secara real-time.</p>
  </section>

  <div class="main-wrapper">

    <div class="sec-head"><div class="sec-bar"></div><div class="sec-title">Kapasitas Area Real-Time</div></div>
    <div class="card-grid">
      <?php if (!empty($statsPublic)): ?>
        <?php foreach ($statsPublic as $s): ?>
          <div class="glass-card">
            <div style="font-weight: 700; font-size: 1rem; margin-bottom: 4px;"><?php echo htmlspecialchars($s["nama_area"]); ?></div>
            <div style="color: var(--text-sub); font-size: 0.75rem; margin-bottom: 8px;">Sisa Slot Kosong</div>
            <div style="display: flex; align-items: baseline;">
              <span class="slot-num"><?php echo $s["sisa"]; ?></span>
              <span style="color: var(--text-sub); font-size: 0.85rem;">/ <?php echo $s["kapasitas"]; ?> Slot Total</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="glass-card" style="grid-column: 1 / -1; text-align: center; color: var(--text-sub);">Data area parkir belum tersedia.</div>
      <?php endif; ?>
    </div>

    <div class="layout-split">
      <div>
        <div class="sec-head"><div class="sec-bar"></div><div class="sec-title">Visual Distribusi Kendaraan</div></div>
        <div class="glass-card"><div class="chart-wrapper"><canvas id="publicVehicleChart"></canvas></div></div>
      </div>
      <div>
        <div class="sec-head"><div class="sec-bar"></div><div class="sec-title">Skema Tarif Resmi</div></div>
        <div class="glass-card">
          <?php if ($resTarifPublic && mysqli_num_rows($resTarifPublic) > 0): ?>
            <?php while ($t = mysqli_fetch_assoc($resTarifPublic)): 
                $jenis = strtolower(trim($t["jenis_kendaraan"] ?? ""));
                $icon = (strpos($jenis, 'motor') !== false) ? "🛵" : "🚘";
            ?>
              <div class="rate-item">
                <div><span><?php echo $icon; ?></span> <strong><?php echo htmlspecialchars(ucwords($t["jenis_kendaraan"] ?? "Kendaraan")); ?></strong></div>
                <div class="rate-price">Rp <?php echo number_format((float)($t["tarif_per_jam"] ?? 0), 0, ",", "."); ?> / jam</div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p style="color: var(--text-sub); text-align: center;">Daftar tarif belum diatur.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="sec-head"><div class="sec-bar"></div><div class="sec-title">Ulasan & Rating Pengunjung</div></div>
    <div class="layout-split" style="align-items: start;">
      <div class="glass-card">
        <h3 style="font-size: 0.95rem; margin-bottom: 12px; color: #fff;">Berikan Ulasan Anda</h3>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
          <div class="alert success">✓ Ulasan Anda berhasil terkirim.</div>
        <?php endif; ?>
        <form method="POST" action="index.php">
          <input type="text" name="nama" class="form-input" placeholder="Nama Anda" required>
          <label style="font-size: 0.75rem; color: var(--text-sub); display: block;">Rating Bintang:</label>
          <div class="star-select" id="starPicker">
            <span data-value="1">★</span><span data-value="2">★</span><span data-value="3">★</span><span data-value="4">★</span><span data-value="5" class="active">★</span>
          </div>
          <input type="hidden" name="rating" id="ratingInput" value="5">
          <textarea name="komentar" rows="3" class="form-input" placeholder="Tulis komentar..." required></textarea>
          <button type="submit" name="submit_review" class="btn-gold" style="width: 100%;">Kirim Ulasan</button>
        </form>
      </div>

      <div>
        <?php if (!empty($listUlasan)): ?>
          <?php foreach ($listUlasan as $u): ?>
            <div class="review-card">
              <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                <strong style="font-size: 0.85rem; color: #fff;"><?php echo htmlspecialchars($u['nama']); ?></strong>
                <span style="color: #fbbf24; font-size: 0.8rem;"><?php echo str_repeat("★", (int)$u['rating']); ?></span>
              </div>
              <p style="color: #cbd5e1; font-size: 0.8rem;">"<?php echo htmlspecialchars($u['komentar']); ?>"</p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="glass-card" style="text-align: center; color: var(--text-sub);">Belum ada ulasan.</div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <footer>
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($namaLokasi); ?>. All rights reserved.
  </footer>

  <script>
    const stars = document.querySelectorAll('#starPicker span');
    const ratingInput = document.getElementById('ratingInput');
    stars.forEach((star) => {
      star.addEventListener('click', () => {
        const val = star.getAttribute('data-value');
        ratingInput.value = val;
        stars.forEach((s, idx) => {
          if (idx < val) s.classList.add('active');
          else s.classList.remove('active');
        });
      });
    });

    document.addEventListener("DOMContentLoaded", function () {
      const ctx = document.getElementById('publicVehicleChart').getContext('2d');
      const labels = <?php echo json_encode($chartLabels); ?>;
      const dataTerisi = <?php echo json_encode($chartDataTerisi); ?>;

      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: labels.length ? labels : ['Kosong'],
          datasets: [{
            data: dataTerisi.length && dataTerisi.some(val => val > 0) ? dataTerisi : [1],
            backgroundColor: ['#d4af37', '#10b981', '#3b82f6', '#ef4444'],
            borderColor: '#060911',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { color: '#94a3b8', font: { size: 11 } } }
          },
          cutout: '70%'
        }
      });
    });
  </script>

</body>
</html>