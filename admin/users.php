<?php
$adminTitle = 'Kelola Pengguna';
include __DIR__ . '/includes/header.php';

$feedback = $_GET['msg'] ?? '';
$errorCode = $_GET['err'] ?? '';
$errorMessage = '';
if ($feedback === 'error') {
    $errorMessage = match ($errorCode) {
        'required' => 'Semua field wajib diisi.',
        'email' => 'Format email tidak valid.',
        'email_exists' => 'Email sudah terdaftar. Gunakan email lain.',
        'password' => 'Password minimal 6 karakter.',
        'confirm' => 'Konfirmasi password tidak cocok.',
        default => 'Terjadi kesalahan. Silakan coba lagi.'
    };
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($id !== (int)$_SESSION['user_id']) {
            $conn->query("DELETE FROM users WHERE id = $id");
        }
        header('Location: /admin/users.php?msg=deleted');
        exit;
    }

    if ($action === 'update_role' && isset($_POST['id'], $_POST['role'])) {
        $id = (int)$_POST['id'];
        $role = $_POST['role'];
        if (in_array($role, ['admin','publisher','mahasiswa'])) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: /admin/users.php?msg=updated');
        exit;
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
        $fakultas = trim($_POST['fakultas'] ?? '');
        $ormawa = trim($_POST['ormawa'] ?? '');
        $role = $_POST['role'] ?? 'mahasiswa';
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($id <= 0 || $nama === '' || $email === '' || $noHp === '' || $fakultas === '' || $ormawa === '') {
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /admin/users.php?msg=error&err=email');
            exit;
        }
        if (strlen($noHp) < 8) {
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }

        $check = $conn->prepare('SELECT id FROM fakultas WHERE nama = ? AND is_active = 1');
        $check->bind_param('s', $fakultas);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $check->close();
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }
        $check->close();

        $check = $conn->prepare('SELECT id FROM ormawa WHERE nama = ? AND is_active = 1');
        $check->bind_param('s', $ormawa);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $check->close();
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }
        $check->close();
        if (!in_array($role, ['admin','publisher','mahasiswa'], true)) {
            $role = 'mahasiswa';
        }
        if ($password !== '') {
            if (strlen($password) < 6) {
                header('Location: /admin/users.php?msg=error&err=password');
                exit;
            }
            if ($password !== $passwordConfirm) {
                header('Location: /admin/users.php?msg=error&err=confirm');
                exit;
            }
        }

        $check = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $check->bind_param('si', $email, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            header('Location: /admin/users.php?msg=error&err=email_exists');
            exit;
        }
        $check->close();

        if ($password !== '') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET nama = ?, email = ?, password = ?, role = ?, no_hp = ?, fakultas = ?, ormawa = ?, is_active = ? WHERE id = ?');
            $stmt->bind_param('sssssssii', $nama, $email, $hashed, $role, $noHp, $fakultas, $ormawa, $isActive, $id);
        } else {
            $stmt = $conn->prepare('UPDATE users SET nama = ?, email = ?, role = ?, no_hp = ?, fakultas = ?, ormawa = ?, is_active = ? WHERE id = ?');
            $stmt->bind_param('ssssssii', $nama, $email, $role, $noHp, $fakultas, $ormawa, $isActive, $id);
        }
        $stmt->execute();
        $stmt->close();

        header('Location: /admin/users.php?msg=updated');
        exit;
    }

    if ($action === 'add') {
        $nama = trim($_POST['nama'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
        $fakultas = trim($_POST['fakultas'] ?? '');
        $ormawa = trim($_POST['ormawa'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $role = $_POST['role'] ?? 'mahasiswa';

        if ($nama === '' || $email === '' || $password === '' || $noHp === '' || $fakultas === '' || $ormawa === '') {
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: /admin/users.php?msg=error&err=email');
            exit;
        }
        if (strlen($noHp) < 8) {
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }

        $check = $conn->prepare('SELECT id FROM fakultas WHERE nama = ? AND is_active = 1');
        $check->bind_param('s', $fakultas);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $check->close();
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }
        $check->close();

        $check = $conn->prepare('SELECT id FROM ormawa WHERE nama = ? AND is_active = 1');
        $check->bind_param('s', $ormawa);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            $check->close();
            header('Location: /admin/users.php?msg=error&err=required');
            exit;
        }
        $check->close();
        if (strlen($password) < 6) {
            header('Location: /admin/users.php?msg=error&err=password');
            exit;
        }
        if ($password !== $passwordConfirm) {
            header('Location: /admin/users.php?msg=error&err=confirm');
            exit;
        }
        if (!in_array($role, ['admin','publisher','mahasiswa'], true)) {
            $role = 'mahasiswa';
        }

        $check = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            header('Location: /admin/users.php?msg=error&err=email_exists');
            exit;
        }
        $check->close();

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, no_hp, fakultas, ormawa, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssss", $nama, $email, $hashed, $role, $noHp, $fakultas, $ormawa);
        $stmt->execute();
        $stmt->close();

        header('Location: /admin/users.php?msg=added');
        exit;
    }
}

// Search & Pagination
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
$types = '';
if (!empty($search)) {
    $where = "WHERE nama LIKE ? OR email LIKE ? OR no_hp LIKE ? OR fakultas LIKE ? OR ormawa LIKE ?";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
    $types = 'sssss';
}

// Count total
$countSql = "SELECT COUNT(*) as total FROM users $where";
if (!empty($params)) {
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $totalRows = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
} else {
    $totalRows = $conn->query($countSql)->fetch_assoc()['total'];
}
$totalPages = ceil($totalRows / $limit);

// Fetch users
$sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users = $stmt->get_result();
    $stmt->close();
} else {
    $users = $conn->query($sql);
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

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:1rem;">
            <button onclick="toggleSidebar()" style="background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; display:none;" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color: #e2e8f0; margin:0; font-family:'Space Grotesk',sans-serif;">Kelola Pengguna</h1>
                <p style="font-size:0.75rem; color:#64748b; margin:0;"><?= $totalRows ?> pengguna terdaftar</p>
            </div>
        </div>
        <button onclick="document.getElementById('addUserModal').classList.add('show')" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem; font-size:0.8125rem;">
            <i class="fas fa-plus"></i> Tambah User
        </button>
    </div>

    <div class="admin-content">
        <!-- Search -->
        <div style="margin-bottom:1.5rem; position:relative; display:inline-block;">
            <i class="fas fa-search" style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); color:#475569; font-size:0.8125rem;"></i>
            <form method="GET">
                <input type="text" name="search" class="admin-search" placeholder="Cari nama, email, No HP, fakultas, atau ormawa..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <?php if ($errorMessage): ?>
        <div class="auth-alert auth-alert-error" style="max-width:400px; margin-bottom:1rem;">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
        <?php elseif (isset($_GET['msg'])): ?>
        <div class="auth-alert auth-alert-success" style="max-width:400px; margin-bottom:1rem;">
            <i class="fas fa-check-circle"></i>
            <?= $_GET['msg'] === 'deleted' ? 'User berhasil dihapus.' : ($_GET['msg'] === 'updated' ? 'User berhasil diperbarui.' : 'User berhasil ditambahkan.') ?>
        </div>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="admin-card">
            <div class="admin-card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pengguna</th>
                            <th>No HP</th>
                            <th>Email</th>
                            <th>Fakultas</th>
                            <th>ORMAWA</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = $offset; while ($user = $users->fetch_assoc()): $no++; ?>
                        <tr>
                            <td style="color:#64748b;"><?= $no ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.75rem;">
                                    <div style="width:2rem; height:2rem; border-radius:0.5rem; background:linear-gradient(135deg,<?= $user['role']==='admin'?'#6366f1,#4f46e5':($user['role']==='publisher'?'#0ea5e9,#0284c7':'#8b5cf6,#7c3aed') ?>); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.6875rem; color: #e2e8f0; flex-shrink:0;">
                                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:500; color:#e2e8f0;"><?= htmlspecialchars($user['nama']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#94a3b8; font-size:0.8125rem;">
                                <?= !empty($user['no_hp']) ? htmlspecialchars($user['no_hp']) : '-' ?>
                            </td>
                            <td style="color:#94a3b8; font-size:0.8125rem;"><?= htmlspecialchars($user['email']) ?></td>
                            <td style="color:#94a3b8; font-size:0.8125rem;">
                                <?= !empty($user['fakultas']) ? htmlspecialchars($user['fakultas']) : '-' ?>
                            </td>
                            <td style="color:#94a3b8; font-size:0.8125rem;">
                                <?= !empty($user['ormawa']) ? htmlspecialchars($user['ormawa']) : '-' ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="update_role">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <select name="role" onchange="this.form.submit()" style="background:transparent; border:none; outline:none; cursor:pointer; font-size:0.75rem; font-weight:600; padding:0.25rem 0.5rem; border-radius:9999px;
                                        <?php if($user['role']==='admin'): ?>color:#818cf8; background:rgba(99,102,241,0.1);
                                        <?php elseif($user['role']==='publisher'): ?>color:#38bdf8; background:rgba(14,165,233,0.1);
                                        <?php else: ?>color:#a78bfa; background:rgba(139,92,246,0.1);<?php endif; ?>">
                                        <option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>Admin</option>
                                        <option value="publisher" <?= $user['role']==='publisher'?'selected':'' ?>>Publisher</option>
                                        <option value="mahasiswa" <?= $user['role']==='mahasiswa'?'selected':'' ?>>Mahasiswa</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <span class="badge-<?= $user['is_active'] ? 'approved' : 'rejected' ?>">
                                    <?= $user['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td style="color:#64748b; font-size:0.8125rem;"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button type="button" class="btn-action" style="background:rgba(255,255,255,0.08); color:#e2e8f0;" onclick="openEditUserModal(this)"
                                    data-id="<?= $user['id'] ?>"
                                    data-nama="<?= htmlspecialchars($user['nama'], ENT_QUOTES) ?>"
                                    data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>"
                                    data-no-hp="<?= htmlspecialchars($user['no_hp'] ?? '', ENT_QUOTES) ?>"
                                    data-fakultas="<?= htmlspecialchars($user['fakultas'] ?? '', ENT_QUOTES) ?>"
                                    data-ormawa="<?= htmlspecialchars($user['ormawa'] ?? '', ENT_QUOTES) ?>"
                                    data-role="<?= $user['role'] ?>"
                                    data-active="<?= (int)$user['is_active'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Hapus user ini?')" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                                <?php else: ?>
                                <span style="font-size:0.6875rem; color:#475569; margin-left:0.5rem;">Anda</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div class="admin-modal-overlay" id="addUserModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 style="font-size:1rem; font-weight:600; color: #e2e8f0; margin:0;">Tambah Pengguna Baru</h3>
            <button onclick="document.getElementById('addUserModal').classList.remove('show')" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:1.25rem;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Nama Lengkap</label>
                    <input type="text" name="nama" class="auth-input" style="padding-left:1rem;" placeholder="Nama lengkap" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">No HP</label>
                    <input type="text" name="no_hp" class="auth-input" style="padding-left:1rem;" placeholder="Contoh: 081234567890" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Email</label>
                    <input type="email" name="email" class="auth-input" style="padding-left:1rem;" placeholder="email@uhn.ac.id" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Fakultas</label>
                    <select name="fakultas" id="addUserFakultas" class="auth-select" style="padding-left:1rem;" required>
                        <option value="">-- Pilih Fakultas --</option>
                        <?php foreach ($fakultasData as $fakultas): ?>
                            <option value="<?= htmlspecialchars($fakultas) ?>"><?= htmlspecialchars($fakultas) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">ORMAWA</label>
                    <select name="ormawa" id="addUserOrmawa" class="auth-select" style="padding-left:1rem;" required>
                        <option value="">-- Pilih ORMAWA --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Password</label>
                    <input type="password" name="password" class="auth-input" style="padding-left:1rem;" placeholder="Min. 6 karakter" required minlength="6">
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Konfirmasi Password</label>
                    <input type="password" name="password_confirm" class="auth-input" style="padding-left:1rem;" placeholder="Ulangi password" required minlength="6">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Role</label>
                    <select name="role" class="auth-select" style="padding-left:1rem;">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="publisher">Publisher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" onclick="document.getElementById('addUserModal').classList.remove('show')" class="btn-action" style="background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); padding:0.5rem 1rem;">Batal</button>
                <button type="submit" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem;">Tambah User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="admin-modal-overlay" id="editUserModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 style="font-size:1rem; font-weight:600; color: #e2e8f0; margin:0;">Edit Pengguna</h3>
            <button onclick="closeEditUserModal()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:1.25rem;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editUserForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editUserId">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Nama Lengkap</label>
                    <input type="text" name="nama" id="editUserNama" class="auth-input" style="padding-left:1rem;" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">No HP</label>
                    <input type="text" name="no_hp" id="editUserNoHp" class="auth-input" style="padding-left:1rem;" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Email</label>
                    <input type="email" name="email" id="editUserEmail" class="auth-input" style="padding-left:1rem;" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Fakultas</label>
                    <select name="fakultas" id="editUserFakultas" class="auth-select" style="padding-left:1rem;" required>
                        <option value="">-- Pilih Fakultas --</option>
                        <?php foreach ($fakultasData as $fakultas): ?>
                            <option value="<?= htmlspecialchars($fakultas) ?>"><?= htmlspecialchars($fakultas) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">ORMAWA</label>
                    <select name="ormawa" id="editUserOrmawa" class="auth-select" style="padding-left:1rem;" required>
                        <option value="">-- Pilih ORMAWA --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Role</label>
                    <select name="role" id="editUserRole" class="auth-select" style="padding-left:1rem;">
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="publisher">Publisher</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Status</label>
                    <select name="is_active" id="editUserActive" class="auth-select" style="padding-left:1rem;">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Password Baru (Opsional)</label>
                    <input type="password" name="password" id="editUserPassword" class="auth-input" style="padding-left:1rem;" placeholder="Min. 6 karakter" minlength="6">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Konfirmasi Password</label>
                    <input type="password" name="password_confirm" id="editUserPasswordConfirm" class="auth-input" style="padding-left:1rem;" placeholder="Ulangi password" minlength="6">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" onclick="closeEditUserModal()" class="btn-action" style="background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); padding:0.5rem 1rem;">Batal</button>
                <button type="submit" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditUserModal(btn) {
    const modal = document.getElementById('editUserModal');
    document.getElementById('editUserId').value = btn.dataset.id || '';
    document.getElementById('editUserNama').value = btn.dataset.nama || '';
    document.getElementById('editUserEmail').value = btn.dataset.email || '';
    document.getElementById('editUserNoHp').value = btn.dataset.noHp || '';
    document.getElementById('editUserFakultas').value = btn.dataset.fakultas || '';
    document.getElementById('editUserRole').value = btn.dataset.role || 'mahasiswa';
    document.getElementById('editUserActive').value = btn.dataset.active || '1';
    document.getElementById('editUserPassword').value = '';
    document.getElementById('editUserPasswordConfirm').value = '';
    renderOrmawaOptions(document.getElementById('editUserFakultas').value, document.getElementById('editUserOrmawa'), btn.dataset.ormawa || '');
    modal.classList.add('show');
}

function closeEditUserModal() {
    document.getElementById('editUserModal').classList.remove('show');
}

const ormawaData = <?= json_encode($ormawaData) ?>;

function renderOrmawaOptions(selectedFaculty, selectEl, selectedValue) {
    if (!selectEl) {
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

    selectEl.innerHTML = '';
    options.forEach((label) => {
        if (label === '-- Pilih ORMAWA --') {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = label;
            selectEl.appendChild(opt);
            return;
        }
        if (label === 'UNIV' || label === 'FDA' || label === 'FDD' || label === 'FBW' || label === 'FAST' || label === 'Kedokteran') {
            const optGroup = document.createElement('option');
            optGroup.disabled = true;
            optGroup.textContent = '--- ' + label + ' ---';
            selectEl.appendChild(optGroup);
            return;
        }
        if (label === 'COMING SOON') {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Coming soon';
            opt.disabled = true;
            selectEl.appendChild(opt);
            return;
        }
        const opt = document.createElement('option');
        opt.value = label;
        opt.textContent = label;
        if (selectedValue && selectedValue === label) {
            opt.selected = true;
        }
        selectEl.appendChild(opt);
    });
}

const addUserFakultas = document.getElementById('addUserFakultas');
const addUserOrmawa = document.getElementById('addUserOrmawa');
if (addUserFakultas && addUserOrmawa) {
    addUserFakultas.addEventListener('change', (e) => {
        renderOrmawaOptions(e.target.value, addUserOrmawa, '');
    });
    renderOrmawaOptions(addUserFakultas.value, addUserOrmawa, '');
}

const editUserFakultas = document.getElementById('editUserFakultas');
const editUserOrmawa = document.getElementById('editUserOrmawa');
if (editUserFakultas && editUserOrmawa) {
    editUserFakultas.addEventListener('change', (e) => {
        renderOrmawaOptions(e.target.value, editUserOrmawa, '');
    });
}
</script>

<style>
@media (max-width: 1024px) { #sidebarToggleBtn { display: block !important; } }
@media (max-width: 768px) {
    .admin-table thead th:nth-child(3), .admin-table tbody td:nth-child(3),
    .admin-table thead th:nth-child(4), .admin-table tbody td:nth-child(4),
    .admin-table thead th:nth-child(5), .admin-table tbody td:nth-child(5),
    .admin-table thead th:nth-child(6), .admin-table tbody td:nth-child(6),
    .admin-table thead th:nth-child(8), .admin-table tbody td:nth-child(8) { display: none; }
}
</style>
</body>
</html>
