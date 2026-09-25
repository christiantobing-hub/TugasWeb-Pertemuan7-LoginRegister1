<?php
/**
 * functions.php
 * Kumpulan fungsi bantu untuk operasi data user (JSON), sanitasi, dan autentikasi.
 */

require_once __DIR__ . '/config.php';

/**
 * Membaca seluruh data user dari file JSON.
 * @return array
 */
function getAllUsers(): array
{
    $json = file_get_contents(USERS_FILE);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

/**
 * Menyimpan seluruh data user ke file JSON.
 * Menggunakan file locking (LOCK_EX) agar aman dari race condition sederhana.
 * @param array $users
 * @return bool
 */
function saveAllUsers(array $users): bool
{
    $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

/**
 * Mencari user berdasarkan email.
 * @param string $email
 * @return array|null
 */
function findUserByEmail(string $email): ?array
{
    $users = getAllUsers();
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return $user;
        }
    }
    return null;
}

/**
 * Mencari user berdasarkan ID.
 * @param string $id
 * @return array|null
 */
function findUserById(string $id): ?array
{
    $users = getAllUsers();
    foreach ($users as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

/**
 * Membuat ID unik sederhana untuk user baru.
 * @return string
 */
function generateUserId(): string
{
    return bin2hex(random_bytes(8)) . '-' . time();
}

/**
 * Sanitasi input teks umum (nama, dll) menggunakan htmlspecialchars + trim.
 * @param string $input
 * @return string
 */
function sanitizeInput(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validasi format email menggunakan filter_var().
 * @param string $email
 * @return bool
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validasi kekuatan password minimal (contoh: minimal 6 karakter).
 * @param string $password
 * @return bool
 */
function isValidPassword(string $password): bool
{
    return strlen($password) >= 6;
}

/**
 * Mengecek apakah user sedang login (via session).
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect helper.
 * @param string $location
 */
function redirectTo(string $location): void
{
    header('Location: ' . $location);
    exit;
}

/**
 * Melindungi halaman: redirect ke login.php jika user belum login.
 * Juga mencoba login otomatis lewat cookie "Remember Me" jika session kosong.
 */
function requireLogin(): void
{
    if (isLoggedIn()) {
        return;
    }

    // Coba auto-login dari cookie remember_token (fitur bonus)
    if (!empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
        $token = $_COOKIE[REMEMBER_COOKIE_NAME];
        $users = getAllUsers();
        foreach ($users as $user) {
            if (isset($user['remember_token']) && $user['remember_token'] !== '' && hash_equals($user['remember_token'], $token)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                return;
            }
        }
        // Token tidak valid / kadaluarsa -> hapus cookie
        setcookie(REMEMBER_COOKIE_NAME, '', time() - 3600, '/');
    }

    redirectTo('login.php');
}

/**
 * Set flash message sederhana di session untuk ditampilkan sekali lalu dihapus.
 * @param string $type 'error' | 'success'
 * @param string $message
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][$type] = $message;
}

/**
 * Ambil dan hapus flash message.
 * @return array
 */
function getFlash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}
