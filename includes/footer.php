    <!-- Footer -->
    <footer class="relative bg-dark-950 border-t border-white/5 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="/foto/logo_uhn.png" alt="UHN Logo" class="h-10 w-10 object-contain">
                        <div>
                            <h3 class="text-xl font-bold font-space bg-gradient-to-r from-primary-400 to-accent-400 bg-clip-text text-transparent">UHN Terkini</h3>
                            <p class="text-xs text-dark-400">Portal Informasi Kampus</p>
                        </div>
                    </div>
                    <p class="text-dark-400 text-sm leading-relaxed max-w-md">
                        Platform informasi terpusat untuk civitas akademika UHN. Temukan beasiswa, lomba, seminar, dan berbagai kegiatan kampus yang sedang trending.
                    </p>
                    <div class="flex gap-3 mt-5">
                        <a href="#" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-primary-600 flex items-center justify-center transition-all duration-300 text-dark-400 hover:text-dark-100">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-primary-600 flex items-center justify-center transition-all duration-300 text-dark-400 hover:text-dark-100">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-primary-600 flex items-center justify-center transition-all duration-300 text-dark-400 hover:text-dark-100">
                            <i class="fab fa-youtube text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-primary-600 flex items-center justify-center transition-all duration-300 text-dark-400 hover:text-dark-100">
                            <i class="fab fa-tiktok text-sm"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Links -->
                <div>
                    <h4 class="text-sm font-semibold text-dark-100 mb-4 uppercase tracking-wider">Navigasi</h4>
                    <ul class="space-y-2.5">
                        <li><a href="/" class="text-dark-400 hover:text-primary-400 text-sm transition-colors">Beranda</a></li>
                        <li><a href="/#trending" class="text-dark-400 hover:text-primary-400 text-sm transition-colors">Trending</a></li>
                        <li><a href="/#kategori" class="text-dark-400 hover:text-primary-400 text-sm transition-colors">Kategori</a></li>
                        <li><a href="/#tentang" class="text-dark-400 hover:text-primary-400 text-sm transition-colors">Tentang</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="text-sm font-semibold text-dark-100 mb-4 uppercase tracking-wider">Kontak</h4>
                    <ul class="space-y-2.5">
                        <li class="flex items-center gap-2 text-dark-400 text-sm">
                            <i class="fas fa-map-marker-alt text-primary-500 text-xs"></i>
                            Jl. Sutomo No. 4A, Medan
                        </li>
                        <li class="flex items-center gap-2 text-dark-400 text-sm">
                            <i class="fas fa-envelope text-primary-500 text-xs"></i>
                            info@uhn.ac.id
                        </li>
                        <li class="flex items-center gap-2 text-dark-400 text-sm">
                            <i class="fas fa-phone text-primary-500 text-xs"></i>
                            (061) 4567890
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom -->
            <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-dark-500 text-xs">&copy; <?= date('Y') ?> UHN Terkini. All rights reserved.</p>
                <p class="text-dark-500 text-xs">Dibuat dengan <i class="fas fa-heart text-red-500"></i> untuk civitas akademika UHN</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="/assets/js/main.js"></script>
</body>
</html>
