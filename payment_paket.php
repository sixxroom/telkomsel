<?php
require 'config.php';
requireLogin();
$user = currentUser();

// ?type=paket (Internet) | darurat (Paket Darurat) | roaming (Roaming)
$type = $_GET['type'] ?? 'paket';
$id   = (int)($_GET['id'] ?? 0);

function getProduct(PDO $pdo, string $type, int $id) {
    if ($type === 'paket') {
        $s = $pdo->prepare("SELECT name, quota_gb, valid_days, price
                            FROM packages WHERE id = ? AND is_active = 1");
        $s->execute([$id]);
        $r = $s->fetch();
        if (!$r) return null;
        return ['name'      => $r['name'],
                'detail'    => gb($r['quota_gb']) . ' / ' . $r['valid_days'] . ' hari',
                'price'     => (int)$r['price'],
                'back'      => 'beli_internet.php',
                'quota'     => (float)$r['quota_gb'],
                'quota_col' => 'quota_gb'];
    }
    if ($type === 'darurat') {
        $s = $pdo->prepare("SELECT name, info, quota_gb, price FROM emergency_packages WHERE id = ?");
        $s->execute([$id]);
        $r = $s->fetch();
        if (!$r) return null;
        return ['name'      => $r['name'],
                'detail'    => $r['info'],
                'price'     => (int)$r['price'],
                'back'      => 'beli_darurat.php',
                'quota'     => (float)$r['quota_gb'],
                'quota_col' => 'emergency_quota_gb'];
    }
    if ($type === 'roaming') {
        $s = $pdo->prepare("SELECT r.name, r.valid_days, r.price, c.name AS country
                            FROM roaming_packages r
                            JOIN countries c ON c.id = r.country_id
                            WHERE r.id = ?");
        $s->execute([$id]);
        $r = $s->fetch();
        if (!$r) return null;
        return ['name'      => $r['name'],
                'detail'    => 'Negara: ' . $r['country'] . ' / ' . $r['valid_days'] . ' hari',
                'price'     => (int)$r['price'],
                'back'      => 'beli_roaming.php',
                'quota'     => 0,
                'quota_col' => null];
    }
    return null;
}

$product = getProduct($pdo, $type, $id);
if (!$product) { header('Location: main.php'); exit; }

$poinDapat = earnedPoints($type, $product['price']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // method_id kosong = bayar dengan pulsa; terisi = metode lain (ABC, RBI, dst.)
    $methodId   = (int)($_POST['method_id'] ?? 0);
    $useOther   = false;
    $methodName = 'Pulsa';

    if ($methodId > 0) {
        $m = $pdo->prepare("SELECT id, name FROM payment_methods
                            WHERE id = ? AND group_name IN ('mbanking','bank')");
        $m->execute([$methodId]);
        $row = $m->fetch();
        if ($row) { $useOther = true; $methodName = $row['name']; }
        else      { $error = 'Metode pembayaran tidak valid.'; }
    }

    if (!$error) {
        try {
            $pdo->beginTransaction();

            $s = $pdo->prepare("SELECT pulsa_balance FROM users WHERE id = ? FOR UPDATE");
            $s->execute([$user['id']]);
            $saldo = (int)$s->fetchColumn();

            if (!$useOther && $saldo < $product['price']) {
                $pdo->rollBack();
                $error = 'Pulsa tidak cukup. Saldo Anda ' . rupiah($saldo) . '.';
            } else {
                $saldoBaru = $saldo;
                if (!$useOther) {
                    $pdo->prepare("UPDATE users SET pulsa_balance = pulsa_balance - ? WHERE id = ?")
                        ->execute([$product['price'], $user['id']]);
                    $saldoBaru = $saldo - $product['price'];
                }

                // Tambahkan poin dari pembelian ini
                $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ?")
                    ->execute([$poinDapat, $user['id']]);

                // Tambahkan kuota ke akun (kuota pribadi / kuota darurat)
                if ($product['quota_col'] && $product['quota'] > 0) {
                    $col = $product['quota_col'];   // dari daftar tetap di atas, aman
                    $pdo->prepare("UPDATE users SET $col = $col + ? WHERE id = ?")
                        ->execute([$product['quota'], $user['id']]);
                }

                $pdo->prepare("INSERT INTO transactions
                    (user_id, product_type, product_id, payment_method_id, total, status)
                    VALUES (?, ?, ?, ?, ?, 'sukses')")
                    ->execute([$user['id'], $type, $id,
                               $useOther ? $methodId : null, $product['price']]);

                $pdo->commit();

                $rows = [
                    ['Produk',      $product['name']],
                    ['Detail',      $product['detail']],
                    ['Total bayar', rupiah($product['price'])],
                    ['Metode',      $methodName],
                ];
                if (!$useOther) { $rows[] = ['Sisa pulsa', rupiah($saldoBaru)]; }
                $rows[] = ['Poin didapat', '+' . number_format($poinDapat, 0, ',', '.')];
                $rows[] = ['Total poin',   number_format($user['points'] + $poinDapat, 0, ',', '.')];
                flashDone('Pembayaran Selesai', $rows);
                header('Location: main.php');
                exit;
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Terjadi kesalahan, coba lagi.';
        }
    }
}

$pageTitle = 'PEMBAYARAN';
$backUrl   = $product['back'];
require 'includes/header.php';
?>
<div class="pay-wrap">
  <div class="pay-head">
    <span><?= htmlspecialchars($product['name']) ?></span>
    <b><?= rupiah($product['price']) ?></b>
  </div>
  <p class="pay-detail"><?= htmlspecialchars($product['detail']) ?></p>
  <p class="earn-line">&#11088; Dapatkan <b>+<?= number_format($poinDapat, 0, ',', '.') ?> poin</b> dari pembelian ini</p>

  <?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="pay-method" data-id="" onclick="pick(this)">
    <div>
      <b>Pulsa</b>
      <small>Saldo: <?= rupiah($user['pulsa_balance']) ?></small>
    </div>
    <span class="radio on"></span>
  </div>

  <button type="button" class="btn btn-outline pay-other" onclick="openPayPopup()">
    Opsi Pembayaran Lain
  </button>
  <p class="other-note" id="other-note"></p>

  <div class="pay-total">
    <span>Total Bayar</span><b><?= rupiah($product['price']) ?></b>
  </div>

  <form method="post">
    <input type="hidden" name="method_id" id="method_id" value="">
    <button class="btn" type="submit">Bayar</button>
  </form>
</div>

<?php require 'includes/payment_popup.php'; ?>
<script>
var hidden = document.getElementById('method_id');
function clearAll() {
  document.querySelectorAll('.pay-method .radio').forEach(function (r) { r.classList.remove('on'); });
}
function pick(row) {            // pilih kembali "Pulsa"
  clearAll();
  row.querySelector('.radio').classList.add('on');
  hidden.value = row.dataset.id;
  document.getElementById('other-note').textContent = '';
}
window.onOtherMethod = function (id, name) {
  clearAll();
  hidden.value = id;
  document.getElementById('other-note').textContent = 'Metode dipilih: ' + name;
};
</script>
<?php require 'includes/footer.php'; ?>