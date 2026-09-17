<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Fungsi proteksi halaman berdasarkan role tertentu
function batasiAkses($allowed_roles = []) {
    $user_role = $_SESSION['role'] ?? '';
    if (!in_array($user_role, $allowed_roles)) {
        echo "<script>
            alert('Akses Ditolak! Anda tidak memiliki izin membuka halaman ini.');
            window.location.href = 'index.php';
        </script>";
        exit();
    }
}
?>