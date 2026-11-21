<?php
require 'config.php';
requireLogin();
$user = currentUser();

$pageTitle = 'INDIHOME';
require 'includes/header.php';

$s = $pdo->prepare("SELECT * FROM indihome_accounts WHERE user_id = ? LIMIT 1");
$s->execute([$user['id']]);
$acc = $s->fetch();
?>
<?php if (!$acc): ?>
  <div class="card" style="max-width:420px;margin:40px auto">
    <p>Anda belum berlangganan IndiHome.</p>
    <a class="btn btn-small" style="margin-top:12px" href="main.php">Kembali</a>
  </div>
<?php else: ?>
  <div class="indi-grid">

    <!-- JARINGAN -->
    <section class="indi-col">
      <h3>JARINGAN</h3>
      <div class="indi-box">
        <span>ID Jaringan</span><b><?= htmlspecialchars($acc['network_id']) ?></b>
      </div>
      <div class="indi-box">
        <span>Kecepatan</span><b><?= $acc['speed_mbps'] ?> Mbps</b>
      </div>
      <div class="indi-box">
        <span>Status</span><b><?= ucfirst($acc['status']) ?></b>
      </div>
    </section>

    <!-- PEMAKAIAN -->
    <section class="indi-col">
      <h3>PEMAKAIAN</h3>
      <div class="indi-info">
        <small>Informasi pemakaian</small>
        <div class="big"><?= (float)$acc['usage_gb'] ?> GB</div>
        <small>digunakan bulan ini</small>
      </div>
    </section>

    <!-- LANGGANAN -->
    <section class="indi-col">
      <h3>LANGGANAN</h3>
      <div class="indi-box">
        <span>Paket</span><b><?= htmlspecialchars($acc['plan_name']) ?></b>
      </div>
      <div class="indi-box">
        <span>Tagihan / bulan</span><b><?= rupiah($acc['monthly_fee']) ?></b>
      </div>
    </section>

  </div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>