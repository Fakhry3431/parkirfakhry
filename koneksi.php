<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_parkir";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}

// Tambahan fungsi untuk mencatat log aktivitas pengguna (mencegah Undefined Function Error)
if (!function_exists('catatLog')) {
    function catatLog($koneksi, $aktivitas) {
        // Mengambil id_user dari session login (default ke 1 jika session tidak ditemukan)
        $id_user = $_SESSION['id_user'] ?? 1;
        $waktu   = date('Y-m-d H:i:s');
        
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_log_aktivitas (id_user, aktivitas, waktu_aktivitas) VALUES (?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iss", $id_user, $aktivitas, $waktu);
            mysqli_stmt_execute($stmt);
        }
    }
}
?>