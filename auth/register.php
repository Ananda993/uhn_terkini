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
    $noHp = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fakultas = trim($_POST['fakultas'] ?? '');
    $ormawa = trim($_POST['ormawa'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $captchaToken = $_POST['cf-turnstile-response'] ?? '';

    $old = ['nama' => $nama, 'no_hp' => $noHp, 'email' => $email, 'fakultas' => $fakultas, 'ormawa' => $ormawa];

    // Validation
    if (!verifyTurnstile($captchaToken)) {
        $error = 'Verifikasi captcha gagal. Silakan coba lagi.';
    } elseif (empty($nama) || empty($email) || empty($password) || empty($password_confirm) || empty($noHp) || empty($fakultas) || empty($ormawa)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($noHp) < 8) {
        $error = 'Nomor HP minimal 8 digit.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM fakultas WHERE nama = ? AND is_active = 1");
        $stmt->bind_param("s", $fakultas);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $error = 'Fakultas tidak valid.';
        }
        $stmt->close();

        if (empty($error)) {
            $stmt = $conn->prepare("SELECT id FROM ormawa WHERE nama = ? AND is_active = 1");
            $stmt->bind_param("s", $ormawa);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $error = 'ORMAWA tidak valid.';
            }
            $stmt->close();
        }

        // Check email uniqueness
        if (empty($error)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
            }
            $stmt->close();
        }

        if (empty($error)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'mahasiswa';
            $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, no_hp, fakultas, ormawa, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("sssssss", $nama, $email, $hashed, $role, $noHp, $fakultas, $ormawa);
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

$fakultasData = [];
$fakultasRes = $conn->query("SELECT nama FROM fakultas WHERE is_active = 1 ORDER BY nama ASC");
if ($fakultasRes) {
    while ($row = $fakultasRes->fetch_assoc()) {
        $fakultasData[] = $row['nama'];
    }
}

$ormawaData = [];
$ormawaRes = $conn->query("SELECT o.nama, o.scope, f.nama as fakultas_nama FROM ormawa o LEFT JOIN fakultas f ON o.fakultas_id = f.id WHERE o.is_active = 1 ORDER BY o.scope ASC, o.nama ASC");
if ($ormawaRes) {
    while ($row = $ormawaRes->fetch_assoc()) {
        $ormawaData[] = [
            'nama' => $row['nama'],
            'scope' => $row['scope'],
            'fakultas' => $row['fakultas_nama']
        ];
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
                    <label for="no_hp">No HP <span style="color:#ef4444;">*</span></label>
                    <i class="fas fa-phone input-icon"></i>
                    <input type="text" id="no_hp" name="no_hp" class="auth-input" placeholder="contoh: 081234567890" value="<?= htmlspecialchars($old['no_hp'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group" style="margin-top:1.25rem;">
                <label for="email">Alamat Email <span style="color:#ef4444;">*</span></label>
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" id="email" name="email" class="auth-input" placeholder="contoh: nama@uhn.ac.id" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="fakultas">Fakultas <span style="color:#ef4444;">*</span></label>
                <i class="fas fa-building input-icon"></i>
                <select id="fakultas" name="fakultas" class="auth-select" required>
                    <option value="">-- Pilih Fakultas --</option>
                    <?php foreach ($fakultasData as $fakultas): ?>
                        <option value="<?= htmlspecialchars($fakultas) ?>" <?= ($old['fakultas'] ?? '') === $fakultas ? 'selected' : '' ?>><?= htmlspecialchars($fakultas) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="ormawa">ORMAWA <span style="color:#ef4444;">*</span></label>
                <i class="fas fa-sitemap input-icon"></i>
                <select id="ormawa" name="ormawa" class="auth-select" required>
                    <option value="">-- Pilih ORMAWA --</option>
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

const fakultasSelect = document.getElementById('fakultas');
const ormawaSelect = document.getElementById('ormawa');
const ormawaData = <?= json_encode($ormawaData) ?>;

function renderOrmawaOptions(selectedFaculty, selectedValue) {
    if (!ormawaSelect) {
        return;
    }
    const options = ['-- Pilih ORMAWA --'];
    const base = ormawaData.filter(item => item.scope === 'univ').map(item => item.nama);
    const facultyOptions = ormawaData
        .filter(item => item.scope === 'fakultas' && item.fakultas === selectedFaculty)
        .map(item => item.nama);

    if (base.length > 0) {
        options.push('UNIV');
        base.forEach(item => options.push(item));
    }

    if (facultyOptions.length > 0) {
        options.push(selectedFaculty);
        facultyOptions.forEach(item => options.push(item));
    }

    if (facultyOptions.length === 0 && selectedFaculty !== '') {
        options.push('COMING SOON');
    }

    if (selectedValue && !options.includes(selectedValue)) {
        options.push(selectedValue);
    }

    ormawaSelect.innerHTML = '';
    options.forEach((label) => {
        if (label === '-- Pilih ORMAWA --') {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = label;
            ormawaSelect.appendChild(opt);
            return;
        }
        if (label === 'UNIV' || label === 'FDA' || label === 'FDD' || label === 'FBW' || label === 'FAST' || label === 'Kedokteran') {
            const optGroup = document.createElement('option');
            optGroup.disabled = true;
            optGroup.textContent = '--- ' + label + ' ---';
            ormawaSelect.appendChild(optGroup);
            return;
        }
        if (label === 'COMING SOON') {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Coming soon';
            opt.disabled = true;
            ormawaSelect.appendChild(opt);
            return;
        }
        const opt = document.createElement('option');
        opt.value = label;
        opt.textContent = label;
        if (selectedValue && selectedValue === label) {
            opt.selected = true;
        }
        ormawaSelect.appendChild(opt);
    });
}

if (fakultasSelect) {
    fakultasSelect.addEventListener('change', (e) => {
        renderOrmawaOptions(e.target.value, '');
    });
    renderOrmawaOptions(fakultasSelect.value, <?= json_encode($old['ormawa'] ?? '') ?>);
}
</script>
<?php if (isCaptchaEnabled()): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>
</body>
</html>
