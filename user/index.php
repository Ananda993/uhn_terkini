<?php
$pageTitle = 'Berita Terbaru';
require_once __DIR__ . '/../config/database.php';

$search = trim($_GET['search'] ?? '');
$sql = "SELECT i.*, c.nama as kategori, c.icon as kategori_icon, c.warna as kategori_warna, u.nama as publisher_nama
    FROM informasi i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN users u ON i.user_id = u.id
    WHERE i.status = 'approved'";

$params = [];
$types = '';
if ($search !== '') {
    $sql .= " AND (i.judul LIKE ? OR i.deskripsi LIKE ? OR i.sumber LIKE ? OR c.nama LIKE ? OR u.nama LIKE ?)";
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like, $like];
    $types = 'sssss';
}
$sql .= " ORDER BY i.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $items = $stmt->get_result();
    $stmt->close();
} else {
    $items = $conn->query($sql);
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="pt-28 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Hasil Pencarian</h1>
            <p class="text-dark-400 mt-2">Baca informasi kampus yang sudah dipublikasikan.</p>
        </div>

        <form method="GET" class="mb-8">
            <div class="relative max-w-xl">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-dark-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul, kategori, atau publisher..." class="w-full pl-11 pr-4 py-3 rounded-xl bg-dark-800 border border-white/10 text-dark-100 placeholder-dark-500 text-sm focus:outline-none focus:border-primary-500/50 focus:ring-2 focus:ring-primary-500/20 transition-all">
            </div>
        </form>

        <?php if ($search !== ''): ?>
            <p class="text-sm text-dark-400 mb-6">Hasil pencarian untuk: <span class="text-dark-100 font-semibold"><?= htmlspecialchars($search) ?></span></p>
        <?php endif; ?>

        <?php if ($items->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php while ($row = $items->fetch_assoc()): ?>
                    <a href="/user/detail.php?id=<?= $row['id'] ?>" class="glass-card rounded-2xl p-6 hover:translate-y-[-3px] transition-all">
                        <div class="flex items-center gap-2 mb-3">
                            <?php if ($row['kategori']): ?>
                                <span class="category-chip text-[11px]" style="background: <?= $row['kategori_warna'] ?>15; color: <?= $row['kategori_warna'] ?>; border-color: <?= $row['kategori_warna'] ?>30;">
                                    <i class="<?= $row['kategori_icon'] ?> mr-1"></i><?= htmlspecialchars($row['kategori']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="text-[11px] text-dark-500"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                        </div>
                        <h3 class="text-lg font-semibold text-dark-100 mb-2 line-clamp-2"><?= htmlspecialchars($row['judul']) ?></h3>
                        <p class="text-sm text-dark-400 line-clamp-2">
                            <?= htmlspecialchars(strip_tags($row['deskripsi'])) ?>
                        </p>
                        <div class="mt-4 flex items-center justify-between text-xs text-dark-500">
                            <span>Oleh <?= htmlspecialchars($row['publisher_nama'] ?? 'Publisher') ?></span>
                            <span><i class="fas fa-eye"></i> <?= number_format($row['views']) ?></span>
                        </div>
                        <?php if (!empty($row['sumber'])): ?>
                            <div class="mt-2 text-[11px] text-dark-500">Sumber: <?= htmlspecialchars($row['sumber']) ?></div>
                        <?php endif; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="glass-card rounded-2xl p-10 text-center">
                <i class="fas fa-inbox text-3xl text-dark-400"></i>
                <p class="text-sm text-dark-400 mt-3">Tidak ada berita yang cocok.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
