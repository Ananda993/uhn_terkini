<?php
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /admin/');
    } else {
        header('Location: /');
    }
    exit;
}

$error = '';
$success = '';

// Check for registration success message
if (isset($_GET['registered'])) {
    $success = 'Akun berhasil dibuat! Silakan login.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaToken = $_POST['cf-turnstile-response'] ?? '';

    if (!verifyTurnstile($captchaToken)) {
        $error = 'Verifikasi captcha gagal. Silakan coba lagi.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama, email, password, role, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $error = 'Email atau password salah.';
        } elseif (!$user['is_active']) {
            $error = 'Akun Anda telah dinonaktifkan. Hubungi admin.';
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Email atau password salah.';
        } else {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: /admin/');
            } else {
                header('Location: /');
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | UHN Terkini</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=light-2">
    <link rel="stylesheet" href="/assets/css/auth.css?v=light-2">
</head>
<body class="font-inter" style="margin:0;">

<div class="auth-page">
    <!-- Background Effects -->
    <div class="auth-grid-pattern"></div>
    <div class="auth-orb auth-orb-1"></div>
    <div class="auth-orb auth-orb-2"></div>
    <div class="auth-orb auth-orb-3"></div>

    <div class="auth-card">
        <!-- Logo -->
        <div style="text-align:center; margin-bottom:2rem;">
            <a href="/" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.75rem;">
                <img src="/foto/logo_uhn.png" alt="UHN Logo" style="height:2.5rem; width:2.5rem; object-fit:contain;">
                <div style="text-align:left;">
                    <div style="font-size:1.25rem; font-weight:700; font-family:'Space Grotesk',sans-serif; color:#b45309; letter-spacing:0.04em;">UHN TERKINI</div>
                    <div style="font-size:0.625rem; color:#64748b; letter-spacing:0.15em; text-transform:uppercase; margin-top:-2px;">Portal Informasi Kampus</div>
                </div>
            </a>
        </div>

        <h1 style="font-size:1.75rem; font-weight:800; color:#0f172a; text-align:center; margin:0 0 0.5rem; font-family:'Space Grotesk',sans-serif;">Selamat Datang!</h1>
        <p style="font-size:0.875rem; color:#94a3b8; text-align:center; margin:0 0 2rem;">Masuk ke akun Anda untuk melanjutkan</p>

        <?php if ($error): ?>
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="auth-alert auth-alert-success">
            <i class="fas fa-check-circle mt-0.5"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" id="email" name="email" class="auth-input" placeholder="contoh: nama@uhn.ac.id" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="password" name="password" class="auth-input" placeholder="Masukkan kata sandi Anda" required autocomplete="current-password">
                <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; margin-top:1rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; font-weight:500; color:#64748b; cursor:pointer; transition:color 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
                    <input type="checkbox" name="remember" style="accent-color:#6366f1; width:1rem; height:1rem; cursor:pointer;">
                    Ingat saya
                </label>
                <a href="#" style="font-size:0.8125rem; font-weight:500; color:#818cf8; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#818cf8'">Lupa sandi?</a>
            </div>

            <?php if (isCaptchaEnabled()): ?>
            <div class="captcha-field">
                <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(TURNSTILE_SITE_KEY) ?>" data-theme="light"></div>
            </div>
            <?php endif; ?>

            <button type="submit" class="auth-btn auth-btn-primary">
                Masuk ke Sistem
                <i class="fas fa-arrow-right ml-1 text-sm"></i>
            </button>
        </form>

        <p style="text-align:center; font-size:0.875rem; color:#94a3b8; margin-top:2rem;">
            Belum punya akun?
            <a href="/auth/register.php" style="color:#818cf8; font-weight:600; text-decoration:none; transition:all 0.2s; border-bottom: 1px solid transparent;" onmouseover="this.style.color='#a5b4fc'; this.style.borderBottomColor='#a5b4fc';" onmouseout="this.style.color='#818cf8'; this.style.borderBottomColor='transparent';">Daftar sekarang</a>
        </p>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
<?php if (isCaptchaEnabled()): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
</body>
</html>
