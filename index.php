<?php
$pageTitle = 'Beranda';
require_once __DIR__ . '/config/database.php';

// Fetch trending informasi (approved, sorted by views)
$trending = $conn->query("
    SELECT i.*, c.nama as kategori, c.icon as kategori_icon, c.warna as kategori_warna, c.slug as kategori_slug,
           u.nama as publisher_nama
    FROM informasi i 
    LEFT JOIN categories c ON i.category_id = c.id 
    LEFT JOIN users u ON i.user_id = u.id
    WHERE i.status = 'approved' AND i.views >= 50
    ORDER BY i.views DESC 
    LIMIT 8
");

// Fetch categories with count
$categories = $conn->query("
    SELECT c.*, COUNT(i.id) as total_info 
    FROM categories c 
    LEFT JOIN informasi i ON c.id = i.category_id AND i.status = 'approved'
    GROUP BY c.id 
    HAVING total_info > 0
    ORDER BY total_info DESC
");

// Fetch deadline-approaching items (urgensi)
$urgent = $conn->query("
    SELECT i.*, c.nama as kategori, c.icon as kategori_icon, c.warna as kategori_warna
    FROM informasi i 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.status = 'approved' AND (i.is_urgent = 1 OR (i.deadline IS NOT NULL AND i.deadline >= CURDATE()))
    ORDER BY i.is_urgent DESC, i.deadline ASC 
    LIMIT 4
");

// Fetch all berita (approved)
$allNewsRows = $conn->query("
    SELECT i.*, c.nama as kategori, c.icon as kategori_icon, c.warna as kategori_warna, u.nama as publisher_nama
    FROM informasi i
    LEFT JOIN categories c ON i.category_id = c.id
    LEFT JOIN users u ON i.user_id = u.id
    WHERE i.status = 'approved'
    ORDER BY i.created_at DESC
");

$allNews = [];
while ($row = $allNewsRows->fetch_assoc()) {
    $allNews[] = $row;
}

// Stats
$totalInfo = $conn->query("SELECT COUNT(*) as c FROM informasi WHERE status='approved'")->fetch_assoc()['c'];
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalViews = $conn->query("SELECT COALESCE(SUM(views),0) as c FROM informasi WHERE status='approved'")->fetch_assoc()['c'];
$totalCategories = $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'];
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

    <!-- ============ HERO SECTION ============ -->
    <section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 grid-pattern"></div>
        
        <!-- Gradient Orbs -->
        <div class="hero-orb absolute top-1/4 left-1/4 w-[500px] h-[500px] rounded-full bg-primary-600/20 blur-[120px] animate-float"></div>
        <div class="hero-orb absolute bottom-1/4 right-1/4 w-[400px] h-[400px] rounded-full bg-accent-500/15 blur-[100px] animate-float-delay"></div>
        <div class="hero-orb absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-purple-600/10 blur-[150px] animate-pulse-glow"></div>
        
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="anim-slide-down inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-sm text-dark-300 mb-8" style="animation-delay: 0.2s;">
                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                <span>Live Trending</span>
                <span class="text-dark-500">•</span>
                <span class="text-primary-400 font-medium"><?= $totalInfo ?> Info Aktif</span>
            </div>
            
            <!-- Title -->
            <h1 class="anim-slide-up hero-title text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black font-space leading-[1.1] mb-6" style="animation-delay: 0.3s;">
                Apa yang sedang
                <br>
                <span class="gradient-text">Trending</span> di UHN?
            </h1>
            
            <!-- Subtitle -->
            <p class="anim-slide-up text-lg sm:text-xl text-dark-400 max-w-2xl mx-auto mb-10 leading-relaxed" style="animation-delay: 0.5s;">
                Portal informasi terpusat berbasis <span class="text-dark-100 font-medium">popularitas</span> & <span class="text-dark-100 font-medium">urgensi</span>. 
                Temukan beasiswa, lomba, seminar, dan kegiatan kampus yang paling dicari.
            </p>
            
            <!-- CTA Buttons -->
            <div class="anim-slide-up flex flex-col sm:flex-row items-center justify-center gap-4" style="animation-delay: 0.7s;">
                <a href="#trending" class="hero-cta-primary group flex items-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-dark-100 font-semibold shadow-2xl shadow-primary-600/25 hover:shadow-primary-500/40 transition-all duration-300 hover:scale-[1.03]">
                    <i class="fas fa-fire text-amber-300"></i>
                    Lihat Trending
                    <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="#kategori" class="hero-cta-secondary flex items-center gap-3 px-8 py-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 text-dark-100 font-semibold transition-all duration-300 hover:scale-[1.03]">
                    <i class="fas fa-compass"></i>
                    Jelajahi Kategori
                </a>
            </div>
            
            <!-- Quick Stats (mini) -->
            <div class="anim-fade-in flex items-center justify-center gap-8 sm:gap-12 mt-16" style="animation-delay: 1s;">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold font-space text-dark-100" data-count="<?= $totalViews ?>" data-suffix="">0</div>
                    <div class="text-xs text-dark-500 mt-1 uppercase tracking-wider">Total Views</div>
                </div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold font-space text-dark-100" data-count="<?= $totalInfo ?>" data-suffix="">0</div>
                    <div class="text-xs text-dark-500 mt-1 uppercase tracking-wider">Informasi</div>
                </div>
                <div class="w-px h-10 bg-white/10"></div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold font-space text-dark-100" data-count="<?= $totalCategories ?>" data-suffix="">0</div>
                    <div class="text-xs text-dark-500 mt-1 uppercase tracking-wider">Kategori</div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Down Indicator -->
        <div class="anim-fade-in absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2" style="animation-delay: 1.5s;">
            <span class="text-[10px] text-dark-500 uppercase tracking-widest">Scroll</span>
            <div class="w-5 h-8 rounded-full border border-white/10 flex items-start justify-center p-1">
                <div class="w-1 h-2 rounded-full bg-primary-400 animate-bounce"></div>
            </div>
        </div>
    </section>

    <!-- ============ DEADLINE / URGENSI SECTION ============ -->
    <section id="urgensi" class="relative py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-12 reveal">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center">
                            <i class="fas fa-clock text-red-400 text-sm"></i>
                        </div>
                        <span class="text-sm font-semibold text-red-400 uppercase tracking-wider">Segera Berakhir</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Jangan Sampai Terlewat!</h2>
                    <p class="text-dark-400 mt-2">Informasi dengan deadline yang semakin dekat</p>
                </div>
            </div>
            
            <!-- Urgent Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <?php while ($item = $urgent->fetch_assoc()):
                    $deadline = new DateTime($item['deadline']);
                    $now = new DateTime();
                    $diff = $now->diff($deadline);
                    $daysLeft = $diff->invert ? 0 : $diff->days;
                    
                    // Urgency color
                    $urgencyColor = match(true) {
                        $daysLeft <= 3 => 'from-red-600 to-red-500',
                        $daysLeft <= 7 => 'from-orange-600 to-amber-500',
                        $daysLeft <= 14 => 'from-yellow-600 to-yellow-500',
                        default => 'from-green-600 to-emerald-500'
                    };
                    $urgencyBg = match(true) {
                        $daysLeft <= 3 => 'bg-red-500/10 border-red-500/20',
                        $daysLeft <= 7 => 'bg-orange-500/10 border-orange-500/20',
                        $daysLeft <= 14 => 'bg-yellow-500/10 border-yellow-500/20',
                        default => 'bg-green-500/10 border-green-500/20'
                    };
                ?>
                <a href="/user/detail.php?id=<?= $item['id'] ?>" class="glass-card rounded-2xl overflow-hidden group reveal block">
                    <!-- Urgency Bar -->
                    <div class="h-1 bg-gradient-to-r <?= $urgencyColor ?>"></div>
                    
                    <div class="p-5">
                        <!-- Category -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="category-chip text-[11px]" style="background: <?= $item['kategori_warna'] ?>15; color: <?= $item['kategori_warna'] ?>; border-color: <?= $item['kategori_warna'] ?>30;">
                                <i class="<?= $item['kategori_icon'] ?> mr-1"></i><?= $item['kategori'] ?>
                            </span>
                            <span class="flex items-center gap-1 text-dark-500 text-xs">
                                <i class="fas fa-eye text-[9px]"></i><?= number_format($item['views']) ?>
                            </span>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-base font-semibold text-dark-100 mb-3 line-clamp-2 group-hover:text-primary-300 transition-colors">
                            <?= htmlspecialchars($item['judul']) ?>
                        </h3>
                        
                        <!-- Deadline Counter -->
                        <div class="flex items-center gap-3 p-3 rounded-xl <?= $urgencyBg ?> border">
                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br <?= $urgencyColor ?> flex items-center justify-center flex-shrink-0">
                                <span class="text-lg font-bold font-space text-dark-100"><?= $daysLeft ?></span>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-dark-100">Hari Tersisa</div>
                                <div class="text-[11px] text-dark-400"><?= $deadline->format('d M Y') ?></div>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- ============ SEMUA BERITA SECTION ============ -->
    <section id="semua-berita" class="relative py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-12 reveal">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                            <i class="fas fa-newspaper text-emerald-400 text-sm"></i>
                        </div>
                        <span class="text-sm font-semibold text-emerald-400 uppercase tracking-wider">Semua Berita</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Update Terbaru</h2>
                    <p class="text-dark-400 mt-2">Semua informasi kampus yang sudah dipublikasikan</p>
                </div>
                <a href="/user/index.php" class="mt-4 sm:mt-0 flex items-center gap-2 text-sm text-primary-400 hover:text-primary-300 font-medium transition-colors group">
                    Lihat Semua
                    <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <?php if (!empty($allNews)): ?>
                <?php $featured = array_shift($allNews); ?>
                <a href="/user/detail.php?id=<?= $featured['id'] ?>" class="glass-card rounded-2xl p-6 sm:p-8 mb-8 block hover:translate-y-[-2px] transition-all reveal">
                    <div class="flex flex-col lg:flex-row gap-6">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-3 flex-wrap">
                                <?php if ($featured['kategori']): ?>
                                <span class="category-chip text-[11px]" style="background: <?= $featured['kategori_warna'] ?>15; color: <?= $featured['kategori_warna'] ?>; border-color: <?= $featured['kategori_warna'] ?>30;">
                                    <i class="<?= $featured['kategori_icon'] ?> mr-1"></i><?= htmlspecialchars($featured['kategori']) ?>
                                </span>
                                <?php endif; ?>
                                <span class="text-[11px] text-dark-500"><?= date('d M Y', strtotime($featured['created_at'])) ?></span>
                                <span class="text-[11px] text-dark-500">Oleh <?= htmlspecialchars($featured['publisher_nama'] ?? 'Publisher') ?></span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-semibold text-dark-100 mb-3 hover:text-primary-300 transition-colors">
                                <?= htmlspecialchars($featured['judul']) ?>
                            </h3>
                            <?php $featuredExcerpt = trim(strip_tags($featured['deskripsi'])); ?>
                            <p class="text-sm text-dark-400 line-clamp-3"><?= htmlspecialchars(mb_substr($featuredExcerpt, 0, 220)) ?>...</p>
                        </div>
                        <div class="lg:w-64 flex-shrink-0">
                            <div class="rounded-2xl bg-white/5 border border-white/10 p-4 h-full flex flex-col justify-between">
                                <div class="text-xs text-dark-500">Views</div>
                                <div class="text-2xl font-bold font-space text-dark-100"><?= number_format($featured['views']) ?></div>
                                <div class="text-xs text-dark-500">Klik untuk baca lengkap</div>
                            </div>
                        </div>
                    </div>
                </a>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($allNews as $item): ?>
                    <a href="/user/detail.php?id=<?= $item['id'] ?>" class="glass-card rounded-2xl p-6 hover:translate-y-[-3px] transition-all reveal">
                        <div class="flex items-center gap-2 mb-3 flex-wrap">
                            <?php if ($item['kategori']): ?>
                            <span class="category-chip text-[11px]" style="background: <?= $item['kategori_warna'] ?>15; color: <?= $item['kategori_warna'] ?>; border-color: <?= $item['kategori_warna'] ?>30;">
                                <i class="<?= $item['kategori_icon'] ?> mr-1"></i><?= htmlspecialchars($item['kategori']) ?>
                            </span>
                            <?php endif; ?>
                            <span class="text-[11px] text-dark-500"><?= date('d M Y', strtotime($item['created_at'])) ?></span>
                        </div>
                        <h3 class="text-lg font-semibold text-dark-100 mb-2 line-clamp-2"><?= htmlspecialchars($item['judul']) ?></h3>
                        <p class="text-sm text-dark-400 line-clamp-2">
                            <?= htmlspecialchars(strip_tags($item['deskripsi'])) ?>
                        </p>
                        <div class="mt-4 flex items-center justify-between text-xs text-dark-500">
                            <span>Oleh <?= htmlspecialchars($item['publisher_nama'] ?? 'Publisher') ?></span>
                            <span><i class="fas fa-eye"></i> <?= number_format($item['views']) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="glass-card rounded-2xl p-10 text-center">
                    <i class="fas fa-inbox text-3xl text-dark-400"></i>
                    <p class="text-sm text-dark-400 mt-3">Belum ada berita yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============ TRENDING SECTION ============ -->
    <section id="trending" class="relative py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-12 reveal">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                            <i class="fas fa-fire text-amber-400 text-sm"></i>
                        </div>
                        <span class="text-sm font-semibold text-amber-400 uppercase tracking-wider">Trending Now</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Paling Banyak Dilihat</h2>
                    <p class="text-dark-400 mt-2">Informasi yang paling populer di kalangan mahasiswa</p>
                </div>
                <a href="/trending.php" class="mt-4 sm:mt-0 flex items-center gap-2 text-sm text-primary-400 hover:text-primary-300 font-medium transition-colors group">
                    Lihat Semua
                    <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
            
            <!-- Trending List -->
            <div class="space-y-4">
                <?php
                $rank = 0;
                while ($item = $trending->fetch_assoc()):
                    $rank++;
                    $rankClass = match($rank) {
                        1 => 'gold',
                        2 => 'silver',
                        3 => 'bronze',
                        default => 'default'
                    };
                    
                    // Random trend bars data
                    $bars = [];
                    for ($b = 0; $b < 7; $b++) {
                        $bars[] = rand(20, 100);
                    }
                    
                    // Calculate days until deadline
                    $daysLeft = null;
                    if ($item['deadline']) {
                        $deadline = new DateTime($item['deadline']);
                        $now = new DateTime();
                        $diff = $now->diff($deadline);
                        $daysLeft = $diff->invert ? -1 : $diff->days;
                    }
                ?>
                <a href="/user/detail.php?id=<?= $item['id'] ?>" class="trending-card p-5 sm:p-6 reveal block">
                    <div class="flex items-center gap-4 sm:gap-6">
                        <!-- Rank -->
                        <div class="rank-badge <?= $rankClass ?>"><?= $rank ?></div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                <span class="category-chip text-[11px]" style="background: <?= $item['kategori_warna'] ?>15; color: <?= $item['kategori_warna'] ?>; border-color: <?= $item['kategori_warna'] ?>30;">
                                    <i class="<?= $item['kategori_icon'] ?> mr-1"></i><?= $item['kategori'] ?>
                                </span>
                                <?php if ($daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 7): ?>
                                <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 text-[11px] font-semibold">
                                    <i class="fas fa-clock text-[9px]"></i>
                                    <?= $daysLeft == 0 ? 'Hari ini!' : $daysLeft . ' hari lagi' ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-base sm:text-lg font-semibold text-dark-100 truncate hover:text-primary-300 transition-colors cursor-pointer">
                                <?= htmlspecialchars($item['judul']) ?>
                            </h3>
                            <?php $excerpt = trim(strip_tags($item['deskripsi'])); ?>
                            <p class="text-sm text-dark-400 mt-1 line-clamp-1 hidden sm:block"><?= htmlspecialchars(mb_substr($excerpt, 0, 120)) ?>...</p>
                        </div>
                        
                        <!-- Trend Chart (mini) -->
                        <div class="hidden md:flex flex-col items-end gap-1">
                            <div class="trend-line w-24">
                                <?php foreach ($bars as $height): ?>
                                <div class="trend-bar bg-gradient-to-t from-primary-600 to-primary-400" data-height="<?= $height ?>" style="height: 4px;"></div>
                                <?php endforeach; ?>
                            </div>
                            <span class="text-[10px] text-dark-500">7 hari terakhir</span>
                        </div>
                        
                        <!-- Views -->
                        <div class="text-right flex-shrink-0">
                            <div class="text-lg sm:text-xl font-bold font-space text-dark-100"><?= number_format($item['views']) ?></div>
                            <div class="text-[11px] text-dark-500 flex items-center gap-1 justify-end">
                                <i class="fas fa-eye text-[9px]"></i>views
                            </div>
                        </div>
                        
                        <!-- Arrow -->
                        <div class="hidden sm:flex w-8 h-8 rounded-lg bg-white/5 items-center justify-center text-dark-400 hover:text-dark-100 hover:bg-primary-600 transition-all cursor-pointer flex-shrink-0">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- ============ KATEGORI SECTION ============ -->
    <section id="kategori" class="relative py-20 lg:py-28">
        <!-- Subtle bg accent -->
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-primary-900/5 to-transparent"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-14 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary-500/10 border border-primary-500/20 text-sm text-primary-400 mb-4">
                    <i class="fas fa-compass"></i>
                    <span class="font-medium">Jelajahi Kategori</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Temukan Berdasarkan Minat</h2>
                <p class="text-dark-400 mt-3 max-w-xl mx-auto">Pilih kategori yang sesuai dengan minatmu dan temukan informasi terkini</p>
            </div>
            
            <!-- Category Grid -->
            <div class="flex flex-wrap justify-center gap-4 max-w-6xl mx-auto">
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="/kategori.php?slug=<?= $cat['slug'] ?>" class="glass-card rounded-2xl p-6 text-center group reveal w-40 sm:w-44 lg:w-48">
                    <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center transition-all duration-300 group-hover:scale-110" style="background: <?= $cat['warna'] ?>15;">
                        <i class="<?= $cat['icon'] ?> text-xl" style="color: <?= $cat['warna'] ?>"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-dark-100 mb-1 group-hover:text-primary-300 transition-colors"><?= $cat['nama'] ?></h3>
                    <p class="text-xs text-dark-500"><?= $cat['total_info'] ?> info</p>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- ============ STATS SECTION ============ -->
    <section class="relative py-20 lg:py-28 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-r from-amber-100/50 via-white to-sky-100/50"></div>
        <div class="absolute inset-0 grid-pattern"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <h2 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Angka yang Berbicara</h2>
                <p class="text-dark-400 mt-3">Pertumbuhan platform informasi kampus UHN</p>
            </div>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat 1 -->
                <div class="glass-card rounded-2xl p-8 text-center reveal">
                    <div class="w-14 h-14 rounded-2xl bg-primary-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-newspaper text-2xl text-primary-400"></i>
                    </div>
                    <div class="text-3xl sm:text-4xl font-bold font-space text-dark-100 mb-1" data-count="<?= $totalInfo ?>">0</div>
                    <div class="text-sm text-dark-400">Informasi Aktif</div>
                </div>
                
                <!-- Stat 2 -->
                <div class="glass-card rounded-2xl p-8 text-center reveal">
                    <div class="w-14 h-14 rounded-2xl bg-accent-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl text-accent-400"></i>
                    </div>
                    <div class="text-3xl sm:text-4xl font-bold font-space text-dark-100 mb-1" data-count="<?= $totalUsers ?>">0</div>
                    <div class="text-sm text-dark-400">Pengguna Terdaftar</div>
                </div>
                
                <!-- Stat 3 -->
                <div class="glass-card rounded-2xl p-8 text-center reveal">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-eye text-2xl text-amber-400"></i>
                    </div>
                    <div class="text-3xl sm:text-4xl font-bold font-space text-dark-100 mb-1" data-count="<?= $totalViews ?>">0</div>
                    <div class="text-sm text-dark-400">Total Views</div>
                </div>
                
                <!-- Stat 4 -->
                <div class="glass-card rounded-2xl p-8 text-center reveal">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-layer-group text-2xl text-emerald-400"></i>
                    </div>
                    <div class="text-3xl sm:text-4xl font-bold font-space text-dark-100 mb-1" data-count="<?= $totalCategories ?>">0</div>
                    <div class="text-sm text-dark-400">Kategori</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ CTA SECTION ============ -->
    <section id="tentang" class="relative py-20 lg:py-28">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
            <!-- Decorative -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-primary-600/10 blur-[120px] pointer-events-none"></div>
            
            <div class="relative">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-500 flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-primary-600/30">
                    <i class="fas fa-rocket text-2xl text-dark-100"></i>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold font-space text-dark-100 mb-4">
                    Jangan Ketinggalan Info Penting!
                </h2>
                <p class="text-lg text-dark-400 max-w-2xl mx-auto mb-8">
                    Daftar sekarang dan dapatkan akses penuh ke semua informasi kampus. Simpan bookmark, terima notifikasi, dan tetap update.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/auth/register.php" class="group flex items-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-r from-primary-600 to-accent-600 hover:from-primary-500 hover:to-accent-500 text-dark-100 font-semibold shadow-2xl shadow-primary-600/25 transition-all duration-300 hover:scale-[1.03] animate-gradient-shift">
                        <i class="fas fa-user-plus"></i>
                        Daftar Gratis Sekarang
                        <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="#trending" class="flex items-center gap-2 text-dark-400 hover:text-dark-100 text-sm font-medium transition-colors">
                        <i class="fas fa-play-circle"></i>
                        Atau telusuri dulu
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
