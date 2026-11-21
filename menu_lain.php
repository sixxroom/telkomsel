<?php
require 'config.php';
requireLogin();

$pageTitle = 'MENU';
require 'includes/header.php';

$menus = $pdo->query("SELECT * FROM other_menus ORDER BY id")->fetchAll();
?>
<div class="other-grid">
<?php foreach ($menus as $m): ?>
  <?php $aksi = $m['is_active'] ? '' : 'onclick="showMaintenance()"'; ?>
  <div class="other-item" <?= $aksi ?>>
    <span class="other-logo"><?= htmlspecialchars(mb_substr($m['label'], 0, 1)) ?></span>
    <span><?= htmlspecialchars($m['label']) ?></span>
  </div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>