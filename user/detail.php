<?php
$pageTitle = 'Detail Berita';
require_once __DIR__ . '/../config/database.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /user/index.php');
    exit;
}

// Fetch data
$stmt = $conn->prepare(
    "SELECT i.*, c.nama as kategori, c.icon as kategori_icon, c.warna as kategori_warna, u.nama as publisher_nama
     FROM informasi i
     LEFT JOIN categories c ON i.category_id = c.id
     LEFT JOIN users u ON i.user_id = u.id
     WHERE i.id = ? AND i.status = 'approved'"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    header('Location: /user/index.php');
    exit;
}

// Update views
$conn->query("UPDATE informasi SET views = views + 1 WHERE id = $id");

// Bookmark handling
$bookmarked = false;
if (isLoggedIn()) {
    $userId = (int)$_SESSION['user_id'];
    $check = $conn->prepare('SELECT id FROM bookmarks WHERE user_id = ? AND informasi_id = ?');
    $check->bind_param('ii', $userId, $id);
    $check->execute();
    $bookmarked = $check->get_result()->num_rows > 0;
    $check->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'bookmark') {
            $stmt = $conn->prepare('INSERT IGNORE INTO bookmarks (user_id, informasi_id) VALUES (?, ?)');
            $stmt->bind_param('ii', $userId, $id);
            $stmt->execute();
            $stmt->close();
            header('Location: /user/detail.php?id=' . $id);
            exit;
        }
        if ($action === 'unbookmark') {
            $stmt = $conn->prepare('DELETE FROM bookmarks WHERE user_id = ? AND informasi_id = ?');
            $stmt->bind_param('ii', $userId, $id);
            $stmt->execute();
            $stmt->close();
            header('Location: /user/detail.php?id=' . $id);
            exit;
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="pt-28 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="/user/index.php" class="text-sm text-primary-400 hover:text-primary-300"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
        </div>

        <div class="glass-card rounded-2xl p-8">
            <div class="flex items-center gap-3 mb-4">
                <?php if ($item['kategori']): ?>
                <span class="category-chip text-[11px]" style="background: <?= $item['kategori_warna'] ?>15; color: <?= $item['kategori_warna'] ?>; border-color: <?= $item['kategori_warna'] ?>30;">
                    <i class="<?= $item['kategori_icon'] ?> mr-1"></i><?= htmlspecialchars($item['kategori']) ?>
                </span>
                <?php endif; ?>
                <span class="text-xs text-dark-500"><?= date('d M Y', strtotime($item['created_at'])) ?></span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold font-space text-dark-100 mb-3"><?= htmlspecialchars($item['judul']) ?></h1>
            <div class="flex flex-wrap items-center gap-3 text-xs text-dark-500 mb-6">
                <span>Oleh <?= htmlspecialchars($item['publisher_nama'] ?? 'Publisher') ?></span>
                <?php if (!empty($item['sumber'])): ?>
                    <span>Sumber: <?= htmlspecialchars($item['sumber']) ?></span>
                <?php endif; ?>
                <span><i class="fas fa-eye"></i> <?= number_format($item['views'] + 1) ?></span>
                <?php if ($item['deadline']): ?>
                    <span><i class="fas fa-clock"></i> Deadline: <?= date('d M Y', strtotime($item['deadline'])) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($item['gambar']): ?>
                <img src="/foto/<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['judul']) ?>" class="w-full rounded-xl mb-6">
            <?php endif; ?>

            <article class="prose prose-invert max-w-none">
                <?= $item['deskripsi'] ?>
            </article>

            <div class="mt-8 flex items-center gap-3">
                <?php if (isLoggedIn()): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?= $bookmarked ? 'unbookmark' : 'bookmark' ?>">
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold <?= $bookmarked ? 'bg-amber-500/10 text-amber-300 border border-amber-500/30' : 'bg-white/5 text-dark-200 border border-white/10' ?>">
                            <i class="fas fa-bookmark mr-2"></i><?= $bookmarked ? 'Tersimpan' : 'Bookmark' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="/auth/login.php" class="text-sm text-primary-400 hover:text-primary-300">Login untuk bookmark</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
