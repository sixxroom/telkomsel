<?php
require 'config.php';
requireLogin();

$pageTitle = 'BELI PULSA';
require 'includes/header.php';

$options = $pdo->query("SELECT * FROM pulsa_options ORDER BY nominal")->fetchAll();
?>
<div class="logo-big" style="font-size:40px"><span>TELKOM</span>SEL</div>

<div class="grid">
<?php foreach ($options as $o): ?>
  <div class="card">
    <div class="tag">Pulsa</div>
    <div class="box"><?= number_format($o['nominal'], 0, ',', '.') ?></div>
    <div class="price"><?= rupiah($o['price']) ?></div>
    <small class="earn">+<?= earnedPoints('pulsa', $o['price']) ?> poin</small>
    <a class="btn btn-small" href="payment_pulsa.php?id=<?= $o['id'] ?>">Beli</a>
  </div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>