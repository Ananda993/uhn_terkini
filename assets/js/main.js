// =========================================
// UHN Terkini - Main JavaScript
// =========================================

document.addEventListener('DOMContentLoaded', () => {
    // --- Navbar Scroll Effect ---
    const navbar = document.getElementById('navbar');
    if (navbar) {
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            if (currentScroll > 50) {
                navbar.classList.add('nav-scrolled');
            } else {
                navbar.classList.remove('nav-scrolled');
            }
            lastScroll = currentScroll;
        });
    }

    // --- Mobile Menu Toggle ---
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            const icon = mobileMenuToggle.querySelector('i');
            if (mobileMenu.classList.contains('hidden')) {
                icon.className = 'fas fa-bars text-sm';
            } else {
                icon.className = 'fas fa-times text-sm';
            }
        });
    }

    // --- Search Toggle ---
    const searchToggle = document.getElementById('searchToggle');
    const searchBar = document.getElementById('searchBar');
    const globalSearch = document.getElementById('globalSearch');
    const globalSearchForm = document.getElementById('globalSearchForm');
    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', () => {
            searchBar.classList.toggle('hidden');
            if (!searchBar.classList.contains('hidden')) {
                globalSearch?.focus();
            }
        });
        // ESC to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !searchBar.classList.contains('hidden')) {
                searchBar.classList.add('hidden');
            }
        });
    }
    if (globalSearchForm && globalSearch) {
        globalSearchForm.addEventListener('submit', (e) => {
            if (globalSearch.value.trim() === '') {
                e.preventDefault();
                return;
            }
            if (searchBar) {
                searchBar.classList.add('hidden');
            }
        });
    }

    // --- Active Nav Link ---
    const navLinks = document.querySelectorAll('.nav-link');
    const clearActive = () => navLinks.forEach(link => link.classList.remove('active'));
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            clearActive();
            link.classList.add('active');
        });
    });

    const hasHomeSections = document.getElementById('trending') || document.getElementById('kategori') || document.getElementById('tentang');
    if (hasHomeSections) {
        const hash = window.location.hash;
        if (hash) {
            const activeLink = document.querySelector(`.nav-link[href="/${hash}"]`);
            if (activeLink) {
                clearActive();
                activeLink.classList.add('active');
            }
        } else {
            const homeLink = document.querySelector('.nav-link[href="/"]');
            if (homeLink) {
                clearActive();
                homeLink.classList.add('active');
            }
        }
    }

    // --- Intersection Observer for Reveal Animations ---
    const revealElements = document.querySelectorAll('.reveal');
    if (revealElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        revealElements.forEach(el => observer.observe(el));
    }

    // --- Animated Counters ---
    const counters = document.querySelectorAll('[data-count]');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.getAttribute('data-count'));
                    const suffix = el.getAttribute('data-suffix') || '';
                    const duration = 2000;
                    const start = 0;
                    const startTime = performance.now();

                    function updateCounter(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        // Ease out cubic
                        const eased = 1 - Math.pow(1 - progress, 3);
                        const current = Math.floor(start + (target - start) * eased);
                        el.textContent = current.toLocaleString() + suffix;
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        }
                    }
                    requestAnimationFrame(updateCounter);
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(el => counterObserver.observe(el));
    }

    // --- Trend Bars Animation ---
    const trendBars = document.querySelectorAll('.trend-bar');
    if (trendBars.length > 0) {
        const barObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const bars = entry.target.querySelectorAll('.trend-bar');
                    bars.forEach((bar, i) => {
                        setTimeout(() => {
                            bar.style.height = bar.getAttribute('data-height') + '%';
                        }, i * 80);
                    });
                    barObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        document.querySelectorAll('.trend-line').forEach(el => barObserver.observe(el));
    }

    // --- Smooth Scroll for Anchor Links ---
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // --- Parallax effect on hero orbs ---
    const orbs = document.querySelectorAll('.hero-orb');
    if (orbs.length > 0) {
        window.addEventListener('mousemove', (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 2;
            const y = (e.clientY / window.innerHeight - 0.5) * 2;
            orbs.forEach((orb, i) => {
                const speed = (i + 1) * 15;
                orb.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    }
});
