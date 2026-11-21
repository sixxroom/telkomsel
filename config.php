<?php
// ============ KONFIGURASI DATABASE ============
// Default XAMPP: user "root", password kosong
define('DB_HOST', 'localhost');
define('DB_NAME', 'telkomsel_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Password halaman admin (folder /admin) - silakan ganti
define('ADMIN_PASSWORD', 'admin123');

session_start();

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Helper: format rupiah -> Rp15.000
function rupiah($angka) {
    return 'Rp' . number_format($angka, 0, ',', '.');
}

// Helper: ambil user yang sedang login (atau null)
function currentUser() {
    global $pdo;
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// Helper: halaman wajib login
function requireLogin() {
    if (!currentUser()) {
        header('Location: login.php');
        exit;
    }
}


// Helper: format kuota -> 12,5 GB
function gb($n) {
    return rtrim(rtrim(number_format((float)$n, 2, ',', '.'), '0'), ',') . ' GB';
}

// Helper: simpan pesan "pembayaran selesai" untuk ditampilkan sebagai pop up
// $rows = [['Label', 'Nilai'], ...]
function flashDone($title, $rows) {
    $_SESSION['flash_done'] = ['title' => $title, 'rows' => $rows];
}


// Aturan poin: setiap pembelian / top up mendapat poin.
// Nilai = "setiap Rp berapa mendapat 1 poin" (minimal selalu 1 poin).
// Ubah angkanya di sini kalau ingin mengganti aturan.
function earnedPoints($type, $price) {
    $perPoint = [
        'paket'   => 1000,   // Internet : 1 poin tiap Rp1.000
        'pulsa'   => 2000,   // Top up pulsa : 1 poin tiap Rp2.000
        'darurat' => 2000,   // Paket darurat: 1 poin tiap Rp2.000
        'roaming' => 500,    // Roaming : 2 poin tiap Rp1.000 (bonus)
    ];
    $per = $perPoint[$type] ?? 1000;
    return max(1, (int)floor($price / $per));
}