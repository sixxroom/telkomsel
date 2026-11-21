<?php
require 'config.php';
requireLogin();
$user = currentUser();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rewardId = (int)($_POST['reward_id'] ?? 0);
    try {
        $pdo->beginTransaction();

        // Kunci baris user dan hadiah agar poin & stok tidak dobel terpotong
        $s = $pdo->prepare("SELECT points FROM users WHERE id = ? FOR UPDATE");
        $s->execute([$user['id']]);
        $poin = (int)$s->fetchColumn();

        $s = $pdo->prepare("SELECT * FROM rewards WHERE id = ? FOR UPDATE");
        $s->execute([$rewardId]);
        $rw = $s->fetch();

        if (!$rw) {
            $pdo->rollBack();
            $error = 'Hadiah tidak ditemukan.';
        } elseif ($rw['stock'] < 1) {
            $pdo->rollBack();
            $error = 'Stok hadiah habis.';
        } elseif ($poin < $rw['points_cost']) {
            $pdo->rollBack();
            $error = 'Poin Anda tidak cukup.';
        } else {
            $pdo->prepare("UPDATE users SET points = points - ? WHERE id = ?")
                ->execute([$rw['points_cost'], $user['id']]);
            $pdo->prepare("UPDATE rewards SET stock = stock - 1 WHERE id = ?")
                ->execute([$rewardId]);
            // total = jumlah poin yang dipakai
            $pdo->prepare("INSERT INTO transactions
                (user_id, product_type, product_id, payment_method_id, total, status)
                VALUES (?, 'hadiah', ?, NULL, ?, 'sukses')")
                ->execute([$user['id'], $rewardId, $rw['points_cost']]);
            $pdo->commit();

            flashDone('Penukaran Selesai', [
                ['Hadiah',        $rw['name']],
                ['Poin terpakai', number_format($rw['points_cost'], 0, ',', '.')],
                ['Sisa poin',     number_format($poin - $rw['points_cost'], 0, ',', '.')],
            ]);
            header('Location: tukar_hadiah.php');
            exit;
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = 'Terjadi kesalahan, coba lagi.';
    }
    $user = currentUser();   // muat ulang data terbaru
}

$pageTitle = 'TUKAR HADIAH';
require 'includes/header.php';

$rewards = $pdo->query("SELECT * FROM rewards ORDER BY points_cost")->fetchAll();
?>
<div class="logo-big" style="font-size:40px"><span>TELKOM</span>SEL</div>

<p class="points-info">
  Poin Anda: <b><?= number_format($user['points'], 0, ',', '.') ?></b>
</p>

<?php if ($error): ?>
  <div class="alert" style="max-width:420px;margin:0 auto 16px;text-align:center">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<div class="grid">
<?php foreach ($rewards as $r):
    $habis  = $r['stock'] < 1;
    $kurang = $user['points'] < $r['points_cost'];
?>
  <div class="card">
    <div class="img-placeholder">&#127873;</div>
    <b><?= htmlspecialchars($r['name']) ?></b>
    <div class="price"><?= number_format($r['points_cost'], 0, ',', '.') ?> poin</div>
    <small>Stok: <?= $r['stock'] ?></small>
    <form method="post" style="margin-top:8px">
      <input type="hidden" name="reward_id" value="<?= $r['id'] ?>">
      <button class="btn btn-small" type="submit" <?= ($habis || $kurang) ? 'disabled' : '' ?>>
        <?= $habis ? 'Stok habis' : ($kurang ? 'Poin kurang' : 'Tukar') ?>
      </button>
    </form>
  </div>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>