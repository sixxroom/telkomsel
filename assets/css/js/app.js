// ===== POP UP BIASA =====
let popupRedirect = null;

// showPopup('pesan')                -> pop up biasa
// showPopup('pesan', 'login.php')   -> setelah OK, pindah ke halaman itu
function showPopup(message, redirectUrl) {
  popupRedirect = redirectUrl || null;
  document.getElementById('popup-text').textContent = message;
  document.getElementById('popup').classList.add('show');
}
function closePopup() {
  document.getElementById('popup').classList.remove('show');
  if (popupRedirect) {
    window.location.href = popupRedirect;
  }
}

// Pop up khusus maintenance (Menu Lain)
function showMaintenance() {
  showPopup('Maaf layanan sedang dalam pemeliharaan');
}

// ===== POP UP "SELESAI" =====
// data = { title: '...', rows: [['Label','Nilai'], ...] }
function showDone(data) {
  document.getElementById('done-title').textContent = data.title || 'Pembayaran Selesai';
  var box = document.getElementById('done-rows');
  box.innerHTML = '';
  (data.rows || []).forEach(function (r) {
    var row = document.createElement('div');
    row.className = 'done-row';
    var a = document.createElement('span'); a.textContent = r[0];
    var b = document.createElement('b');    b.textContent = r[1];
    row.appendChild(a); row.appendChild(b);
    box.appendChild(row);
  });
  document.getElementById('donePopup').classList.add('show');
}
function closeDone() {
  document.getElementById('donePopup').classList.remove('show');
}