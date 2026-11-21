<?php
// Halaman uji: buka http://localhost/telkomsel/test.php
require 'config.php';
$pageTitle = 'TES KONEKSI';
require 'includes/header.php';

$jumlahUser = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$paket      = $pdo->query("SELECT * FROM packages LIMIT 3")->fetchAll();
?>
<div class="logo-big"><span>TELKOM</span>SEL</div>
<p style="text-align:center;margin-bottom:20px">
  Koneksi database berhasil. Jumlah user: <b><?= $jumlahUser ?></b>
</p>

<div class="grid">
<?php foreach ($paket as $p): ?>
  <div class="card">
    <div class="tag"><?= $p['valid_days'] ?> hari</div>
    <div class="box"><?= htmlspecialchars($p['name']) ?></div>
    <div class="price"><?= rupiah($p['price']) ?></div>
    <button class="btn btn-small" onclick="showPopup('Tes pop up berhasil')">Beli</button>
  </div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>