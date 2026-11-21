<?php
require 'config.php';
if (currentUser()) { header('Location: main.php'); exit; }

// Jenis kartu dari URL: masuk.php?type=halo atau masuk.php?type=simpati
$type = $_GET['type'] ?? '';
if (!in_array($type, ['halo', 'simpati'])) {
    header('Location: login.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Bersihkan nomor: buang selain angka, ubah 62xxx -> 0xxx
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    if (substr($phone, 0, 2) === '62') $phone = '0' . substr($phone, 2);

    if ($phone === '') {
        $error = 'Nomor tidak boleh kosong.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE phone_number = ? AND card_type = ?"
        );
        $stmt->execute([$phone, $type]);
        $user = $stmt->fetch();

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header('Location: main.php'); exit;
        }
        $error = 'Nomor ' . strtoupper($type) . ' tidak ditemukan.';
    }
}

$label      = strtoupper($type);
$pageTitle  = "Masuk $label";
$showHeader = false;
require 'includes/header.php';
?>
<div class="auth-page">
  <div class="auth-box auth-<?= $type ?>">
    <h2>Masuk ke akun <?= $label ?></h2>
    <p class="sub">Cek kuota, nikmati fitur favorit, dan bayarkan pesanan terbaik</p>

    <?php if ($error): ?>
      <div class="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <!-- Kolom nomor: pil merah tua, sesuai Figma -->
      <input class="input-red" type="tel" name="phone" required
             inputmode="numeric" autocomplete="tel"
             placeholder="MASUKKAN NOMOR <?= $label ?>"
             value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      <!-- Tombol MASUK: outline, sesuai Figma -->
      <button class="btn btn-outline" type="submit">MASUK</button>
    </form>

    <a class="back" href="login.php">&larr; Ganti jenis kartu</a>
  </div>
</div>
<?php require 'includes/footer.php'; ?>