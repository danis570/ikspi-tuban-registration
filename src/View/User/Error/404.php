<!-- src/View/admin/errors/404.php (Lakukan hal sama untuk public & user) -->
<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Card 404 -->
    <div
        class="bg-surface border-4 border-text shadow-brutal p-8 sm:p-12 max-w-lg w-full text-center relative overflow-hidden">

        <!-- Aksen background -->
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-soft rounded-full blur-2xl opacity-50"></div>

        <!-- Ikon Peringatan -->
        <div
            class="w-24 h-24 mx-auto bg-danger text-text-invert border-4 border-text shadow-brutal-sm flex items-center justify-center mb-6 -rotate-3">
            <i data-lucide="search-x" class="w-12 h-12"></i>
        </div>

        <h1 class="font-display text-5xl sm:text-6xl text-text uppercase tracking-tighter mb-2">
            404
        </h1>

        <h2 class="font-display text-xl sm:text-2xl text-primary uppercase tracking-wider mb-4">
            PAGE NOT FOUND!
        </h2>

        <p class="font-bold text-text-muted mb-6">
            Maaf, halaman yang Anda cari tidak ada.
        </p>

        <!-- Tombol Kembali -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <!-- Sesuaikan tombol kembali dengan role -->

            <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 
                      bg-cta text-cta-text border-4 border-text shadow-brutal-sm 
                      font-black uppercase text-sm tracking-wider 
                      hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none 
                      transition-all duration-150">
                <i data-lucide="home" class="w-5 h-5"></i>
                Ke Dashboard
            </a>
        </div>

    </div>
</div>