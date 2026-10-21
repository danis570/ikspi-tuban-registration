</main>

<footer class="border-t border-border bg-surface">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">

            <p class="text-sm text-text-muted">
                &copy; <?= date('Y') ?> IKSPI <span class="text-primary font-semibold">cabang</span> Tuban.
            </p>

            <ul class="flex items-center gap-5">
                <li>
                    <a href="/about" class="text-sm text-text-muted hover:text-text transition-colors">About</a>
                </li>
            </ul>

        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ---- Icons ----
        if (window.lucide) lucide.createIcons();

        // ---- Navbar scroll + mobile menu ----
        const navbar = document.getElementById('navbar');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');

        // Guard: hanya jalankan kalau navbar ada di halaman ini
        if (navbar) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('shadow-md', 'border-border');
                    navbar.classList.remove('border-transparent');
                } else {
                    navbar.classList.remove('shadow-md', 'border-border');
                    navbar.classList.add('border-transparent');
                }
            });
        }

        if (mobileMenuBtn && navLinks) {
            function toggleMenu(forceClose = false) {
                const isOpen = mobileMenuBtn.classList.contains('active');
                const willOpen = forceClose ? false : !isOpen;

                mobileMenuBtn.classList.toggle('active', willOpen);
                mobileMenuBtn.setAttribute('aria-expanded', String(willOpen));

                navLinks.style.maxHeight = willOpen ? navLinks.scrollHeight + 'px' : '0px';
            }

            mobileMenuBtn.addEventListener('click', () => toggleMenu());

            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => toggleMenu(true));
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) toggleMenu(true);
            });
        }
    });
</script>

</body>

</html>