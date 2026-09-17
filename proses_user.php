<?php
require_once "auth_check.php";
require_once "koneksi.php";

batasiAkses(['admin']);

$id_user_login = $_SESSION['id_user'] ?? 0;

// Ambil aksi dari POST (form tambah/edit) atau GET (link hapus/toggle)
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

// Role yang diperbolehkan (whitelist, mencegah role sembarangan disuntik lewat form)
$allowed_roles = ['admin', 'petugas', 'owner', 'pengunjung'];

function cekUsernameDipakai($koneksi, $username, $exclude_id = null) {
    if ($exclude_id) {
        $stmt = mysqli_prepare($koneksi, "SELECT id_user FROM tb_user WHERE username = ? AND id_user != ?");
        mysqli_stmt_bind_param($stmt, "si", $username, $exclude_id);
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id_user FROM tb_user WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_num_rows($result) > 0;
}

switch ($aksi) {

    // ================= TAMBAH USER =================
    case 'tambah':
        $nama     = trim($_POST['nama_lengkap'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'petugas';

        if (!in_array($role, $allowed_roles)) $role = 'petugas';

        if ($nama === '' || $username === '' || strlen($password) < 4) {
            header("Location: kelola_user.php?status=error");
            exit();
        }

        if (cekUsernameDipakai($koneksi, $username)) {
            header("Location: kelola_user.php?status=dup");
            exit();
        }

        // Catatan: password disimpan plain text mengikuti pola data yang sudah ada di sistem ini.
        // Disarankan ke depannya migrasi ke password_hash() + password_verify() di login.php.
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES (?, ?, ?, ?, 1)");
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $password, $role);

        if (mysqli_stmt_execute($stmt)) {
            catatLog($koneksi, "Menambahkan user baru: {$username} ({$role})");
            header("Location: kelola_user.php?status=added");
        } else {
            header("Location: kelola_user.php?status=error");
        }
        exit();

    // ================= EDIT USER =================
    case 'edit':
        $id       = (int)($_POST['id_user'] ?? 0);
        $nama     = trim($_POST['nama_lengkap'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'petugas';

        if (!in_array($role, $allowed_roles)) $role = 'petugas';

        if ($id <= 0 || $nama === '' || $username === '') {
            header("Location: kelola_user.php?status=error");
            exit();
        }

        if (cekUsernameDipakai($koneksi, $username, $id)) {
            header("Location: kelola_user.php?status=dup");
            exit();
        }

        if ($password !== '' && strlen($password) >= 4) {
            // Update termasuk password baru
            $stmt = mysqli_prepare($koneksi, "UPDATE tb_user SET nama_lengkap=?, username=?, password=?, role=? WHERE id_user=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $nama, $username, $password, $role, $id);
        } else {
            // Update tanpa mengubah password
            $stmt = mysqli_prepare($koneksi, "UPDATE tb_user SET nama_lengkap=?, username=?, role=? WHERE id_user=?");
            mysqli_stmt_bind_param($stmt, "sssi", $nama, $username, $role, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            catatLog($koneksi, "Mengubah data user: {$username} (ID #{$id})");
            header("Location: kelola_user.php?status=updated");
        } else {
            header("Location: kelola_user.php?status=error");
        }
        exit();

    // ================= HAPUS USER =================
    case 'hapus':
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header("Location: kelola_user.php?status=error");
            exit();
        }

        // Cegah admin menghapus akunnya sendiri
        if ($id === (int)$id_user_login) {
            header("Location: kelola_user.php?status=self");
            exit();
        }

        $stmt = mysqli_prepare($koneksi, "DELETE FROM tb_user WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            catatLog($koneksi, "Menghapus user ID #{$id}");
            header("Location: kelola_user.php?status=deleted");
        } else {
            header("Location: kelola_user.php?status=error");
        }
        exit();

    // ================= TOGGLE STATUS AKTIF =================
    case 'toggle':
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header("Location: kelola_user.php?status=error");
            exit();
        }

        // Cegah admin menonaktifkan akunnya sendiri
        if ($id === (int)$id_user_login) {
            header("Location: kelola_user.php?status=self");
            exit();
        }

        $stmt = mysqli_prepare($koneksi, "UPDATE tb_user SET status_aktif = 1 - status_aktif WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            catatLog($koneksi, "Mengubah status aktif user ID #{$id}");
            header("Location: kelola_user.php?status=toggled");
        } else {
            header("Location: kelola_user.php?status=error");
        }
        exit();

    default:
        header("Location: kelola_user.php");
        exit();
}