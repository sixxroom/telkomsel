<?php
// ============================================================
// PANEL ADMINISTRASI TELKOMSEL
// Alamat: http://localhost/telkomsel/admin/
// Terpisah dari portal pelanggan. Fungsi: tambah, ubah, dan hapus
// data pelanggan Halo/Simpati serta layanan IndiHome.
// ============================================================
require '../config.php';

// ---------- Autentikasi administrator ----------
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_ok']);
    header('Location: index.php'); exit;
}
$loginError = '';
if (($_POST['action'] ?? '') === 'login') {
    if (hash_equals(ADMIN_PASSWORD, (string)($_POST['password'] ?? ''))) {
        $_SESSION['admin_ok'] = true;
        header('Location: index.php'); exit;
    }
    $loginError = 'Kata sandi tidak sesuai.';
}
$isAdmin = !empty($_SESSION['admin_ok']);

// ---------- Fungsi bantu ----------
function pesan($text, $ok = true) {
    $_SESSION['admin_msg'] = ['text' => $text, 'ok' => $ok];
    header('Location: index.php'); exit;
}
function angka($v) { return max(0, (float)str_replace(',', '.', (string)$v)); }
function h($v)     { return htmlspecialchars((string)$v); }
function val($row, $key, $default = '') { return $row ? $row[$key] : $default; }

// Ambil & validasi isian form pelanggan (dipakai tambah & ubah)
function inputPelanggan() {
    $name  = trim($_POST['name'] ?? '');
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    if (substr($phone, 0, 2) === '62') $phone = '0' . substr($phone, 2);
    $card  = $_POST['card_type'] ?? '';

    if ($name === '' || substr($phone, 0, 1) !== '0' || strlen($phone) < 9
        || strlen($phone) > 15 || !in_array($card, ['halo', 'simpati'])) {
        return null;
    }
    return [
        'name'      => $name,
        'phone'     => $phone,
        'card'      => $card,
        'pulsa'     => (int)angka($_POST['pulsa'] ?? 0),
        'points'    => (int)angka($_POST['points'] ?? 0),
        'quota'     => angka($_POST['quota'] ?? 0),
        'emergency' => angka($_POST['emergency'] ?? 0),
    ];
}

// ---------- Proses aksi ----------
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // === Tambah pelanggan ===
    if ($action === 'add_user') {
        $d = inputPelanggan();
        if (!$d) pesan('Data tidak valid. Nomor harus diawali 0 (9-15 digit) dan seluruh kolom wajib terisi.', false);
        try {
            $pdo->prepare("INSERT INTO users
                (phone_number, name, card_type, pulsa_balance, quota_gb, emergency_quota_gb, points)
                VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$d['phone'], $d['name'], $d['card'], $d['pulsa'],
                           $d['quota'], $d['emergency'], $d['points']]);
        } catch (PDOException $e) {
            pesan($e->getCode() === '23000' ? 'Nomor telepon sudah terdaftar.' : 'Data gagal disimpan.', false);
        }
        pesan('Pelanggan ' . $d['phone'] . ' (' . strtoupper($d['card']) . ') berhasil didaftarkan.');
    }

    // === Ubah pelanggan ===
    if ($action === 'update_user') {
        $uid = (int)($_POST['id'] ?? 0);
        $d = inputPelanggan();
        if (!$d) pesan('Data tidak valid. Nomor harus diawali 0 (9-15 digit) dan seluruh kolom wajib terisi.', false);
        try {
            $pdo->prepare("UPDATE users SET phone_number = ?, name = ?, card_type = ?,
                pulsa_balance = ?, quota_gb = ?, emergency_quota_gb = ?, points = ?
                WHERE id = ?")
                ->execute([$d['phone'], $d['name'], $d['card'], $d['pulsa'],
                           $d['quota'], $d['emergency'], $d['points'], $uid]);
        } catch (PDOException $e) {
            pesan($e->getCode() === '23000' ? 'Nomor telepon sudah digunakan pelanggan lain.' : 'Perubahan gagal disimpan.', false);
        }
        pesan('Data pelanggan ' . $d['phone'] . ' berhasil diperbarui.');
    }

    // === Hapus pelanggan (beserta IndiHome & transaksinya) ===
    if ($action === 'delete_user') {
        $uid = (int)($_POST['id'] ?? 0);
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM indihome_accounts WHERE user_id = ?")->execute([$uid]);
            $pdo->prepare("DELETE FROM transactions WHERE user_id = ?")->execute([$uid]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            pesan('Data pelanggan gagal dihapus.', false);
        }
        pesan('Data pelanggan berhasil dihapus.');
    }

    // === Tambah / ubah layanan IndiHome ===
    if ($action === 'add_indihome' || $action === 'update_indihome') {
        $iid    = (int)($_POST['id'] ?? 0);
        $uid    = (int)($_POST['user_id'] ?? 0);
        $net    = trim($_POST['network_id'] ?? '');
        $plan   = trim($_POST['plan_name'] ?? '');
        $status = ($_POST['status'] ?? '') === 'nonaktif' ? 'nonaktif' : 'aktif';

        $cek = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $cek->execute([$uid]);
        if (!$cek->fetch() || $net === '' || $plan === '') {
            pesan('Pilih pelanggan, lalu lengkapi ID jaringan dan nama paket.', false);
        }
        // Satu pelanggan hanya boleh memiliki satu layanan IndiHome
        $ada = $pdo->prepare("SELECT id FROM indihome_accounts WHERE user_id = ? AND id <> ?");
        $ada->execute([$uid, $iid]);
        if ($ada->fetch()) {
            pesan('Pelanggan tersebut sudah memiliki layanan IndiHome.', false);
        }

        $speed = (int)angka($_POST['speed'] ?? 0);
        $usage = angka($_POST['usage'] ?? 0);
        $fee   = (int)angka($_POST['fee'] ?? 0);

        if ($action === 'add_indihome') {
            $pdo->prepare("INSERT INTO indihome_accounts
                (user_id, network_id, speed_mbps, usage_gb, plan_name, monthly_fee, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)")
                ->execute([$uid, $net, $speed, $usage, $plan, $fee, $status]);
            pesan('Layanan IndiHome berhasil didaftarkan.');
        }
        $pdo->prepare("UPDATE indihome_accounts SET user_id = ?, network_id = ?, speed_mbps = ?,
            usage_gb = ?, plan_name = ?, monthly_fee = ?, status = ? WHERE id = ?")
            ->execute([$uid, $net, $speed, $usage, $plan, $fee, $status, $iid]);
        pesan('Data layanan IndiHome berhasil diperbarui.');
    }

    // === Hapus layanan IndiHome ===
    if ($action === 'delete_indihome') {
        $pdo->prepare("DELETE FROM indihome_accounts WHERE id = ?")
            ->execute([(int)($_POST['id'] ?? 0)]);
        pesan('Layanan IndiHome berhasil dihapus.');
    }
}

// ---------- Data untuk tampilan ----------
$msg = $_SESSION['admin_msg'] ?? null;
unset($_SESSION['admin_msg']);

$users = $isAdmin ? $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll() : [];
$indi  = $isAdmin ? $pdo->query(
    "SELECT i.*, u.name, u.phone_number FROM indihome_accounts i
     JOIN users u ON u.id = i.user_id ORDER BY i.id DESC")->fetchAll() : [];

// Mode ubah: ?edit_user=ID atau ?edit_indihome=ID
$editUser = null; $editIndi = null;
if ($isAdmin && isset($_GET['edit_user'])) {
    $s = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $s->execute([(int)$_GET['edit_user']]);
    $editUser = $s->fetch() ?: null;
}
if ($isAdmin && isset($_GET['edit_indihome'])) {
    $s = $pdo->prepare("SELECT * FROM indihome_accounts WHERE id = ?");
    $s->execute([(int)$_GET['edit_indihome']]);
    $editIndi = $s->fetch() ?: null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel Administrasi Telkomsel</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; background: #1f2430; color: #e8eaf0; padding: 20px; }
  .wrap { max-width: 1100px; margin: 0 auto; }
  h1 { font-size: 20px; } h2 { font-size: 16px; margin-bottom: 4px; }
  .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
  .top a { color: #9db4ff; font-size: 13px; }
  .note { color: #99a; font-size: 12px; margin-bottom: 18px; line-height: 1.5; }
  .panel { background: #2a3040; border-radius: 10px; padding: 18px; margin-bottom: 20px; }
  .panel.editing { outline: 2px solid #f5b942; }
  .sub { color: #99a; font-size: 12px; margin-bottom: 14px; }
  .msg { padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
  .msg.ok { background: #1d5c3a; } .msg.err { background: #7a2530; }
  form.grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); align-items: end; }
  label { font-size: 12px; color: #aab; display: block; margin-bottom: 4px; }
  input, select { width: 100%; padding: 8px 10px; border-radius: 6px; border: 1px solid #455;
                  background: #1f2430; color: #fff; font-size: 14px; }
  button, .btn-link { padding: 9px 16px; border: 0; border-radius: 6px; background: #4f7cff;
           color: #fff; font-weight: bold; cursor: pointer; font-size: 14px;
           display: inline-block; text-decoration: none; }
  .actions { display: flex; gap: 8px; align-items: center; }
  .btn-link.ghost { background: #3a4256; }
  button.del  { background: #b8394a; padding: 5px 10px; font-size: 12px; }
  .btn-link.edit { background: #c98a1b; padding: 5px 10px; font-size: 12px; }
  .tbl { overflow-x: auto; margin-top: 16px; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #3a4256; white-space: nowrap; }
  th { color: #9aa3b8; font-weight: normal; font-size: 12px; }
  tr.row-edit td { background: #3a3526; }
  td.act { display: flex; gap: 6px; }
  .badge { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }
  .halo { background: #7b3fe4; } .simpati { background: #e6532e; }
  .login { max-width: 360px; margin: 80px auto; }
  .login input { margin-bottom: 12px; }
</style>
</head>
<body>
<div class="wrap">

<?php if (!$isAdmin): ?>
  <div class="panel login">
    <h2>Autentikasi Administrator</h2>
    <p class="sub">Masukkan kata sandi untuk mengakses panel administrasi.</p>
    <?php if ($loginError): ?><div class="msg err"><?= h($loginError) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="action" value="login">
      <input type="password" name="password" placeholder="Kata sandi administrator" required autofocus>
      <button type="submit" style="width:100%">Masuk</button>
    </form>
  </div>

<?php else: ?>
  <div class="top">
    <h1>Panel Administrasi Telkomsel</h1>
    <a href="?logout=1">Akhiri Sesi</a>
  </div>
  <p class="note">
    Panel administrasi ini beroperasi terpisah dari portal pelanggan.
    Perubahan data pada panel ini otomatis tersinkronisasi dengan halaman Login, Beranda, dan IndiHome di portal pelanggan.
  </p>

  <?php if ($msg): ?>
    <div class="msg <?= $msg['ok'] ? 'ok' : 'err' ?>"><?= h($msg['text']) ?></div>
  <?php endif; ?>

  <!-- ====== PELANGGAN HALO / SIMPATI ====== -->
  <section class="panel <?= $editUser ? 'editing' : '' ?>" id="form-user">
    <h2><?= $editUser ? 'Ubah Data Pelanggan' : 'Registrasi Pelanggan Halo / Simpati' ?></h2>
    <p class="sub">
      <?= $editUser ? 'Perbarui informasi pelanggan, saldo, poin, dan kuota.'
                    : 'Daftarkan nomor baru beserta saldo pulsa, poin, dan kuota awal.' ?>
    </p>
    <form method="post" class="grid">
      <input type="hidden" name="action" value="<?= $editUser ? 'update_user' : 'add_user' ?>">
      <?php if ($editUser): ?><input type="hidden" name="id" value="<?= $editUser['id'] ?>"><?php endif; ?>

      <div><label>Nama pelanggan</label>
        <input name="name" required value="<?= h(val($editUser, 'name')) ?>"></div>
      <div><label>Nomor telepon</label>
        <input name="phone" required placeholder="08xxxxxxxxxx" value="<?= h(val($editUser, 'phone_number')) ?>"></div>
      <div><label>Jenis kartu</label>
        <select name="card_type">
          <option value="simpati" <?= val($editUser, 'card_type') === 'simpati' ? 'selected' : '' ?>>Simpati</option>
          <option value="halo"    <?= val($editUser, 'card_type') === 'halo'    ? 'selected' : '' ?>>Halo</option>
        </select></div>
      <div><label>Saldo pulsa (Rp)</label>
        <input type="number" name="pulsa" min="0" value="<?= h(val($editUser, 'pulsa_balance', 0)) ?>"></div>
      <div><label>Poin loyalitas</label>
        <input type="number" name="points" min="0" value="<?= h(val($editUser, 'points', 0)) ?>"></div>
      <div><label>Kuota utama (GB)</label>
        <input name="quota" value="<?= h((float)val($editUser, 'quota_gb', 0)) ?>"></div>
      <div><label>Kuota darurat (GB)</label>
        <input name="emergency" value="<?= h((float)val($editUser, 'emergency_quota_gb', 0)) ?>"></div>
      <div class="actions">
        <button type="submit"><?= $editUser ? 'Perbarui Data' : 'Simpan Pelanggan' ?></button>
        <?php if ($editUser): ?><a class="btn-link ghost" href="index.php">Batal</a><?php endif; ?>
      </div>
    </form>

    <div class="tbl">
      <table>
        <tr><th>Nomor</th><th>Nama</th><th>Kartu</th><th>Saldo pulsa</th><th>Poin</th>
            <th>Kuota utama</th><th>Kuota darurat</th><th>Aksi</th></tr>
        <?php foreach ($users as $u): ?>
        <tr class="<?= ($editUser && $editUser['id'] == $u['id']) ? 'row-edit' : '' ?>">
          <td><?= h($u['phone_number']) ?></td>
          <td><?= h($u['name']) ?></td>
          <td><span class="badge <?= h($u['card_type']) ?>"><?= strtoupper(h($u['card_type'])) ?></span></td>
          <td><?= rupiah($u['pulsa_balance']) ?></td>
          <td><?= number_format($u['points'], 0, ',', '.') ?></td>
          <td><?= gb($u['quota_gb']) ?></td>
          <td><?= gb($u['emergency_quota_gb']) ?></td>
          <td class="act">
            <a class="btn-link edit" href="?edit_user=<?= $u['id'] ?>#form-user">Ubah</a>
            <form method="post" onsubmit="return confirm('Hapus pelanggan ini beserta data IndiHome dan riwayat transaksinya?')">
              <input type="hidden" name="action" value="delete_user">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button class="del" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?><tr><td colspan="8">Belum ada data pelanggan.</td></tr><?php endif; ?>
      </table>
    </div>
  </section>

  <!-- ====== LAYANAN INDIHOME ====== -->
  <section class="panel <?= $editIndi ? 'editing' : '' ?>" id="form-indihome">
    <h2><?= $editIndi ? 'Ubah Data Layanan IndiHome' : 'Registrasi Layanan IndiHome' ?></h2>
    <p class="sub">
      <?= $editIndi ? 'Perbarui informasi jaringan, pemakaian, dan langganan.'
                    : 'Hubungkan layanan IndiHome ke pelanggan yang sudah terdaftar.' ?>
    </p>
    <form method="post" class="grid">
      <input type="hidden" name="action" value="<?= $editIndi ? 'update_indihome' : 'add_indihome' ?>">
      <?php if ($editIndi): ?><input type="hidden" name="id" value="<?= $editIndi['id'] ?>"><?php endif; ?>

      <div><label>Pelanggan (pemilik)</label>
        <select name="user_id" required>
          <option value="">- Pilih pelanggan -</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= val($editIndi, 'user_id') == $u['id'] ? 'selected' : '' ?>>
              <?= h($u['phone_number']) ?> &ndash; <?= h($u['name']) ?>
            </option>
          <?php endforeach; ?>
        </select></div>
      <div><label>ID jaringan</label>
        <input name="network_id" required placeholder="IND-100234" value="<?= h(val($editIndi, 'network_id')) ?>"></div>
      <div><label>Kecepatan (Mbps)</label>
        <input type="number" name="speed" min="0" value="<?= h(val($editIndi, 'speed_mbps', 20)) ?>"></div>
      <div><label>Pemakaian bulan berjalan (GB)</label>
        <input name="usage" value="<?= h((float)val($editIndi, 'usage_gb', 0)) ?>"></div>
      <div><label>Nama paket</label>
        <input name="plan_name" required placeholder="Paket 50 Mbps" value="<?= h(val($editIndi, 'plan_name')) ?>"></div>
      <div><label>Tagihan bulanan (Rp)</label>
        <input type="number" name="fee" min="0" value="<?= h(val($editIndi, 'monthly_fee', 0)) ?>"></div>
      <div><label>Status layanan</label>
        <select name="status">
          <option value="aktif"    <?= val($editIndi, 'status') !== 'nonaktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="nonaktif" <?= val($editIndi, 'status') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select></div>
      <div class="actions">
        <button type="submit"><?= $editIndi ? 'Perbarui Data' : 'Simpan Layanan' ?></button>
        <?php if ($editIndi): ?><a class="btn-link ghost" href="index.php#form-indihome">Batal</a><?php endif; ?>
      </div>
    </form>

    <div class="tbl">
      <table>
        <tr><th>Pelanggan</th><th>ID jaringan</th><th>Kecepatan</th><th>Pemakaian</th>
            <th>Paket</th><th>Tagihan</th><th>Status</th><th>Aksi</th></tr>
        <?php foreach ($indi as $i): ?>
        <tr class="<?= ($editIndi && $editIndi['id'] == $i['id']) ? 'row-edit' : '' ?>">
          <td><?= h($i['phone_number']) ?> (<?= h($i['name']) ?>)</td>
          <td><?= h($i['network_id']) ?></td>
          <td><?= (int)$i['speed_mbps'] ?> Mbps</td>
          <td><?= gb($i['usage_gb']) ?></td>
          <td><?= h($i['plan_name']) ?></td>
          <td><?= rupiah($i['monthly_fee']) ?></td>
          <td><?= ucfirst(h($i['status'])) ?></td>
          <td class="act">
            <a class="btn-link edit" href="?edit_indihome=<?= $i['id'] ?>#form-indihome">Ubah</a>
            <form method="post" onsubmit="return confirm('Hapus layanan IndiHome ini?')">
              <input type="hidden" name="action" value="delete_indihome">
              <input type="hidden" name="id" value="<?= $i['id'] ?>">
              <button class="del" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$indi): ?><tr><td colspan="8">Belum ada data layanan IndiHome.</td></tr><?php endif; ?>
      </table>
    </div>
  </section>
<?php endif; ?>

</div>
</body>
</html>