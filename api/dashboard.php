<?php
require_once __DIR__ . '/functions.php';

// Proteksi halaman: redirect ke login jika belum login
requireLogin();

$user = findUserById($_SESSION['user_id']);

// Jika user tidak ditemukan (mis. data dihapus manual), paksa logout
if ($user === null) {
    session_destroy();
    redirectTo('login.php');
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Login/Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="card wide">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <span class="badge">Login aktif</span>
        </div>

        <?php if (!empty($flash['success'])): ?>
            <div class="alert success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="info-box">
            <p><strong>Nama:</strong> <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Terdaftar sejak:</strong> <?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="actions-row">
            <a href="edit_profile.php" class="btn secondary">Edit Profile</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>
