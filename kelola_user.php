<?php
require_once "auth_check.php";
require_once "koneksi.php";

batasiAkses(['admin']);

// Ambil semua data user, diurutkan dari yang terbaru
$qUser = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user ASC");

// Pesan status dari proses_user.php (lewat query string)
$flash = $_GET['status'] ?? '';
$flash_messages = [
    'added'    => ['type' => 'success', 'text' => 'User baru berhasil ditambahkan.'],
    'updated'  => ['type' => 'success', 'text' => 'Data user berhasil diperbarui.'],
    'deleted'  => ['type' => 'success', 'text' => 'User berhasil dihapus.'],
    'toggled'  => ['type' => 'success', 'text' => 'Status user berhasil diubah.'],
    'self'     => ['type' => 'error',   'text' => 'Tidak bisa menghapus/menonaktifkan akun Anda sendiri.'],
    'dup'      => ['type' => 'error',   'text' => 'Username sudah digunakan, silakan pilih username lain.'],
    'error'    => ['type' => 'error',   'text' => 'Terjadi kesalahan, silakan coba lagi.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen User - Grand Pasar Executive Parking</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Style tambahan khusus halaman ini (tidak mengubah style.css utama) */
    .role-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    .role-admin      { background: rgba(212, 175, 55, 0.15); color: #d4af37; border: 1px solid rgba(212, 175, 55, 0.35); }
    .role-petugas    { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); }
    .role-owner      { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35); }
    .role-pengunjung { background: rgba(148, 163, 184, 0.15); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.35); }

    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      text-decoration: none;
      cursor: pointer;
      border: none;
    }
    .status-aktif   { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
    .status-nonaktif{ background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

    .flash-msg {
      padding: 14px 18px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }
    .flash-success { background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35); color: #10b981; }
    .flash-error   { background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); color: #f87171; }

    .btn-add-user {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      background: var(--gold-gradient);
      color: #0b0f17;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-add-user:hover { opacity: 0.9; }

    .btn-icon-action {
      background: transparent;
      border: 1px solid var(--border-card);
      color: var(--text-muted);
      border-radius: 6px;
      padding: 6px 10px;
      cursor: pointer;
      font-size: 0.8rem;
      margin-right: 6px;
      transition: all 0.2s ease;
    }
    .btn-icon-action:hover { border-color: var(--gold-primary); color: var(--gold-primary); }
    .btn-icon-danger:hover { border-color: #ef4444; color: #ef4444; }

    /* MODAL */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(5, 8, 14, 0.75);
      z-index: 999;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
      background: #121826;
      border: 1px solid var(--border-card);
      border-radius: 16px;
      padding: 30px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    .modal-box h3 {
      margin-bottom: 20px;
      font-size: 1.1rem;
      letter-spacing: 1px;
      color: #f1f5f9;
    }
    .modal-actions {
      display: flex;
      gap: 10px;
      margin-top: 22px;
    }
    .btn-cancel {
      flex: 1;
      padding: 12px;
      background: transparent;
      border: 1px solid var(--border-card);
      color: var(--text-muted);
      border-radius: 10px;
      cursor: pointer;
      font-size: 0.85rem;
    }
    .btn-cancel:hover { color: #fff; border-color: #fff; }
    .modal-box .btn-luxury { flex: 1.4; }
    small.hint { color: var(--text-muted); font-size: 0.72rem; display: block; margin-top: 4px; }
  </style>
</head>
<body>

  <div class="app-container">
    <?php if (file_exists("sidebar.php")) include "sidebar.php"; ?>

    <main class="main-content" style="padding: 30px;">
      <header class="hero-header" style="margin-bottom: 25px; display:flex; align-items:center; justify-content:space-between; flex-wrap: wrap; gap: 15px;">
        <div>
          <h1><i class="fa-solid fa-users-gear"></i> MANAJEMEN <span>USER</span></h1>
          <p>Kelola akun admin, petugas, dan owner sistem parkir.</p>
        </div>
        <button class="btn-add-user" onclick="bukaModalTambah()">
          <i class="fa-solid fa-plus"></i> Tambah User
        </button>
      </header>

      <?php if ($flash && isset($flash_messages[$flash])): ?>
        <div class="flash-msg flash-<?php echo $flash_messages[$flash]['type']; ?>">
          <i class="fa-solid fa-circle-info"></i> <?php echo $flash_messages[$flash]['text']; ?>
        </div>
      <?php endif; ?>

      <section class="panel">
        <div class="panel-title"><i class="fa-solid fa-address-card"></i> Daftar User (<?php echo mysqli_num_rows($qUser); ?>)</div>
        <div class="parking-table-wrapper" style="margin-top: 15px;">
          <table class="parking-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($qUser) > 0): ?>
                <?php while ($u = mysqli_fetch_assoc($qUser)): ?>
                <tr>
                  <td>#<?php echo str_pad($u['id_user'], 3, '0', STR_PAD_LEFT); ?></td>
                  <td><?php echo htmlspecialchars($u['nama_lengkap']); ?></td>
                  <td><?php echo htmlspecialchars($u['username']); ?></td>
                  <td><span class="role-badge role-<?php echo htmlspecialchars($u['role']); ?>"><?php echo htmlspecialchars($u['role']); ?></span></td>
                  <td>
                    <?php if ((int)$u['status_aktif'] === 1): ?>
                      <a href="proses_user.php?aksi=toggle&id=<?php echo $u['id_user']; ?>"
                         class="status-pill status-aktif"
                         onclick="return confirm('Nonaktifkan user ini? User tidak akan bisa login.');">
                         <i class="fa-solid fa-check"></i> Aktif
                      </a>
                    <?php else: ?>
                      <a href="proses_user.php?aksi=toggle&id=<?php echo $u['id_user']; ?>"
                         class="status-pill status-nonaktif"
                         onclick="return confirm('Aktifkan kembali user ini?');">
                         <i class="fa-solid fa-xmark"></i> Nonaktif
                      </a>
                    <?php endif; ?>
                  </td>
                  <td style="white-space: nowrap;">
                    <button type="button" class="btn-icon-action"
                      onclick='bukaModalEdit(<?php echo json_encode([
                          "id" => $u["id_user"],
                          "nama" => $u["nama_lengkap"],
                          "username" => $u["username"],
                          "role" => $u["role"]
                      ]); ?>)'>
                      <i class="fa-solid fa-pen"></i> Edit
                    </button>
                    <a href="proses_user.php?aksi=hapus&id=<?php echo $u['id_user']; ?>"
                       class="btn-icon-action btn-icon-danger"
                       style="text-decoration:none; display:inline-block;"
                       onclick="return confirm('Yakin ingin menghapus user \'<?php echo htmlspecialchars($u['username'], ENT_QUOTES); ?>\'? Aksi ini tidak bisa dibatalkan.');">
                      <i class="fa-solid fa-trash"></i> Hapus
                    </a>
                  </td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" style="text-align:center; color: var(--text-muted);">Belum ada data user.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <!-- MODAL TAMBAH / EDIT USER -->
  <div class="modal-overlay" id="modalUser">
    <div class="modal-box">
      <h3 id="modalTitle"><i class="fa-solid fa-user-plus"></i> Tambah User Baru</h3>
      <form action="proses_user.php" method="POST">
        <input type="hidden" name="aksi" id="formAksi" value="tambah">
        <input type="hidden" name="id_user" id="formIdUser" value="">

        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" id="formNama" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" id="formUsername" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Password <span id="passwordOptionalLabel" style="display:none;">(kosongkan jika tidak ingin mengubah)</span></label>
          <input type="password" name="password" id="formPassword" class="form-control" autocomplete="new-password">
          <small class="hint">Minimal 4 karakter.</small>
        </div>

        <div class="form-group">
          <label>Role</label>
          <select name="role" id="formRole" class="form-control" required>
            <option value="admin">Admin</option>
            <option value="petugas">Petugas</option>
            <option value="owner">Owner</option>
            <option value="pengunjung">Pengunjung</option>
          </select>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick="tutupModal()">Batal</button>
          <button type="submit" class="btn-luxury">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById('modalUser');

    function bukaModalTambah() {
      document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus"></i> Tambah User Baru';
      document.getElementById('formAksi').value = 'tambah';
      document.getElementById('formIdUser').value = '';
      document.getElementById('formNama').value = '';
      document.getElementById('formUsername').value = '';
      document.getElementById('formPassword').value = '';
      document.getElementById('formPassword').required = true;
      document.getElementById('formRole').value = 'petugas';
      document.getElementById('passwordOptionalLabel').style.display = 'none';
      modal.classList.add('active');
    }

    function bukaModalEdit(data) {
      document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-pen"></i> Edit User';
      document.getElementById('formAksi').value = 'edit';
      document.getElementById('formIdUser').value = data.id;
      document.getElementById('formNama').value = data.nama;
      document.getElementById('formUsername').value = data.username;
      document.getElementById('formPassword').value = '';
      document.getElementById('formPassword').required = false;
      document.getElementById('formRole').value = data.role;
      document.getElementById('passwordOptionalLabel').style.display = 'inline';
      modal.classList.add('active');
    }

    function tutupModal() {
      modal.classList.remove('active');
    }

    // Klik di luar box modal untuk menutup
    modal.addEventListener('click', function(e) {
      if (e.target === modal) tutupModal();
    });
  </script>

</body>
</html>