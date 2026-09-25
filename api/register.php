<?php
require_once __DIR__ . '/functions.php';

// Jika sudah login, langsung ke dashboard
if (isLoggedIn()) {
    redirectTo('dashboard.php');
}

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input
    $name = sanitizeInput($_POST['name'] ?? '');
    $emailRaw = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    $old['name'] = $name;
    $old['email'] = htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8');

    // Validasi nama
    if ($name === '') {
        $errors[] = 'Nama tidak boleh kosong.';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    // Validasi email
    if ($emailRaw === '') {
        $errors[] = 'Email tidak boleh kosong.';
    } elseif (!isValidEmail($emailRaw)) {
        $errors[] = 'Format email tidak valid.';
    }

    // Validasi password
    if ($password === '') {
        $errors[] = 'Password tidak boleh kosong.';
    } elseif (!isValidPassword($password)) {
        $errors[] = 'Password minimal 6 karakter.';
    } elseif ($password !== $passwordConfirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Cek duplikasi email (hanya jika email valid)
    if (empty($errors) && findUserByEmail($emailRaw) !== null) {
        $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
    }

    // Jika semua validasi lolos, simpan user baru
    if (empty($errors)) {
        $users = getAllUsers();

        $newUser = [
            'id' => generateUserId(),
            'name' => $name,
            'email' => strtolower($emailRaw),
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'remember_token' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $users[] = $newUser;

        if (saveAllUsers($users)) {
            setFlash('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
            redirectTo('login.php');
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Login/Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="card">
        <h1>Buat Akun Baru</h1>
        <p class="subtitle">Isi form di bawah untuk mendaftar</p>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <div>• <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="<?= $old['name'] ?>" placeholder="Masukkan nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= $old['email'] ?>" placeholder="nama@email.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>

            <div class="form-group">
                <label for="password_confirm">Konfirmasi Password</label>
                <input type="password" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="btn">Daftar</button>
        </form>

        <div class="link-row">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>
