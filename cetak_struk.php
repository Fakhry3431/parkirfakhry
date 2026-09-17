<?php
require_once "auth_check.php";
require_once "koneksi.php";

batasiAkses(['admin', 'petugas', 'owner']);

$id_parkir = intval($_GET['id'] ?? 0);

// Ambil Detail Transaksi Parkir
$query = mysqli_query($koneksi, "
    SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, a.nama_area, tr.tarif_per_jam, u.nama_lengkap as nama_petugas
    FROM tb_transaksi t
    JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
    JOIN tb_area_parkir a ON t.id_area = a.id_area
    JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif
    LEFT JOIN tb_user u ON t.id_user = u.id_user
    WHERE t.id_parkir = '$id_parkir'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data transaksi tidak ditemukan.";
    exit;
}

// Status pembayaran: 'keluar' berarti sudah dibayar & selesai, 'masuk' berarti masih di dalam (belum dihitung biayanya)
$sudahBayar = ($data['status'] === 'keluar');

// Format Data untuk QRIS & Struk
$nomor_tiket = "TKT-" . str_pad($data['id_parkir'], 6, '0', STR_PAD_LEFT);
$biaya_total = $sudahBayar ? (float)($data['biaya_total'] ?? 0) : null;

// Generate URL QR Code QRIS Dinamis (hanya dibutuhkan saat sudah dibayar)
$qr_url = "";
if ($sudahBayar) {
    $qris_data = "PAY-PARKIR-" . $nomor_tiket . "-RP" . $biaya_total;
    $qr_url    = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qris_data);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?php echo $sudahBayar ? "Struk Pembayaran" : "Tiket Masuk"; ?> - <?php echo $nomor_tiket; ?></title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Courier New', Courier, monospace;
    }
    body {
      width: 80mm;
      margin: 0 auto;
      padding: 10px;
      background: #fff;
      color: #000;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
    .header p { margin: 2px 0; font-size: 10px; }
    .line { border-bottom: 1px dashed #000; margin: 8px 0; }
    table { width: 100%; font-size: 11px; border-collapse: collapse; }
    table td { padding: 3px 0; vertical-align: top; }
    .qris-box {
      margin-top: 10px;
      text-align: center;
      border: 1px solid #000;
      padding: 8px;
    }
    .qris-box img { width: 110px; height: 110px; }
    .qris-title { font-weight: bold; font-size: 12px; margin-bottom: 4px; }
    .notice-box {
      margin-top: 10px;
      text-align: center;
      border: 1px dashed #000;
      padding: 8px;
      font-size: 10px;
    }
    .footer { font-size: 9px; margin-top: 12px; }

    @media print {
      body { width: 100%; padding: 0; }
      .no-print { display: none; }
    }
  </style>
</head>
<body onload="window.print()">

  <div class="no-print" style="margin-bottom: 15px; text-align: center;">
    <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer;">🖨️ Cetak Struk</button>
    <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer;">❌ Tutup</button>
  </div>

  <div class="header text-center">
    <h2>GRAND PASAR</h2>
    <p>Jl. Pasar Pundong No. 01, Bantul</p>
    <p><?php echo $sudahBayar ? "STRUK PEMBAYARAN PARKIR" : "TIKET MASUK PARKIR"; ?></p>
  </div>

  <div class="line"></div>

  <table>
    <tr>
      <td>No. Tiket</td>
      <td>:</td>
      <td class="text-right"><strong><?php echo $nomor_tiket; ?></strong></td>
    </tr>
    <tr>
      <td>Plat Nomor</td>
      <td>:</td>
      <td class="text-right"><strong><?php echo htmlspecialchars($data['plat_nomor']); ?></strong></td>
    </tr>
    <tr>
      <td>Jenis / Area</td>
      <td>:</td>
      <td class="text-right"><?php echo strtoupper($data['jenis_kendaraan']); ?> (<?php echo htmlspecialchars($data['nama_area']); ?>)</td>
    </tr>
    <tr>
      <td>Waktu Masuk</td>
      <td>:</td>
      <td class="text-right"><?php echo date('d/m/Y H:i', strtotime($data['waktu_masuk'])); ?></td>
    </tr>
    <?php if ($sudahBayar): ?>
    <tr>
      <td>Waktu Keluar</td>
      <td>:</td>
      <td class="text-right"><?php echo date('d/m/Y H:i', strtotime($data['waktu_keluar'])); ?></td>
    </tr>
    <tr>
      <td>Durasi</td>
      <td>:</td>
      <td class="text-right"><?php echo $data['durasi_jam']; ?> Jam</td>
    </tr>
    <?php endif; ?>
    <tr>
      <td>Petugas</td>
      <td>:</td>
      <td class="text-right"><?php echo htmlspecialchars($data['nama_petugas'] ?? 'System'); ?></td>
    </tr>
  </table>

  <div class="line"></div>

  <?php if ($sudahBayar): ?>
    <!-- Sudah diproses keluar & dibayar: tampilkan total biaya + QRIS -->
    <table>
      <tr style="font-size: 13px; font-weight: bold;">
        <td>TOTAL BIAYA</td>
        <td>:</td>
        <td class="text-right">Rp <?php echo number_format($biaya_total, 0, ',', '.'); ?></td>
      </tr>
      <tr>
        <td>Status</td>
        <td>:</td>
        <td class="text-right">LUNAS</td>
      </tr>
    </table>

    <div class="qris-box">
      <div class="qris-title">SCAN UNTUK BAYAR (QRIS)</div>
      <img src="<?php echo $qr_url; ?>" alt="QRIS Parkir">
      <div style="font-size: 9px; margin-top: 4px;">Mendukung: BCA, Mandiri, BRI, GoPay, OVO, ShopeePay</div>
    </div>

    <div class="footer text-center">
      <p>Terima kasih atas kunjungan Anda.</p>
      <p>Simpan struk ini sebagai bukti pembayaran sah.</p>
    </div>
  <?php else: ?>
    <!-- Baru masuk, belum dibayar: jangan tampilkan total/QRIS, cukup info tiket -->
    <div class="notice-box">
      Tarif dihitung otomatis saat kendaraan <strong>keluar</strong>.<br>
      Simpan tiket ini untuk proses pembayaran.
    </div>

    <div class="footer text-center">
      <p>Selamat berbelanja / beraktivitas.</p>
      <p>Barang hilang/rusak di luar tanggung jawab pengelola.</p>
    </div>
  <?php endif; ?>

</body>
</html>