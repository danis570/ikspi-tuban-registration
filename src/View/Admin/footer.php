</div><!-- /.flex-1 -->

<!-- FOOTER -->
<footer class="bg-text text-text-invert py-6 border-t-4 border-primary">
    <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row justify-between items-center gap-2">
        <p class="font-bold text-sm">© 2026 IKSPI Cabang Tuban.</p>
        <p class="text-xs text-text-invert/70 font-bold">All rights reserved</p>
    </div>
</footer>
</main>

<!-- ============================================ -->
<!-- SCRIPT -->
<!-- ============================================ -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---- Icons ----
        if (window.lucide) lucide.createIcons();

        // ---- Sidebar Mobile ----
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const openBtn = document.getElementById('sidebar-open');
        const closeBtn = document.getElementById('sidebar-close');

        function openSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('-translate-x-full');
            overlay?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            if (!sidebar) return;
            sidebar.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
            document.body.style.overflow = '';
        }

        openBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        overlay?.addEventListener('click', closeSidebar);

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) closeSidebar();
        });
    });
</script>
</body>

</html>