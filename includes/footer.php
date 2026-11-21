</main>

<!-- Pop up umum (pesan, maintenance, dll.) -->
<div class="modal-overlay" id="popup" onclick="closePopup()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <p id="popup-text"></p>
    <button class="btn" onclick="closePopup()">OK</button>
  </div>
</div>

<!-- Pop up konfirmasi pembayaran / penukaran selesai -->
<div class="modal-overlay" id="donePopup">
  <div class="modal-box done-box">
    <div class="done-check">&#10003;</div>
    <h3 id="done-title"></h3>
    <div class="done-rows" id="done-rows"></div>
    <button class="btn" onclick="closeDone()">OK</button>
  </div>
</div>

<script src="assets/js/app.js"></script>
<?php if (!empty($_SESSION['flash_done'])):
  $fd = $_SESSION['flash_done'];
  unset($_SESSION['flash_done']); ?>
<script>
  window.addEventListener('DOMContentLoaded', function () {
    showDone(<?= json_encode($fd, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);
  });
</script>
<?php endif; ?>
</body>
</html>