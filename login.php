<?php
require 'config.php';
if (currentUser()) { header('Location: main.php'); exit; }

$pageTitle  = 'Login';
$showHeader = false;
require 'includes/header.php';
?>
<div class="auth-page">
  <div class="auth-box">
    <h2>Masuk ke akun Telkomsel</h2>
    <p class="sub">Silakan pilih jenis kartu Anda untuk melanjutkan</p>

    <a class="btn" href="masuk.php?type=simpati">MASUK SIMPATI</a>
    <a class="btn btn-outline" href="masuk.php?type=halo">MASUK HALO</a>
    <a class="back" href="index.php">&larr; Kembali</a>
  </div>
</div>
<?php require 'includes/footer.php'; ?>