<?php
/**
 * config.php
 * Konfigurasi dasar: session, path penyimpanan data JSON, dan pengaturan cookie.
 */

// Mulai session di awal sebelum ada output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path ke file penyimpanan data (JSON sebagai "database")
// CATATAN PENTING UNTUK VERCEL: filesystem Vercel bersifat read-only,
// kecuali folder /tmp yang bisa ditulis tapi SEMENTARA (hilang saat function
// "tidur"/cold start baru). Deteksi environment Vercel lewat env var VERCEL,
// lalu gunakan /tmp supaya aplikasi tidak error saat mencoba menulis data.
// Di luar Vercel (lokal / hosting biasa), tetap pakai folder data/ seperti biasa.
if (getenv('VERCEL')) {
    define('DATA_DIR', '/tmp/data');
} else {
    define('DATA_DIR', __DIR__ . '/data');
}
define('USERS_FILE', DATA_DIR . '/users.json');

// Pastikan folder data ada
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Pastikan file users.json ada, inisialisasi dengan array kosong jika belum ada
if (!file_exists(USERS_FILE)) {
    file_put_contents(USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
}

// Nama cookie untuk fitur "Remember Me"
define('REMEMBER_COOKIE_NAME', 'remember_token');
define('REMEMBER_COOKIE_DURATION', 60 * 60 * 24 * 30); // 30 hari
