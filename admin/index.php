<?php
$adminTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalInfo = $conn->query("SELECT COUNT(*) as c FROM informasi")->fetch_assoc()['c'];
$totalViews = $conn->query("SELECT COALESCE(SUM(views),0) as c FROM informasi WHERE status='approved'")->fetch_assoc()['c'];
$totalCategories = $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'];
$pendingInfo = $conn->query("SELECT COUNT(*) as c FROM informasi WHERE status='pending'")->fetch_assoc()['c'];

// Recent pending informasi
$recentPending = $conn->query("
    SELECT i.*, u.nama as publisher_nama, c.nama as kategori
    FROM informasi i
    LEFT JOIN users u ON i.user_id = u.id
    LEFT JOIN categories c ON i.category_id = c.id
    WHERE i.status = 'pending'
    ORDER BY i.created_at DESC LIMIT 5
");

// Recent users
$recentUsers = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Monthly data for chart
$monthlyData = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total
    FROM informasi
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY bulan ASC
");
$chartLabels = [];
$chartValues = [];
while ($row = $monthlyData->fetch_assoc()) {
    $chartLabels[] = date('M Y', strtotime($row['bulan'] . '-01'));
    $chartValues[] = $row['total'];
}
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <!-- Top Bar -->
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:1rem;">
            <button onclick="toggleSidebar()" class="lg:hidden" style="background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; display:none;" id="sidebarToggleBtn">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color: #e2e8f0; margin:0; font-family:'Space Grotesk',sans-serif;">Dashboard</h1>
                <p style="font-size:0.75rem; color:#64748b; margin:0;">Selamat datang kembali, <?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin') ?>!</p>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <span style="font-size:0.75rem; color:#64748b;"><i class="fas fa-calendar-alt" style="margin-right:0.375rem;"></i><?= date('d M Y') ?></span>
        </div>
    </div>

    <div class="admin-content">
        <!-- Stat Cards -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.25rem; margin-bottom:2rem;">
            <div class="stat-card" style="--card-accent:#6366f1;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                    <div class="stat-icon" style="background:rgba(99,102,241,0.1); color:#818cf8;"><i class="fas fa-users"></i></div>
                    <span style="font-size:0.6875rem; color:#34d399; background:rgba(16,185,129,0.1); padding:0.25rem 0.5rem; border-radius:9999px;"><i class="fas fa-arrow-up" style="margin-right:0.25rem;"></i>Aktif</span>
                </div>
                <div class="stat-value"><?= number_format($totalUsers) ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>

            <div class="stat-card" style="--card-accent:#0ea5e9;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                    <div class="stat-icon" style="background:rgba(14,165,233,0.1); color:#38bdf8;"><i class="fas fa-newspaper"></i></div>
                    <?php if ($pendingInfo > 0): ?>
                    <span style="font-size:0.6875rem; color:#fbbf24; background:rgba(245,158,11,0.1); padding:0.25rem 0.5rem; border-radius:9999px;"><?= $pendingInfo ?> pending</span>
                    <?php endif; ?>
                </div>
                <div class="stat-value"><?= number_format($totalInfo) ?></div>
                <div class="stat-label">Total Informasi</div>
            </div>

            <div class="stat-card" style="--card-accent:#f59e0b;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                    <div class="stat-icon" style="background:rgba(245,158,11,0.1); color:#fbbf24;"><i class="fas fa-eye"></i></div>
                </div>
                <div class="stat-value"><?= number_format($totalViews) ?></div>
                <div class="stat-label">Total Views</div>
            </div>

            <div class="stat-card" style="--card-accent:#10b981;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                    <div class="stat-icon" style="background:rgba(16,185,129,0.1); color:#34d399;"><i class="fas fa-layer-group"></i></div>
                </div>
                <div class="stat-value"><?= number_format($totalCategories) ?></div>
                <div class="stat-label">Kategori</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">
            <!-- Pending Informasi -->
            <div class="admin-card" style="grid-column: span 2 / span 2;">
                <div class="admin-card-header">
                    <div>
                        <h3 style="font-size:0.9375rem; font-weight:600; color: #e2e8f0; margin:0;">Informasi Menunggu Validasi</h3>
                        <p style="font-size:0.75rem; color:#64748b; margin:0.25rem 0 0;">Approve atau reject informasi dari publisher</p>
                    </div>
                    <a href="/admin/informasi.php?status=pending" style="font-size:0.75rem; color:#818cf8; text-decoration:none;">Lihat Semua →</a>
                </div>
                <div class="admin-card-body">
                    <?php if ($recentPending->num_rows > 0): ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Publisher</th>
                                <th>Kategori</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $recentPending->fetch_assoc()): ?>
                            <tr>
                                <td style="max-width:250px;">
                                    <div style="font-weight:500; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($item['judul']) ?></div>
                                </td>
                                <td style="color:#94a3b8;"><?= htmlspecialchars($item['publisher_nama'] ?? '-') ?></td>
                                <td><span class="badge-pending" style="font-size:0.6875rem;"><?= htmlspecialchars($item['kategori'] ?? '-') ?></span></td>
                                <td style="color:#64748b; font-size:0.8125rem;"><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div style="display:flex; gap:0.375rem;">
                                        <form method="POST" action="/admin/informasi.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-action btn-approve"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" action="/admin/informasi.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn-action btn-reject"><i class="fas fa-times"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div style="padding:3rem; text-align:center;">
                        <i class="fas fa-check-circle" style="font-size:2.5rem; color:#334155; margin-bottom:0.75rem; display:block;"></i>
                        <p style="color:#64748b; font-size:0.875rem; margin:0;">Tidak ada informasi yang menunggu validasi</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <h3 style="font-size:0.9375rem; font-weight:600; color: #e2e8f0; margin:0;">Pengguna Terbaru</h3>
                    </div>
                    <a href="/admin/users.php" style="font-size:0.75rem; color:#818cf8; text-decoration:none;">Semua →</a>
                </div>
                <div class="admin-card-body" style="padding:0.5rem 0;">
                    <?php while ($user = $recentUsers->fetch_assoc()): ?>
                    <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1.5rem; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                        <div style="width:2.25rem; height:2.25rem; border-radius:0.625rem; background:linear-gradient(135deg,<?= $user['role']==='admin'?'#6366f1,#4f46e5':($user['role']==='publisher'?'#0ea5e9,#0284c7':'#8b5cf6,#7c3aed') ?>); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.75rem; color: #e2e8f0; flex-shrink:0;">
                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                        </div>
                        <div style="min-width:0; flex:1;">
                            <div style="font-size:0.8125rem; font-weight:500; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($user['nama']) ?></div>
                            <div style="font-size:0.6875rem; color:#64748b;"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                        <span class="badge-<?= $user['role'] ?>"><?= $user['role'] ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 style="font-size:0.9375rem; font-weight:600; color: #e2e8f0; margin:0;">Ringkasan Status</h3>
                </div>
                <div class="admin-card-body" style="padding:1.5rem;">
                    <?php
                    $statusCounts = $conn->query("SELECT status, COUNT(*) as total FROM informasi GROUP BY status");
                    $statuses = ['approved' => 0, 'pending' => 0, 'rejected' => 0];
                    while ($s = $statusCounts->fetch_assoc()) {
                        $statuses[$s['status']] = $s['total'];
                    }
                    $totalAll = max(array_sum($statuses), 1);
                    ?>
                    <div style="space-y:1rem;">
                        <!-- Approved -->
                        <div style="margin-bottom:1.25rem;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.375rem;">
                                <span style="font-size:0.8125rem; color:#94a3b8;">Approved</span>
                                <span style="font-size:0.8125rem; font-weight:600; color:#34d399;"><?= $statuses['approved'] ?></span>
                            </div>
                            <div style="height:6px; background:rgba(255,255,255,0.05); border-radius:3px; overflow:hidden;">
                                <div style="height:100%; width:<?= round($statuses['approved']/$totalAll*100) ?>%; background:linear-gradient(90deg,#10b981,#34d399); border-radius:3px; transition:width 1s;"></div>
                            </div>
                        </div>
                        <!-- Pending -->
                        <div style="margin-bottom:1.25rem;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.375rem;">
                                <span style="font-size:0.8125rem; color:#94a3b8;">Pending</span>
                                <span style="font-size:0.8125rem; font-weight:600; color:#fbbf24;"><?= $statuses['pending'] ?></span>
                            </div>
                            <div style="height:6px; background:rgba(255,255,255,0.05); border-radius:3px; overflow:hidden;">
                                <div style="height:100%; width:<?= round($statuses['pending']/$totalAll*100) ?>%; background:linear-gradient(90deg,#f59e0b,#fbbf24); border-radius:3px; transition:width 1s;"></div>
                            </div>
                        </div>
                        <!-- Rejected -->
                        <div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:0.375rem;">
                                <span style="font-size:0.8125rem; color:#94a3b8;">Rejected</span>
                                <span style="font-size:0.8125rem; font-weight:600; color:#f87171;"><?= $statuses['rejected'] ?></span>
                            </div>
                            <div style="height:6px; background:rgba(255,255,255,0.05); border-radius:3px; overflow:hidden;">
                                <div style="height:100%; width:<?= round($statuses['rejected']/$totalAll*100) ?>%; background:linear-gradient(90deg,#ef4444,#f87171); border-radius:3px; transition:width 1s;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 1024px) {
    #sidebarToggleBtn { display: block !important; }
}
@media (max-width: 768px) {
    div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
    div[style*="grid-column: span 2"] { grid-column: span 1 !important; }
}
</style>
</body>
</html>
