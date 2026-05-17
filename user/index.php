<?php
$pageTitle = 'Berita Terbaru';
require_once __DIR__ . '/../config/database.php';

$items = $conn->query(
    "SELECT i.*, c.nama as kategori, c.icon as kategori_icon, c.warna as kategori_warna, u.nama as publisher_nama
     FROM informasi i
     LEFT JOIN categories c ON i.category_id = c.id
     LEFT JOIN users u ON i.user_id = u.id
     WHERE i.status = 'approved'
     ORDER BY i.created_at DESC"
);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="pt-28 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Berita Terbaru</h1>
            <p class="text-dark-400 mt-2">Baca informasi kampus yang sudah dipublikasikan.</p>
        </div>

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
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
