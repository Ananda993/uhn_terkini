<!-- Navbar -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="relative">
                    <img src="/foto/logo_uhn.png" alt="UHN Logo" class="h-9 w-9 object-contain relative z-10 group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-primary-500/20 rounded-full blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <span class="text-lg font-bold font-space tracking-wide text-amber-400">UHN TERKINI</span>
            </a>
            
            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-1">
                <a href="/" class="nav-link active px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300">
                    <i class="fas fa-fire mr-1.5 text-xs"></i>Beranda
                </a>
                <a href="/#trending" class="nav-link px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300">
                    <i class="fas fa-chart-line mr-1.5 text-xs"></i>Trending
                </a>
                <a href="/#kategori" class="nav-link px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300">
                    <i class="fas fa-th-large mr-1.5 text-xs"></i>Kategori
                </a>
               
                 <a href="/#tentang" class="nav-link px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300">
                    <i class="fas fa-info-circle mr-1.5 text-xs"></i>Tentang
                </a>
            </div>
            
            <!-- Right Side -->
            <div class="flex items-center gap-3">
                <!-- Search Toggle -->
                <button id="searchToggle" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center transition-all duration-300 text-dark-300 hover:text-dark-100">
                    <i class="fas fa-search text-sm"></i>
                </button>

                <?php if (isLoggedIn()): ?>
                <!-- Logged In: User Dropdown -->
                <div class="relative" id="userDropdownContainer">
                    <button id="userDropdownBtn" class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-white/5 transition-all duration-300">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-400 flex items-center justify-center text-dark-100 text-sm font-bold">
                            <?= strtoupper(substr($_SESSION['user_nama'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-dark-200 max-w-[120px] truncate"><?= htmlspecialchars($_SESSION['user_nama'] ?? 'User') ?></span>
                        <i class="fas fa-chevron-down text-[10px] text-dark-400 hidden sm:block"></i>
                    </button>
                    <!-- Dropdown Menu -->
                    <div id="userDropdown" class="hidden absolute right-0 top-full mt-2 w-56 bg-dark-900/95 backdrop-blur-xl border border-white/10 rounded-xl shadow-2xl shadow-black/50 overflow-hidden" style="z-index:100;">
                        <div class="px-4 py-3 border-b border-white/5">
                            <div class="text-sm font-semibold text-dark-100"><?= htmlspecialchars($_SESSION['user_nama'] ?? 'User') ?></div>
                            <div class="text-xs text-dark-400"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></div>
                            <span class="inline-block mt-1 text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>bg-primary-500/10 text-primary-400
                                <?php elseif ($_SESSION['user_role'] === 'publisher'): ?>bg-accent-500/10 text-accent-400
                                <?php else: ?>bg-purple-500/10 text-purple-400<?php endif; ?>">
                                <?= $_SESSION['user_role'] ?>
                            </span>
                        </div>
                        <div class="py-1">
                            <a href="/user/profile.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                                <i class="fas fa-user-circle text-xs w-4 text-center"></i>Profil
                            </a>
                            <?php if (isAdmin()): ?>
                            <a href="/admin/" class="flex items-center gap-3 px-4 py-2.5 text-sm text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                                <i class="fas fa-chart-pie text-xs w-4 text-center"></i>Dashboard Admin
                            </a>
                            <?php endif; ?>
                            <a href="/user/index.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                                <i class="fas fa-newspaper text-xs w-4 text-center"></i>Berita Terbaru
                            </a>
                            <?php if (isPublisher()): ?>
                            <a href="/publisher/index.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                                <i class="fas fa-pen text-xs w-4 text-center"></i>Dashboard Publisher
                            </a>
                            <?php endif; ?>
                            <a href="/auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-all">
                                <i class="fas fa-sign-out-alt text-xs w-4 text-center"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Not Logged In: Auth Buttons -->
                <a href="/auth/login.php" class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all duration-300">
                    <i class="fas fa-sign-in-alt text-xs"></i>Masuk
                </a>
                <a href="/auth/register.php" class="hidden sm:flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-dark-100 shadow-lg shadow-primary-600/20 hover:shadow-primary-500/30 transition-all duration-300 hover:scale-[1.02]">
                    Daftar
                </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <button id="mobileMenuToggle" class="lg:hidden w-9 h-9 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center transition-all duration-300 text-dark-300 hover:text-dark-100">
                    <i class="fas fa-bars text-sm"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Search Bar (hidden by default) -->
    <div id="searchBar" class="hidden border-t border-white/5 bg-dark-900/95 backdrop-blur-xl">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <form id="globalSearchForm" method="GET" action="/user/index.php">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-dark-400"></i>
                    <input type="text" id="globalSearch" name="search" placeholder="Cari informasi beasiswa, lomba, seminar..." class="w-full pl-11 pr-4 py-3 rounded-xl bg-dark-800 border border-white/10 text-dark-100 placeholder-dark-500 text-sm focus:outline-none focus:border-primary-500/50 focus:ring-2 focus:ring-primary-500/20 transition-all">
                    <kbd class="absolute right-4 top-1/2 -translate-y-1/2 px-2 py-0.5 text-[10px] text-dark-500 bg-dark-700 rounded border border-white/10">ESC</kbd>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden lg:hidden border-t border-white/5 bg-dark-900/95 backdrop-blur-xl">
        <div class="px-4 py-4 space-y-1">
            <a href="/" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-100 bg-white/5">
                <i class="fas fa-fire text-primary-400"></i>Beranda
            </a>
            <a href="/#trending" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                <i class="fas fa-chart-line"></i>Trending
            </a>
            <a href="/#kategori" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                <i class="fas fa-th-large"></i>Kategori
            </a>
            <a href="/#tentang" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                <i class="fas fa-info-circle"></i>Tentang
            </a>
            <hr class="border-white/5 my-2">
            <?php if (isLoggedIn()): ?>
                <a href="/user/profile.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                    <i class="fas fa-user-circle"></i>Profil
                </a>
                <?php if (isAdmin()): ?>
                <a href="/admin/" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                    <i class="fas fa-chart-pie"></i>Dashboard Admin
                </a>
                <?php endif; ?>
                <a href="/user/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                    <i class="fas fa-newspaper"></i>Berita Terbaru
                </a>
                <?php if (isPublisher()): ?>
                <a href="/publisher/index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                    <i class="fas fa-pen"></i>Dashboard Publisher
                </a>
                <?php endif; ?>
                <a href="/auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-red-400 hover:text-red-300 hover:bg-red-500/5 transition-all">
                    <i class="fas fa-sign-out-alt"></i>Logout
                </a>
            <?php else: ?>
                <a href="/auth/login.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-dark-300 hover:text-dark-100 hover:bg-white/5 transition-all">
                    <i class="fas fa-sign-in-alt"></i>Masuk
                </a>
                <a href="/auth/register.php" class="flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold bg-gradient-to-r from-primary-600 to-primary-500 text-dark-100">
                    <i class="fas fa-user-plus"></i>Daftar Sekarang
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
// User dropdown toggle
const dropdownBtn = document.getElementById('userDropdownBtn');
const dropdown = document.getElementById('userDropdown');
if (dropdownBtn && dropdown) {
    dropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });
    document.addEventListener('click', () => dropdown.classList.add('hidden'));
}
</script>
