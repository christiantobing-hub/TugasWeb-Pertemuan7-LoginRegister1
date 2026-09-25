<?php
require_once __DIR__ . '/functions.php';

requireLogin();

$user = findUserById($_SESSION['user_id']);
if ($user === null) {
    session_destroy();
    redirectTo('login.php');
}

$errors = [];
$old = ['name' => $user['name'], 'email' => $user['email']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $emailRaw = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';

    $old['name'] = $name;
    $old['email'] = htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8');

    // Validasi nama
    if ($name === '' || strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    // Validasi email
    if ($emailRaw === '' || !isValidEmail($emailRaw)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        // Cek duplikasi email ke user lain (bukan diri sendiri)
        $existing = findUserByEmail($emailRaw);
        if ($existing !== null && $existing['id'] !== $user['id']) {
            $errors[] = 'Email sudah digunakan oleh akun lain.';
        }
    }

    // Wajib masukkan password saat ini untuk konfirmasi perubahan
    if ($currentPassword === '' || !password_verify($currentPassword, $user['password'])) {
        $errors[] = 'Password saat ini salah atau belum diisi.';
    }

    // Jika ingin ganti password, validasi panjang minimal
    if ($newPassword !== '' && !isValidPassword($newPassword)) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }

    if (empty($errors)) {
        $users = getAllUsers();
        foreach ($users as &$u) {
            if ($u['id'] === $user['id']) {
                $u['name'] = $name;
                $u['email'] = strtolower($emailRaw);
                if ($newPassword !== '') {
                    $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                $user = $u;
                break;
            }
        }
        unset($u);

        if (saveAllUsers($users)) {
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            setFlash('success', 'Profil berhasil diperbarui.');
            redirectTo('dashboard.php');
        } else {
            $errors[] = 'Gagal menyimpan perubahan. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Sistem Login/Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="card">
        <h1>Edit Profile</h1>
        <p class="subtitle">Perbarui data akun Anda</p>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <div>• <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_profile.php" novalidate>
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= $old['email'] ?>" required>
            </div>

            <div class="form-group">
                <label for="new_password">Password Baru (opsional)</label>
                <input type="password" id="new_password" name="new_password" placeholder="Kosongkan jika tidak ingin ganti">
            </div>

            <div class="form-group">
                <label for="current_password">Password Saat Ini (wajib untuk konfirmasi)</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <button type="submit" class="btn">Simpan Perubahan</button>
        </form>

        <div class="link-row">
            <a href="dashboard.php">&larr; Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>
