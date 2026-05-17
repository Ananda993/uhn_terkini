<?php
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /');
    exit;
}

$error = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $captchaToken = $_POST['cf-turnstile-response'] ?? '';

    $old = ['nama' => $nama, 'nim' => $nim, 'email' => $email, 'prodi' => $prodi];

    // Validation
    if (!verifyTurnstile($captchaToken)) {
        $error = 'Verifikasi captcha gagal. Silakan coba lagi.';
    } elseif (empty($nama) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Check email uniqueness
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }
        $stmt->close();

        if (empty($error)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'mahasiswa';
            $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, nim, prodi, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("ssssss", $nama, $email, $hashed, $role, $nim, $prodi);
            if ($stmt->execute()) {
                header('Location: /auth/login.php?registered=1');
                exit;
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | UHN Terkini</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=light-2">
    <link rel="stylesheet" href="/assets/css/auth.css?v=light-2">
</head>
<body class="font-inter" style="margin:0;">

<div class="auth-page">
    <div class="auth-grid-pattern"></div>
    <div class="auth-orb auth-orb-1"></div>
    <div class="auth-orb auth-orb-2"></div>
    <div class="auth-orb auth-orb-3"></div>

    <div class="auth-card" style="max-width:480px;">
        <!-- Logo -->
        <div style="text-align:center; margin-bottom:1.75rem;">
            <a href="/" style="text-decoration:none; display:inline-flex; align-items:center; gap:0.75rem;">
                <img src="/foto/logo_uhn.png" alt="UHN Logo" style="height:2.5rem; width:2.5rem; object-fit:contain;">
                <div style="text-align:left;">
                    <div style="font-size:1.25rem; font-weight:700; font-family:'Space Grotesk',sans-serif; color:#b45309; letter-spacing:0.04em;">UHN TERKINI</div>
                    <div style="font-size:0.625rem; color:#64748b; letter-spacing:0.15em; text-transform:uppercase; margin-top:-2px;">Portal Informasi Kampus</div>
                </div>
            </a>
        </div>

        <h1 style="font-size:1.75rem; font-weight:800; color:#0f172a; text-align:center; margin:0 0 0.5rem; font-family:'Space Grotesk',sans-serif;">Buat Akun Baru</h1>
        <p style="font-size:0.875rem; color:#94a3b8; text-align:center; margin:0 0 2rem;">Bergabung dengan komunitas UHN Terkini</p>

        <?php if ($error): ?>
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="nama">Nama Lengkap <span style="color:#ef4444;">*</span></label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="nama" name="nama" class="auth-input" placeholder="contoh: Budi Santoso" value="<?= htmlspecialchars($old['nama'] ?? '') ?>" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="nim">NIM</label>
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="nim" name="nim" class="auth-input" placeholder="contoh: 210101001" value="<?= htmlspecialchars($old['nim'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin-top:1.25rem;">
                <label for="email">Alamat Email <span style="color:#ef4444;">*</span></label>
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" id="email" name="email" class="auth-input" placeholder="contoh: nama@uhn.ac.id" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="prodi">Program Studi</label>
                <i class="fas fa-building input-icon"></i>
                <select id="prodi" name="prodi" class="auth-select">
                    <option value="">-- Pilih Program Studi --</option>
                    <option value="Pendidikan Agama Hindu" <?= ($old['prodi'] ?? '') === 'Pendidikan Agama Hindu' ? 'selected' : '' ?>>Pendidikan Agama Hindu</option>
                    <option value="Sastra Agama dan Pendidikan Bahasa Bali" <?= ($old['prodi'] ?? '') === 'Sastra Agama dan Pendidikan Bahasa Bali' ? 'selected' : '' ?>>Sastra Agama dan Pendidikan Bahasa Bali</option>
                    <option value="Pendidikan Guru Sekolah Dasar" <?= ($old['prodi'] ?? '') === 'Pendidikan Guru Sekolah Dasar' ? 'selected' : '' ?>>Pendidikan Guru Sekolah Dasar</option>
                    <option value="Pendidikan Bahasa Inggris" <?= ($old['prodi'] ?? '') === 'Pendidikan Bahasa Inggris' ? 'selected' : '' ?>>Pendidikan Bahasa Inggris</option>
                    <option value="Ilmu Komunikasi Hindu" <?= ($old['prodi'] ?? '') === 'Ilmu Komunikasi Hindu' ? 'selected' : '' ?>>Ilmu Komunikasi Hindu</option>
                    <option value="Penerangan Agama Hindu" <?= ($old['prodi'] ?? '') === 'Penerangan Agama Hindu' ? 'selected' : '' ?>>Penerangan Agama Hindu</option>
                    <option value="Hukum Hindu" <?= ($old['prodi'] ?? '') === 'Hukum Hindu' ? 'selected' : '' ?>>Hukum Hindu</option>
                    <option value="Industri Perjalanan" <?= ($old['prodi'] ?? '') === 'Industri Perjalanan' ? 'selected' : '' ?>>Industri Perjalanan</option>
                    <option value="Kewirausahaan" <?= ($old['prodi'] ?? '') === 'Kewirausahaan' ? 'selected' : '' ?>>Kewirausahaan</option>
                    <option value="Informatika" <?= ($old['prodi'] ?? '') === 'Informatika' ? 'selected' : '' ?>>Informatika</option>
                    <option value="Yoga Kesehatan" <?= ($old['prodi'] ?? '') === 'Yoga Kesehatan' ? 'selected' : '' ?>>Yoga Kesehatan</option>
                    <option value="Teologi Hindu" <?= ($old['prodi'] ?? '') === 'Teologi Hindu' ? 'selected' : '' ?>>Teologi Hindu</option>
                    <option value="Filsafat Hindu" <?= ($old['prodi'] ?? '') === 'Filsafat Hindu' ? 'selected' : '' ?>>Filsafat Hindu</option>
                    <option value="Pendidikan Guru Pendidikan Anak Usia Dini" <?= ($old['prodi'] ?? '') === 'Pendidikan Guru Pendidikan Anak Usia Dini' ? 'selected' : '' ?>>Pendidikan Guru Pendidikan Anak Usia Dini</option>
                    <option value="Sains Informasi" <?= ($old['prodi'] ?? '') === 'Sains Informasi' ? 'selected' : '' ?>>Sains Informasi</option>
                    <option value="Desain Komunikasi Visual" <?= ($old['prodi'] ?? '') === 'Desain Komunikasi Visual' ? 'selected' : '' ?>>Desain Komunikasi Visual</option>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; margin-bottom: 2rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label for="password">Kata Sandi <span style="color:#ef4444;">*</span></label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="auth-input" placeholder="Min. 6 karakter" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePassword('password', this)"><i class="fas fa-eye"></i></button>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label for="password_confirm">Konfirmasi <span style="color:#ef4444;">*</span></label>
                    <i class="fas fa-shield-alt input-icon"></i>
                    <input type="password" id="password_confirm" name="password_confirm" class="auth-input" placeholder="Ulangi kata sandi" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirm', this)"><i class="fas fa-eye"></i></button>
                </div>
            </div>

            <?php if (isCaptchaEnabled()): ?>
            <div class="captcha-field">
                <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars(TURNSTILE_SITE_KEY) ?>" data-theme="light"></div>
            </div>
            <?php endif; ?>

            <button type="submit" class="auth-btn auth-btn-primary">
                Daftar Sekarang
                <i class="fas fa-arrow-right ml-1 text-sm"></i>
            </button>
        </form>

        <p style="text-align:center; font-size:0.875rem; color:#94a3b8; margin-top:2rem;">
            Sudah punya akun?
            <a href="/auth/login.php" style="color:#818cf8; font-weight:600; text-decoration:none; transition:all 0.2s; border-bottom: 1px solid transparent;" onmouseover="this.style.color='#a5b4fc'; this.style.borderBottomColor='#a5b4fc';" onmouseout="this.style.color='#818cf8'; this.style.borderBottomColor='transparent';">Masuk di sini</a>
        </p>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fas fa-eye'; }
}
</script>
<?php if (isCaptchaEnabled()): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
</body>
</html>
