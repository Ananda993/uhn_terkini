<?php
$pageTitle = 'Dashboard Publisher';
require_once __DIR__ . '/../config/database.php';
requireLogin();
if (!isPublisher()) {
    header('Location: /');
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
$items = $conn->query(
    "SELECT i.*, c.nama as kategori, c.warna as kategori_warna
     FROM informasi i
     LEFT JOIN categories c ON i.category_id = c.id
     WHERE i.user_id = $userId
     ORDER BY i.created_at DESC"
);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="pt-28 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Dashboard Publisher</h1>
                <p class="text-dark-400 mt-2">Kelola berita yang kamu buat.</p>
            </div>
            <a href="/publisher/create.php" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 text-dark-100 font-semibold shadow-lg shadow-primary-600/20 hover:shadow-primary-500/30 transition-all">
                <i class="fas fa-pen"></i>
                Tulis Berita
            </a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
            <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                Berita berhasil dikirim untuk ditinjau.
            </div>
        <?php endif; ?>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-white/5">
                <h2 class="text-lg font-semibold text-dark-100">Berita Saya</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-dark-400 border-b border-white/5">
                        <tr>
                            <th class="px-5 py-3 font-medium">Judul</th>
                            <th class="px-5 py-3 font-medium">Kategori</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php if ($items->num_rows === 0): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-dark-400">Belum ada berita.</td>
                            </tr>
                        <?php endif; ?>
                        <?php while ($row = $items->fetch_assoc()): ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-5 py-4">
                                    <div class="font-semibold text-dark-100 truncate max-w-[360px]">
                                        <?= htmlspecialchars($row['judul']) ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if ($row['kategori']): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background: <?= $row['kategori_warna'] ?>15; color: <?= $row['kategori_warna'] ?>;">
                                            <?= htmlspecialchars($row['kategori']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-dark-500">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?php
                                        $status = $row['status'];
                                        $statusColor = $status === 'approved' ? 'text-emerald-300 bg-emerald-500/10' : ($status === 'rejected' ? 'text-red-300 bg-red-500/10' : 'text-amber-300 bg-amber-500/10');
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusColor ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-dark-400">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
