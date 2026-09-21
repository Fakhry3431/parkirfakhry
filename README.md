# 🅿️ E-Parkir - Grand Pasar Executive Parking

**E-Parkir** adalah sistem manajemen operasional dan pemrosesan transaksi parkir berbasis web yang dirancang untuk mengelola alur kendaraan masuk, perhitungan biaya durasi secara otomatis, cetak tiket/struk dengan QR Code/QRIS, serta pembatasan hak akses berbasis peran (*role-based access*).

🌐 **Live Demo:** [http://parkirsaya.free.nf/](http://parkirsaya.free.nf/)
# Wireframe (perencanaan)
https://raw.githubusercontent.com/Fakhry3431/parkirfakhry/refs/heads/main/Code_Generated_Image.jpg
---

## 🚀 Fitur Utama

* **🔐 Authentication & Multi-Role Access Control:**
  * **Admin:** Akses penuh ke seluruh konfigurasi sistem, kelola user, tarif, dan laporan.
  * **Petugas:** Mengelola transaksi parkir masuk, proses pembayaran/keluar, dan cetak tiket/struk.
  * **Owner:** Melihat laporan transaksi dan rekapitulasi operasional.
* **🚗 Manajeman Transaksi Parkir:**
  * Pencatatan kendaraan masuk (Plat nomor, Jenis, Warna, Area Parkir).
  * Pengecekan otomatis plat nomor yang terdaftar di database.
  * Update otomatis sisa kapasitas slot area parkir secara *real-time*.
* **⏱️ Perhitungan Biaya Otomatis:**
  * Penghitungan durasi parkir berbasis jam (pembulatan ke atas dengan batas minimal 1 jam).
  * Pengkalkulasian total biaya berdasarkan tarif per jam sesuai jenis kendaraan.
* **🖨️ Cetak Tiket & Struk QR Code/QRIS:**
  * Generasi QR Code otomatis untuk tiket masuk.
  * Generasi QRIS dynamic untuk pembacaan pembayaran saat kendaraan keluar.
  * Tampilan cetak responsif yang siap dicetak ke thermal printer (80mm).

---

## 🛠️ Teknologi & Stack yang Digunakan

* **Language (Backend):** PHP (Native)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
* **Iconry & UI:** FontAwesome 6, Custom Executive Theme Layout
* **QR Code API:** QR Server API (`api.qrserver.com`)
* **Hosting / Server Environment:** InfinityFree (Apache Server), Windows XAMPP Local Development

---

## 🗄️ Struktur Database (`db_parkir`)

Sistem terdiri dari 5 tabel utama yang saling berelasi:

1. `tb_user`: Menyimpan data akun pengguna (`id_user`, `username`, `password`, `nama_lengkap`, `role`).
2. `tb_kendaraan`: Menyimpan data profil kendaraan (`id_kendaraan`, `plat_nomor`, `jenis_kendaraan`, `warna`, `id_user`).
3. `tb_area_parkir`: Menyimpan data kuota area (`id_area`, `nama_area`, `kapasitas`, `terisi`).
4. `tb_tarif`: Menyimpan standar tarif parkir (`id_tarif`, `jenis_kendaraan`, `tarif_per_jam`).
5. `tb_transaksi`: Menyimpan log aktivitas parkir (`id_parkir`, `id_kendaraan`, `id_tarif`, `id_area`, `id_user`, `waktu_masuk`, `waktu_keluar`, `durasi_jam`, `biaya_total`, `status`).

---

## ⚙️ Petunjuk Instalasi Lokal (XAMPP / Windows)

1. **Clone / Download Repositori:**
   Pindahkan folder proyek ke direktori server lokal Anda (misal: `C:\xampp\htdocs\e-parkir`).

2. **Konfigurasi Database:**
   * Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
   * Buat database baru bernama `db_parkir`.
   * Import file database SQL proyek Anda ke dalam `db_parkir`.

3. **Konfigurasi Koneksi (`koneksi.php`):**
   Sesuaikan kredensial koneksi database Anda:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "db_parkir";

   $koneksi = mysqli_connect($host, $user, $pass, $db);
