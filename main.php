<?php
require 'config.php';
requireLogin();
$user = currentUser();

$pageTitle = 'MAIN SCREEN';
$backUrl   = '';   // Main Screen tanpa tombol back
require 'includes/header.php';

// Daftar 7 menu: [label, ikon, file tujuan]
$menus = [
  ['Internet',   '&#127760;', 'beli_internet.php'],
  ['Darurat',    '&#9888;',   'beli_darurat.php'],
  ['Pulsa',      '&#128241;', 'beli_pulsa.php'],
  ['Roaming',    '&#9992;',   'beli_roaming.php'],
  ['Hadiah',     '&#127873;', 'tukar_hadiah.php'],
  ['IndiHome',   '&#127968;', 'indihome.php'],
  ['Menu Lain',  '&#9776;',   'menu_lain.php'],
];
?>
<div class="logo-big" style="font-size:40px"><span>TELKOM</span>SEL</div>

<!-- INFORMASI PRIBADI -->
<section class="profile-card">
  <div class="profile-name">
    Halo, <?= htmlspecialchars($user['name']) ?>
    <small><?= strtoupper($user['card_type']) ?></small>
  </div>
  <div class="profile-number"><?= htmlspecialchars($user['phone_number']) ?></div>
  <div class="profile-stats">
    <div><span>Pulsa</span><b><?= rupiah($user['pulsa_balance']) ?></b></div>
    <div><span>Poin</span><b><?= number_format($user['points'], 0, ',', '.') ?></b></div>
    <div><span>Sisa Kuota Pribadi</span><b><?= gb($user['quota_gb']) ?></b></div>
    <div><span>Sisa Kuota Darurat</span><b><?= gb($user['emergency_quota_gb']) ?></b></div>
  </div>
</section>

<!-- 7 TOMBOL MENU -->
<nav class="menu-circles">
<?php foreach ($menus as [$label, $icon, $file]): ?>
  <?php if (file_exists(__DIR__ . '/' . $file)): ?>
    <a class="circle-item" href="<?= $file ?>">
  <?php else: ?>
    <a class="circle-item" onclick="showPopup('Halaman <?= $label ?> dibuat di langkah berikutnya')">
  <?php endif; ?>
      <span class="circle"><?= $icon ?></span>
      <span class="circle-label"><?= $label ?></span>
    </a>
<?php endforeach; ?>
</nav>
<?php require 'includes/footer.php'; ?>