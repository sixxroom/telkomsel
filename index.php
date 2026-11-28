<?php
require 'config.php';
if (currentUser()) { header('Location: main.php'); exit; }

$pageTitle  = 'Beranda';
$showHeader = false;
require 'includes/header.php';

$pesan = 'Mohon masukkan akun anda atau masukkan nomor telepon';
?>

<div class="lp-wrap">
  <div class="lp-box">

    <nav class="lp-nav">
      <span class="lp-brand">TELKOMSEL</span>
      <span class="lp-links">
        <a onclick="showPopup('<?= $pesan ?>', 'login.php')">Beli Paket</a>&nbsp;&nbsp;
        <a onclick="showPopup('<?= $pesan ?>', 'login.php')">Beli Pulsa</a>&nbsp;&nbsp;
        <a onclick="showPopup('<?= $pesan ?>', 'login.php')">Roaming</a>&nbsp;&nbsp;
        <a onclick="showPopup('<?= $pesan ?>', 'login.php')">IndiHome</a>
      </span>
      <a class="lp-masuk" href="login.php">Masuk</a>
    </nav>

    <section class="lp-hero">
      <div class="lp-text">
        <h1>Jaringan yang bikin dunia dalam genggaman</h1>
        <p>Beli paket internet, pulsa, roaming, dan tukar hadiah dengan mudah dalam satu tempat.</p>
        <a href="login.php">Mulai Sekarang</a>
      </div>
    </section>

  </div>
</div>
<?php require 'includes/footer.php'; ?>