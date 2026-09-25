<?php
require_once __DIR__ . '/functions.php';

// Jika sudah login, langsung ke dashboard
if (isLoggedIn()) {
    redirectTo('dashboard.php');
}

$errors = [];
$old = ['email' => ''];
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailRaw = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $old['email'] = htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8');

    if ($emailRaw === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } elseif (!isValidEmail($emailRaw)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $user = findUserByEmail($emailRaw);

        // Pesan generik agar tidak bocorkan info mana yang salah (email/password)
        if ($user === null || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            // Login sukses -> set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Fitur bonus: Remember Me via cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));

                $users = getAllUsers();
                foreach ($users as &$u) {
                    if ($u['id'] === $user['id']) {
                        $u['remember_token'] = $token;
                        break;
                    }
                }
                unset($u);
                saveAllUsers($users);

                setcookie(REMEMBER_COOKIE_NAME, $token, time() + REMEMBER_COOKIE_DURATION, '/', '', false, true);
            }

            setFlash('success', 'Login berhasil! Selamat datang kembali, ' . $user['name'] . '.');
            redirectTo('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Login/Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="card">
        <h1>Selamat Datang</h1>
        <p class="subtitle">Login untuk melanjutkan ke dashboard</p>

        <?php if (!empty($flash['success'])): ?>
            <div class="alert success"><?= htmlspecialchars($flash['success'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <div>• <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= $old['email'] ?>" placeholder="nama@email.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <div class="checkbox-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" style="margin:0;">Ingat saya (Remember Me)</label>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="link-row">
            Belum punya akun? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>
</body>
</html>
