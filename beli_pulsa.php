<?php
require 'config.php';
requireLogin();

$pageTitle = 'BELI INTERNET';
require 'includes/header.php';

$packages = $pdo->query(
    "SELECT * FROM packages WHERE is_active = 1 ORDER BY price"
)->fetchAll();
?>
<div class="logo-big" style="font-size:40px"><span>TELKOM</span>SEL</div>

<div class="grid">
<?php foreach ($packages as $p): ?>
  <div class="card">
    <div class="tag"><?= $p['valid_days'] ?> hari</div>
    <div class="box"><?= (float)$p['quota_gb'] ?> GB</div>
    <div><?= htmlspecialchars($p['name']) ?></div>
    <div class="price"><?= rupiah($p['price']) ?></div>
    <small class="earn">+<?= earnedPoints('paket', $p['price']) ?> poin</small>
    <a class="btn btn-small" href="payment_paket.php?type=paket&id=<?= $p['id'] ?>">Beli</a>
  </div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>