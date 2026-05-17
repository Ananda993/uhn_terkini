<?php
$adminTitle = 'Kelola Pengguna';
include __DIR__ . '/includes/header.php';

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

    if ($action === 'add') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'mahasiswa';

        if (!empty($nama) && !empty($email) && !empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $nama, $email, $hashed, $role);
            $stmt->execute();
            $stmt->close();
        }
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
    $where = "WHERE nama LIKE ? OR email LIKE ?";
    $searchParam = "%$search%";
    $params = [$searchParam, $searchParam];
    $types = 'ss';
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
                <input type="text" name="search" class="admin-search" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="auth-alert auth-alert-success" style="max-width:400px; margin-bottom:1rem;">
            <i class="fas fa-check-circle"></i>
            <?= $_GET['msg'] === 'deleted' ? 'User berhasil dihapus.' : ($_GET['msg'] === 'updated' ? 'Role berhasil diperbarui.' : 'User berhasil ditambahkan.') ?>
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
                            <th>Email</th>
                            <th>Role</th>
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
                                        <?php if (!empty($user['nim'])): ?>
                                        <div style="font-size:0.6875rem; color:#64748b;">NIM: <?= htmlspecialchars($user['nim']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#94a3b8; font-size:0.8125rem;"><?= htmlspecialchars($user['email']) ?></td>
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
                            <td style="color:#64748b; font-size:0.8125rem;"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                                <form method="POST" onsubmit="return confirm('Hapus user ini?')" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                                <?php else: ?>
                                <span style="font-size:0.6875rem; color:#475569;">Anda</span>
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
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Email</label>
                    <input type="email" name="email" class="auth-input" style="padding-left:1rem;" placeholder="email@uhn.ac.id" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Password</label>
                    <input type="password" name="password" class="auth-input" style="padding-left:1rem;" placeholder="Min. 6 karakter" required minlength="6">
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

<style>
@media (max-width: 1024px) { #sidebarToggleBtn { display: block !important; } }
@media (max-width: 768px) { .admin-table thead th:nth-child(3), .admin-table tbody td:nth-child(3) { display: none; } }
</style>
</body>
</html>
