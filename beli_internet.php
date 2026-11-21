<?php
require 'config.php';
requireLogin();
$user = currentUser();

$id = (int)($_GET['id'] ?? 0);
$s  = $pdo->prepare("SELECT * FROM pulsa_options WHERE id = ?");
$s->execute([$id]);
$opt = $s->fetch();
if (!$opt) { header('Location: beli_pulsa.php'); exit; }

$error = '';
$poinDapat = earnedPoints('pulsa', $opt['price']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $methodId = (int)($_POST['method_id'] ?? 0);

    // Boleh: salah satu bank (hasil pilihan M-Banking) atau Indomi
    $m = $pdo->prepare("SELECT id, name, group_name FROM payment_methods
                        WHERE id = ? AND (group_name IN ('mbanking','bank') OR code = 'INDOMI')");
    $m->execute([$methodId]);
    $method = $m->fetch();

    if (!$method) {
        $error = 'Pilih menu pembayaran anda.';
    } else {
        $methodName = in_array($method['group_name'], ['mbanking', 'bank'])
                    ? 'M-Banking (' . $method['name'] . ')'
                    : $method['name'];
        try {
            $pdo->beginTransaction();
            // Pembayaran pihak ketiga dianggap berhasil (simulasi),
            // lalu pulsa ditambahkan sebesar nominal.
            $pdo->prepare("UPDATE users SET pulsa_balance = pulsa_balance + ?, points = points + ? WHERE id = ?")
                ->execute([$opt['nominal'], $poinDapat, $user['id']]);
            $pdo->prepare("INSERT INTO transactions
                (user_id, product_type, product_id, payment_method_id, total, status)
                VALUES (?, 'pulsa', ?, ?, ?, 'sukses')")
                ->execute([$user['id'], $id, $methodId, $opt['price']]);
            $pdo->commit();

            flashDone('Pembayaran Selesai', [
                ['Produk',        'Pulsa ' . number_format($opt['nominal'], 0, ',', '.')],
                ['Nomor',         $user['phone_number']],
                ['Total bayar',   rupiah($opt['price'])],
                ['Metode',        $methodName],
                ['Pulsa sekarang', rupiah($user['pulsa_balance'] + $opt['nominal'])],
                ['Poin didapat',   '+' . number_format($poinDapat, 0, ',', '.')],
                ['Total poin',     number_format($user['points'] + $poinDapat, 0, ',', '.')],
            ]);
            header('Location: main.php');
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Terjadi kesalahan, coba lagi.';
        }
    }
}

// Pilihan di halaman: M-Banking dan Indomi
$direct = $pdo->query(
    "SELECT id, name, code FROM payment_methods WHERE group_name = 'pulsa' ORDER BY id"
)->fetchAll();

$pageTitle = 'PEMBAYARAN';
$backUrl   = 'beli_pulsa.php';
require 'includes/header.php';
?>
<div class="pay-wrap">
  <div class="pay-head">
    <span>Pulsa <?= number_format($opt['nominal'], 0, ',', '.') ?></span>
    <b><?= rupiah($opt['price']) ?></b>
  </div>
  <p class="pay-detail">Nomor: <?= htmlspecialchars($user['phone_number']) ?></p>
  <p class="earn-line">&#11088; Dapatkan <b>+<?= number_format($poinDapat, 0, ',', '.') ?> poin</b> dari top up ini</p>

  <?php if ($error): ?>
    <div class="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php foreach ($direct as $d): ?>
    <div class="pay-method" data-id="<?= $d['id'] ?>"
         data-type="<?= $d['code'] === 'MBANKING' ? 'mbanking' : 'direct' ?>"
         onclick="pickRow(this)">
      <b><?= htmlspecialchars($d['name']) ?></b>
      <span class="radio"></span>
    </div>
  <?php endforeach; ?>
  <p class="other-note" id="other-note"></p>

  <div class="pay-total">
    <span>Total Bayar</span><b><?= rupiah($opt['price']) ?></b>
  </div>

  <form method="post" id="payForm">
    <input type="hidden" name="method_id" id="method_id" value="">
    <button class="btn" type="submit">Bayar</button>
  </form>
</div>

<?php require 'includes/payment_popup.php'; ?>
<script>
var hidden = document.getElementById('method_id');
var note   = document.getElementById('other-note');

function clearAll() {
  document.querySelectorAll('.pay-method .radio').forEach(function (r) { r.classList.remove('on'); });
}
function pickRow(row) {
  clearAll();
  row.querySelector('.radio').classList.add('on');
  note.textContent = '';
  if (row.dataset.type === 'mbanking') {
    hidden.value = '';        // menunggu pilihan bank
    openPayPopup();           // langsung tampilkan pop up bank
  } else {
    hidden.value = row.dataset.id;
  }
}
// dipanggil setelah KONFIRMASI di pop up bank
window.onOtherMethod = function (id, name) {
  hidden.value = id;
  note.textContent = 'M-Banking: ' + name;
};
document.getElementById('payForm').addEventListener('submit', function (e) {
  if (!hidden.value) { e.preventDefault(); showPopup('Pilih menu pembayaran anda'); }
});
</script>
<?php require 'includes/footer.php'; ?>