<?php
// Cara pakai di tiap halaman:
//   $pageTitle = 'BELI PULSA';
//   $backUrl   = 'main.php';  // tujuan tombol back (default main.php, '' = tanpa tombol)
//   $showHeader = true;       // false untuk halaman Awal/Login
//   require 'includes/header.php';
if (!isset($pageTitle))  $pageTitle  = 'Telkomsel';
if (!isset($showHeader)) $showHeader = true;
if (!isset($backUrl))    $backUrl    = 'main.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?> - Telkomsel</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<?php if ($showHeader): ?>
<header class="topbar">
  <?php if ($backUrl): ?>
    <a class="topbar-back" href="<?= htmlspecialchars($backUrl) ?>" title="Kembali">&larr;</a>
  <?php endif; ?>
  <a class="topbar-icon" href="main.php" title="Beranda">&#128100;</a>
  <span class="topbar-title"><?= htmlspecialchars($pageTitle) ?></span>
  <input class="topbar-search" type="search" placeholder="Search">
  <a class="topbar-icon" href="logout.php" title="Keluar">&#9211;</a>
</header>
<?php endif; ?>
<main class="container">