<?php
require 'config.php';
requireLogin();
$user = currentUser();

$pageTitle = 'BELI PAKET DARURAT';
require 'includes/header.php';

// Nama kolom sesuai desain Figma
$columns = [
  'darurat' => 'Paket Darurat',
  'pinjam'  => 'Pinjam Paket',
  'runmap'  => 'RunMap Paket',
];

// Ambil semua paket darurat, kelompokkan per tipe
$rows = $pdo->query("SELECT * FROM emergency_packages ORDER BY type, price")->fetchAll();
$byType = [];
foreach ($rows as $r) { $byType[$r['type']][] = $r; }

// Informasi pembelian paket darurat terakhir milik user ini
$s = $pdo->prepare(
  "SELECT t.created_at, e.name, e.info
   FROM transactions t
   JOIN emergency_packages e ON e.id = t.product_id
   WHERE t.user_id = ? AND t.product_type = 'darurat' AND t.status = 'sukses'
   ORDER BY t.created_at DESC LIMIT 1"
);
$s->execute([$user['id']]);
$last = $s->fetch();

$c = $pdo->prepare(
  "SELECT COUNT(*) FROM transactions
   WHERE user_id = ? AND product_type = 'darurat' AND status = 'sukses'"
);
$c->execute([$user['id']]);
$total = (int)$c->fetchColumn();
?>
<!-- BANNER INFORMASI -->
<section class="emg-banner">
  <h2>Informasi tentang sisa paket darurat</h2>
  <div class="emg-quota">
    <span>Sisa kuota darurat</span><b><?= gb($user['emergency_quota_gb']) ?></b>
  </div>
  <?php if ($last): ?>
    <p>Paket terakhir: <b><?= htmlspecialchars($last['name']) ?></b>
       (<?= htmlspecialchars($last['info']) ?>)</p>
    <p>Dibeli pada <?= date('d-m-Y H:i', strtotime($last['created_at'])) ?>
       &bull; Total pembelian: <?= $total ?>x</p>
  <?php else: ?>
    <p>Anda belum pernah membeli paket darurat.</p>
  <?php endif; ?>
</section>

<!-- 3 KOLOM PAKET -->
<section class="emg-panel">
<?php foreach ($columns as $type => $title): ?>
  <div class="emg-col">
    <div class="emg-title"><?= $title ?></div>
    <?php foreach ($byType[$type] ?? [] as $p): ?>
      <a class="emg-card" href="payment_paket.php?type=darurat&id=<?= $p['id'] ?>">
        <b><?= htmlspecialchars($p['name']) ?></b>
        <span><?= htmlspecialchars($p['info']) ?></span>
        <em><?= rupiah($p['price']) ?></em>
        <small class="earn">+<?= earnedPoints('darurat', $p['price']) ?> poin</small>
      </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>
</section>
<?php require 'includes/footer.php'; ?>