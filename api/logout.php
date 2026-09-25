<?php
require_once __DIR__ . '/functions.php';

// Jika user login lewat remember token, hapus token dari data juga
if (isLoggedIn()) {
    $users = getAllUsers();
    foreach ($users as &$u) {
        if ($u['id'] === $_SESSION['user_id']) {
            $u['remember_token'] = '';
            break;
        }
    }
    unset($u);
    saveAllUsers($users);
}

// Hapus cookie remember me
if (!empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
    setcookie(REMEMBER_COOKIE_NAME, '', time() - 3600, '/');
}

// Hapus semua data session lalu hancurkan session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}
session_destroy();

// Redirect ke halaman login dengan pesan
session_start();
setFlash('success', 'Anda berhasil logout.');
redirectTo('login.php');
