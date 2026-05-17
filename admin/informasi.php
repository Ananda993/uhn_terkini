<?php
$adminTitle = 'Kelola Informasi';
include __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'approve' && $id) {
        $conn->query("UPDATE informasi SET status='approved' WHERE id=$id");
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/admin/informasi.php');
        exit;
    }
    if ($action === 'reject' && $id) {
        $conn->query("UPDATE informasi SET status='rejected' WHERE id=$id");
        header('Location: ' . $_SERVER['HTTP_REFERER'] ?? '/admin/informasi.php');
        exit;
    }
    if ($action === 'delete' && $id) {
        $conn->query("DELETE FROM informasi WHERE id=$id");
        header('Location: /admin/informasi.php?msg=deleted');
        exit;
    }
}

// Filters
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = '';

if (!empty($status) && in_array($status, ['pending','approved','rejected'])) {
    $where[] = "i.status = ?";
    $params[] = $status;
    $types .= 's';
}
if (!empty($search)) {
    $where[] = "i.judul LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count
$countSql = "SELECT COUNT(*) as total FROM informasi i $whereClause";
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

// Fetch
$sql = "SELECT i.*, u.nama as publisher_nama, c.nama as kategori, c.warna as kategori_warna
    FROM informasi i
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN categories c ON i.category_id = c.id
    $whereClause
    ORDER BY i.created_at DESC LIMIT $limit OFFSET $offset";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $informasi = $stmt->get_result();
    $stmt->close();
} else {
    $informasi = $conn->query($sql);
}

// Status counts
$allCount = $conn->query("SELECT COUNT(*) as c FROM informasi")->fetch_assoc()['c'];
$pendCount = $conn->query("SELECT COUNT(*) as c FROM informasi WHERE status='pending'")->fetch_assoc()['c'];
$appCount = $conn->query("SELECT COUNT(*) as c FROM informasi WHERE status='approved'")->fetch_assoc()['c'];
$rejCount = $conn->query("SELECT COUNT(*) as c FROM informasi WHERE status='rejected'")->fetch_assoc()['c'];
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:1rem;">
            <button onclick="toggleSidebar()" style="background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; display:none;" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color: #e2e8f0; margin:0; font-family:'Space Grotesk',sans-serif;">Kelola Informasi</h1>
                <p style="font-size:0.75rem; color:#64748b; margin:0;">Validasi dan kelola seluruh informasi</p>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <!-- Status Tabs -->
        <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
            <a href="/admin/informasi.php" style="padding:0.5rem 1rem; border-radius:0.5rem; font-size:0.8125rem; font-weight:500; text-decoration:none; transition:all 0.25s; <?= empty($status) ? 'background:rgba(99,102,241,0.1); color:#818cf8; border:1px solid rgba(99,102,241,0.2);' : 'background:rgba(255,255,255,0.03); color:#94a3b8; border:1px solid rgba(255,255,255,0.06);' ?>">
                Semua <span style="margin-left:0.25rem; opacity:0.7;"><?= $allCount ?></span>
            </a>
            <a href="/admin/informasi.php?status=pending" style="padding:0.5rem 1rem; border-radius:0.5rem; font-size:0.8125rem; font-weight:500; text-decoration:none; transition:all 0.25s; <?= $status==='pending' ? 'background:rgba(245,158,11,0.1); color:#fbbf24; border:1px solid rgba(245,158,11,0.2);' : 'background:rgba(255,255,255,0.03); color:#94a3b8; border:1px solid rgba(255,255,255,0.06);' ?>">
                Pending <span style="margin-left:0.25rem; opacity:0.7;"><?= $pendCount ?></span>
            </a>
            <a href="/admin/informasi.php?status=approved" style="padding:0.5rem 1rem; border-radius:0.5rem; font-size:0.8125rem; font-weight:500; text-decoration:none; transition:all 0.25s; <?= $status==='approved' ? 'background:rgba(16,185,129,0.1); color:#34d399; border:1px solid rgba(16,185,129,0.2);' : 'background:rgba(255,255,255,0.03); color:#94a3b8; border:1px solid rgba(255,255,255,0.06);' ?>">
                Approved <span style="margin-left:0.25rem; opacity:0.7;"><?= $appCount ?></span>
            </a>
            <a href="/admin/informasi.php?status=rejected" style="padding:0.5rem 1rem; border-radius:0.5rem; font-size:0.8125rem; font-weight:500; text-decoration:none; transition:all 0.25s; <?= $status==='rejected' ? 'background:rgba(239,68,68,0.1); color:#f87171; border:1px solid rgba(239,68,68,0.2);' : 'background:rgba(255,255,255,0.03); color:#94a3b8; border:1px solid rgba(255,255,255,0.06);' ?>">
                Rejected <span style="margin-left:0.25rem; opacity:0.7;"><?= $rejCount ?></span>
            </a>
        </div>

        <!-- Search -->
        <div style="margin-bottom:1.5rem; position:relative; display:inline-block;">
            <i class="fas fa-search" style="position:absolute; left:0.875rem; top:50%; transform:translateY(-50%); color:#475569; font-size:0.8125rem;"></i>
            <form method="GET">
                <?php if (!empty($status)): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>"><?php endif; ?>
                <input type="text" name="search" class="admin-search" placeholder="Cari judul informasi..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>

        <?php if (isset($_GET['msg'])): ?>
        <div class="auth-alert auth-alert-success" style="max-width:400px; margin-bottom:1rem;">
            <i class="fas fa-check-circle"></i> Berhasil!
        </div>
        <?php endif; ?>

        <!-- Informasi Table -->
        <div class="admin-card">
            <div class="admin-card-body">
                <?php if ($informasi->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Publisher</th>
                            <th>Kategori</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $informasi->fetch_assoc()): ?>
                        <tr>
                            <td style="max-width:250px;">
                                <div style="font-weight:500; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($item['judul']) ?></div>
                                <?php if ($item['deadline']): ?>
                                <div style="font-size:0.6875rem; color:#64748b; margin-top:0.125rem;"><i class="fas fa-clock" style="margin-right:0.25rem;"></i>Deadline: <?= date('d M Y', strtotime($item['deadline'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="color:#94a3b8; font-size:0.8125rem;"><?= htmlspecialchars($item['publisher_nama'] ?? '-') ?></td>
                            <td>
                                <?php if ($item['kategori']): ?>
                                <span style="font-size:0.6875rem; padding:0.25rem 0.5rem; border-radius:9999px; background:<?= $item['kategori_warna'] ?? '#6366f1' ?>15; color:<?= $item['kategori_warna'] ?? '#6366f1' ?>; font-weight:500;">
                                    <?= htmlspecialchars($item['kategori']) ?>
                                </span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td style="color:#94a3b8; font-size:0.8125rem; font-family:'Space Grotesk',sans-serif; font-weight:600;"><?= number_format($item['views']) ?></td>
                            <td><span class="badge-<?= $item['status'] ?>"><?= $item['status'] ?></span></td>
                            <td style="color:#64748b; font-size:0.8125rem;"><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                            <td>
                                <div style="display:flex; gap:0.375rem;">
                                    <?php if ($item['status'] !== 'approved'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-action btn-approve" title="Approve"><i class="fas fa-check"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($item['status'] !== 'rejected'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-action btn-reject" title="Reject"><i class="fas fa-times"></i></button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" onsubmit="return confirm('Hapus informasi ini?')" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="padding:3rem; text-align:center;">
                    <i class="fas fa-inbox" style="font-size:2.5rem; color:#334155; margin-bottom:0.75rem; display:block;"></i>
                    <p style="color:#64748b; font-size:0.875rem; margin:0;">Tidak ada informasi ditemukan</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media (max-width: 1024px) { #sidebarToggleBtn { display: block !important; } }
@media (max-width: 768px) {
    .admin-table thead th:nth-child(2), .admin-table tbody td:nth-child(2),
    .admin-table thead th:nth-child(4), .admin-table tbody td:nth-child(4) { display: none; }
}
</style>
</body>
</html>
