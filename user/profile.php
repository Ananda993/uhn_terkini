<?php
$pageTitle = 'Profil';
require_once __DIR__ . '/../config/database.php';
requireLogin();

$userId = (int)$_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT id, nama, email, no_hp, fakultas, ormawa, ukm_id, periode, foto_profil FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: /');
    exit;
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

$ukmData = [];
$ukmRes = $conn->query("SELECT id, nama FROM ukm WHERE is_active = 1 ORDER BY nama ASC");
if ($ukmRes) {
    while ($row = $ukmRes->fetch_assoc()) {
        $ukmData[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $noHp = trim($_POST['no_hp'] ?? '');
    $fakultas = trim($_POST['fakultas'] ?? '');
    $ormawa = trim($_POST['ormawa'] ?? '');
    $ukmId = (int)($_POST['ukm_id'] ?? 0);
    $periode = trim($_POST['periode'] ?? '');

    if ($nama === '' || $email === '' || $noHp === '' || $fakultas === '' || $ormawa === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($noHp) < 8) {
        $error = 'Nomor HP minimal 8 digit.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $check->bind_param('si', $email, $userId);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar.';
        }
        $check->close();
    }

    if ($error === '') {
        $check = $conn->prepare('SELECT id FROM fakultas WHERE nama = ? AND is_active = 1');
        $check->bind_param('s', $fakultas);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $error = 'Fakultas tidak valid.';
        }
        $check->close();
    }

    if ($error === '') {
        $check = $conn->prepare('SELECT id FROM ormawa WHERE nama = ? AND is_active = 1');
        $check->bind_param('s', $ormawa);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $error = 'ORMAWA tidak valid.';
        }
        $check->close();
    }

    if ($error === '' && $ukmId > 0) {
        $check = $conn->prepare('SELECT id FROM ukm WHERE id = ? AND is_active = 1');
        $check->bind_param('i', $ukmId);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $error = 'UKM tidak valid.';
        }
        $check->close();
    }

    $fotoName = $user['foto_profil'] ?? null;
    if ($error === '' && !empty($_FILES['foto_profil']['name'])) {
        $uploadDir = __DIR__ . '/../foto/';
        $tmpName = $_FILES['foto_profil']['tmp_name'];
        $fileInfo = @getimagesize($tmpName);
        if ($fileInfo === false) {
            $error = 'File foto tidak valid.';
        } elseif ($_FILES['foto_profil']['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran foto maksimal 2MB.';
        } else {
            $ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowedExt, true)) {
                $error = 'Format foto harus JPG, PNG, atau WEBP.';
            } else {
                $fotoName = 'profile_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($tmpName, $uploadDir . $fotoName)) {
                    $error = 'Gagal mengunggah foto.';
                }
            }
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare('UPDATE users SET nama = ?, email = ?, no_hp = ?, fakultas = ?, ormawa = ?, ukm_id = ?, periode = ?, foto_profil = ? WHERE id = ?');
        $stmt->bind_param('sssssissi', $nama, $email, $noHp, $fakultas, $ormawa, $ukmId, $periode, $fotoName, $userId);
        $stmt->execute();
        $stmt->close();

        $_SESSION['user_nama'] = $nama;
        $_SESSION['user_email'] = $email;

        $success = 'Profil berhasil diperbarui.';
        $user['nama'] = $nama;
        $user['email'] = $email;
        $user['no_hp'] = $noHp;
        $user['fakultas'] = $fakultas;
        $user['ormawa'] = $ormawa;
        $user['ukm_id'] = $ukmId;
        $user['periode'] = $periode;
        $user['foto_profil'] = $fotoName;
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="pt-28 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Profil Mahasiswa</h1>
            <p class="text-dark-400 mt-2">Lengkapi data dan kelola foto profil Anda.</p>
        </div>

        <?php if ($error): ?>
            <div class="auth-alert auth-alert-error" style="max-width:520px; margin-bottom:1rem;">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif ($success): ?>
            <div class="auth-alert auth-alert-success" style="max-width:520px; margin-bottom:1rem;">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="glass-card rounded-2xl p-6 sm:p-8">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="flex flex-col sm:flex-row items-start gap-6">
                    <div class="w-28 h-28 rounded-2xl overflow-hidden bg-white/5 border border-white/10 flex items-center justify-center">
                        <?php if (!empty($user['foto_profil'])): ?>
                            <img src="/foto/<?= htmlspecialchars($user['foto_profil']) ?>" alt="Foto Profil" class="w-full h-full object-cover">
                        <?php else: ?>
                            <span class="text-2xl font-bold text-dark-200"><?= strtoupper(substr($user['nama'], 0, 1)) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm text-dark-300 mb-2">Foto Profil</label>
                        <input type="file" name="foto_profil" accept="image/*" class="auth-input" style="padding-left:1rem; max-width:420px;">
                        <p class="text-xs text-dark-500 mt-2">Format JPG/PNG/WEBP, maksimal 2MB.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-dark-300 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" class="auth-input" style="padding-left:1rem;" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm text-dark-300 mb-2">Email</label>
                        <input type="email" name="email" class="auth-input" style="padding-left:1rem;" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm text-dark-300 mb-2">No HP</label>
                        <input type="text" name="no_hp" class="auth-input" style="padding-left:1rem;" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm text-dark-300 mb-2">Periode</label>
                        <input type="text" name="periode" class="auth-input" style="padding-left:1rem;" placeholder="Contoh: 2025/2026" value="<?= htmlspecialchars($user['periode'] ?? '') ?>">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-dark-300 mb-2">Fakultas</label>
                        <select name="fakultas" id="fakultas" class="auth-select" style="padding-left:1rem;" required>
                            <option value="">-- Pilih Fakultas --</option>
                            <?php foreach ($fakultasData as $fakultas): ?>
                                <option value="<?= htmlspecialchars($fakultas) ?>" <?= ($user['fakultas'] ?? '') === $fakultas ? 'selected' : '' ?>><?= htmlspecialchars($fakultas) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-dark-300 mb-2">ORMAWA</label>
                        <select name="ormawa" id="ormawa" class="auth-select" style="padding-left:1rem;" required>
                            <option value="">-- Pilih ORMAWA --</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-dark-300 mb-2">UKM (Opsional)</label>
                    <select name="ukm_id" class="auth-select" style="padding-left:1rem;">
                        <option value="0">-- Pilih UKM --</option>
                        <?php foreach ($ukmData as $ukm): ?>
                            <option value="<?= $ukm['id'] ?>" <?= (int)($user['ukm_id'] ?? 0) === (int)$ukm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($ukm['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="auth-btn auth-btn-primary" style="margin-top:0;">Simpan Profil</button>
                    <a href="/" class="text-sm text-dark-400 hover:text-dark-100">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
const ormawaData = <?= json_encode($ormawaData) ?>;
const fakultasSelect = document.getElementById('fakultas');
const ormawaSelect = document.getElementById('ormawa');

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
    renderOrmawaOptions(fakultasSelect.value, <?= json_encode($user['ormawa'] ?? '') ?>);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
