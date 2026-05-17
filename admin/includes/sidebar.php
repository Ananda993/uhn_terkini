<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Count pending informasi for badge
$pendingCount = $conn->query("SELECT COUNT(*) as c FROM informasi WHERE status='pending'")->fetch_assoc()['c'];
?>
<!-- Sidebar Overlay (mobile) -->
<div class="admin-sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="/admin/" style="text-decoration:none; display:flex; align-items:center; gap:0.75rem;">
            <img src="/foto/logo_uhn.png" alt="UHN Logo" style="height:2.25rem; width:2.25rem; object-fit:contain;">
            <div>
                <div style="font-size:1.125rem; font-weight:700; font-family:'Space Grotesk',sans-serif; background:linear-gradient(135deg,#818cf8,#38bdf8); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;">UHN Terkini</div>
                <div style="font-size:0.5625rem; color:#475569; letter-spacing:0.15em; text-transform:uppercase;">Admin Panel</div>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section">Menu Utama</div>

        <a href="/admin/" class="sidebar-link <?= $currentPage === 'index' ? 'active' : '' ?>">
            <span class="icon"><i class="fas fa-chart-pie"></i></span>
            Dashboard
        </a>

        <a href="/admin/users.php" class="sidebar-link <?= $currentPage === 'users' ? 'active' : '' ?>">
            <span class="icon"><i class="fas fa-users"></i></span>
            Pengguna
        </a>

        <a href="/admin/categories.php" class="sidebar-link <?= $currentPage === 'categories' ? 'active' : '' ?>">
            <span class="icon"><i class="fas fa-th-large"></i></span>
            Kategori
        </a>

        <a href="/admin/informasi.php" class="sidebar-link <?= $currentPage === 'informasi' ? 'active' : '' ?>">
            <span class="icon"><i class="fas fa-newspaper"></i></span>
            Informasi
            <?php if ($pendingCount > 0): ?>
            <span class="badge" style="background:rgba(245,158,11,0.15); color:#fbbf24;"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>

        <a href="/admin/berita-upload.php" class="sidebar-link <?= $currentPage === 'berita-upload' ? 'active' : '' ?>">
            <span class="icon"><i class="fas fa-pen-nib"></i></span>
            Upload Berita
        </a>

        <div class="sidebar-section" style="margin-top:2rem;">Lainnya</div>

        <a href="/" class="sidebar-link" target="_blank">
            <span class="icon"><i class="fas fa-external-link-alt"></i></span>
            Lihat Website
        </a>

        <a href="/auth/logout.php" class="sidebar-link" style="color:#f87171;">
            <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
            Logout
        </a>
    </nav>

    <!-- Profile -->
    <div class="sidebar-profile">
        <div style="width:2.25rem; height:2.25rem; border-radius:0.625rem; background:linear-gradient(135deg,#6366f1,#4f46e5); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8125rem; color: #e2e8f0; flex-shrink:0;">
            <?= strtoupper(substr($_SESSION['user_nama'] ?? 'A', 0, 1)) ?>
        </div>
        <div style="min-width:0;">
            <div style="font-size:0.8125rem; font-weight:600; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($_SESSION['user_nama'] ?? 'Admin') ?></div>
            <div style="font-size:0.6875rem; color:#64748b;">Administrator</div>
        </div>
    </div>
</aside>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
</script>
