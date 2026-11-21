<?php
require 'config.php';
requireLogin();

$pageTitle = 'BELI ROAMING';
require 'includes/header.php';

$countries = $pdo->query("SELECT * FROM countries ORDER BY name")->fetchAll();
$packages  = $pdo->query("SELECT * FROM roaming_packages ORDER BY price")->fetchAll();

$byCountry = [];
foreach ($packages as $p) { $byCountry[$p['country_id']][] = $p; }
?>
<div class="logo-big" style="font-size:40px"><span>TELKOM</span>SEL</div>

<div class="roam-wrap">
  <!-- PILIH NEGARA -->
  <section class="roam-box">
    <h3>Pilih Negara</h3>
    <?php foreach ($countries as $c): ?>
      <label class="roam-country">
        <input type="radio" name="country" value="<?= $c['id'] ?>"
               onchange="showCountry(<?= $c['id'] ?>)">
        <span class="dot"></span>
        <?= htmlspecialchars($c['name']) ?>
      </label>
    <?php endforeach; ?>
  </section>

  <!-- PILIH PAKET -->
  <section class="roam-box">
    <h3>Pilih Paket</h3>
    <p id="roam-hint" class="roam-hint">Silakan pilih negara terlebih dahulu.</p>

    <?php foreach ($byCountry as $countryId => $list): ?>
      <div class="roam-list" data-country="<?= $countryId ?>" style="display:none">
        <?php foreach ($list as $p): ?>
          <div class="roam-item" onclick="location.href='payment_paket.php?type=roaming&id=<?= $p['id'] ?>'">
            <div>
              <b><?= htmlspecialchars($p['name']) ?></b>
              <small>Masa aktif <?= $p['valid_days'] ?> hari</small>
            </div>
            <em><?= rupiah($p['price']) ?><small class="earn">+<?= earnedPoints('roaming', $p['price']) ?> poin</small></em>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </section>
</div>

<script>
function showCountry(id) {
  document.getElementById('roam-hint').style.display = 'none';
  document.querySelectorAll('.roam-list').forEach(function (el) {
    el.style.display = (el.dataset.country == id) ? 'block' : 'none';
  });
}
</script>
<?php require 'includes/footer.php'; ?>