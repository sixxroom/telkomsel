<?php
require 'config.php';
if (currentUser()) { header('Location: main.php'); exit; }

$pageTitle  = 'Beranda';
$showHeader = false;
require 'includes/header.php';

$pesan = 'Mohon masukkan akun anda atau masukkan nomor telepon';
?>
<nav class="landing-nav">
  <span class="brand">TELKOMSEL</span>
  <div class="links">
    <a onclick="showPopup('<?= $pesan ?>', 'login.php')">Beli Paket</a>
    <a onclick="showPopup('<?= $pesan ?>', 'login.php')">Beli Pulsa</a>
    <a onclick="showPopup('<?= $pesan ?>', 'login.php')">Roaming</a>
    <a onclick="showPopup('<?= $pesan ?>', 'login.php')">IndiHome</a>
  </div>
  <a class="btn" href="login.php">Masuk</a>
</nav>

<section class="hero">
  <div class="hero-text">
    <h1>Jaringan yang bikin dunia dalam genggaman</h1>
    <p>Beli paket internet, pulsa, roaming, dan tukar hadiah dengan mudah dalam satu tempat.</p>
    <a class="btn" href="login.php">Mulai Sekarang</a>
  </div>
</section>
<?php require 'includes/footer.php'; ?>