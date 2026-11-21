<?php
// Pop up "Opsi Pembayaran Lain": ABC, MANJA | RBI, BANK TINY
// Dipasang di halaman payment SEBELUM footer.php:
//   require 'includes/payment_popup.php';
// Halaman harus mendefinisikan: window.onOtherMethod = function(id, name) {...}
$popMethods = $pdo->query(
    "SELECT id, name, group_name FROM payment_methods
     WHERE group_name IN ('mbanking','bank') ORDER BY id"
)->fetchAll();

$popGroups = [];
foreach ($popMethods as $m) { $popGroups[$m['group_name']][] = $m; }
?>
<div class="modal-overlay" id="payPopup" onclick="closePayPopup()">
  <div class="modal-box pay-popup" onclick="event.stopPropagation()">
    <?php foreach ($popGroups as $group): ?>
      <div class="pop-group">
      <?php foreach ($group as $m): ?>
        <label class="pop-row">
          <span class="dot"></span>
          <span class="pop-name"><?= htmlspecialchars($m['name']) ?></span>
          <input type="radio" name="popmethod" value="<?= $m['id'] ?>"
                 data-name="<?= htmlspecialchars($m['name']) ?>">
        </label>
      <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <button type="button" class="btn" onclick="confirmPayPopup()">KONFIRMASI</button>
  </div>
</div>

<script>
function openPayPopup()  { document.getElementById('payPopup').classList.add('show'); }
function closePayPopup() { document.getElementById('payPopup').classList.remove('show'); }
function confirmPayPopup() {
  var chosen = document.querySelector('input[name="popmethod"]:checked');
  if (!chosen) { showPopup('Pilih menu pembayaran anda'); return; }
  closePayPopup();
  if (window.onOtherMethod) window.onOtherMethod(chosen.value, chosen.dataset.name);
}
</script>