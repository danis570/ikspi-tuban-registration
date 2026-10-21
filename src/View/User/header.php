<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>

    <!-- Tailwind (build dari input.css) -->
    <link rel="stylesheet" href="/assets/output.css">

    <!-- Preload Fonts -->
    <link rel="preload" href="/assets/fonts/space-grotesk-v22-latin-regular.woff2" as="font" type="font/woff2"
        crossorigin>
    <link rel="preload" href="/assets/fonts/archivo-black-v23-latin-regular.woff2" as="font" type="font/woff2"
        crossorigin>
    <link rel="preload" href="/assets/fonts/space-grotesk-v22-latin-500.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/space-grotesk-v22-latin-600.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/assets/fonts/space-grotesk-v22-latin-700.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Lucide Icons -->
    <script src="/assets/vendor/lucide.min.js"></script>
</head>

<body class="transition-colors duration-300">

    <!-- ============================== -->
    <!-- SIDEBAR -->
    <!-- ============================== -->
    <aside id="sidebar" class="fixed top-0 left-0 z-50 h-screen w-72 bg-surface
               border-r-4 border-text
               flex flex-col transform -translate-x-full lg:translate-x-0
               transition-transform duration-300">

        <!-- Logo -->
        <div class="h-20 flex items-center gap-3 px-6 border-b-4 border-text shrink-0">
            <a href="/" class="flex items-center gap-2 group">
                <div class="w-12 h-12 shrink-0 flex items-center justify-center
                group-hover:rotate-12 transition-transform">
                    <img src="/logo.png" alt="Logo IKSPI" class="w-10 h-10 object-contain" />
                </div>

                <span class="font-display text-2xl tracking-tighter uppercase text-text">
                    TUBAN
                </span>
            </a>

            <button id="sidebar-close" class="lg:hidden ml-auto p-1 border-2 border-text text-text">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Menu -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-5">

            <!-- SECTION 1: -->
            <div>
                <p class="px-2 mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">
                    MENU
                </p>

                <a href="/registration" class="flex items-center gap-3 px-4 py-3 font-black uppercase text-sm border-4 transition-all
                    <?= ($current ?? '') === 'registration'
                        ? 'bg-primary text-text-invert border-text shadow-brutal'
                        : 'border-transparent text-text hover:bg-primary hover:text-text-invert hover:border-text' ?>">
                    <i data-lucide="square-plus" class="w-5 h-5 shrink-0"></i>
                    <span>Registrasi</span>
                </a>
            </div>

            <!-- SECTION 2: AKUN -->
            <div class="pt-4 border-t-2 border-dashed border-border-strong">
                <p class="px-2 mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">
                    Akun
                </p>

                <a href="/profile" class="flex items-center gap-3 px-4 py-3 font-black uppercase text-sm border-4 transition-all
                    <?= ($current ?? '') === 'profile'
                        ? 'bg-primary text-text-invert border-text shadow-brutal'
                        : 'border-transparent text-text hover:bg-primary hover:text-text-invert hover:border-text' ?>">
                    <i data-lucide="user-pen" class="w-5 h-5 shrink-0"></i>
                    <span>Profil</span>
                </a>
                <a href="/auth/password" class="flex items-center gap-3 px-4 py-3 font-black uppercase text-sm border-4 transition-all
                    <?= ($current ?? '') === 'account'
                        ? 'bg-primary text-text-invert border-text shadow-brutal'
                        : 'border-transparent text-text hover:bg-primary hover:text-text-invert hover:border-text' ?>">
                    <i data-lucide="key-round" class="w-5 h-5 shrink-0"></i>
                    <span>Akun</span>
                </a>
            </div>
        </nav>

        <!-- User Card + Logout -->
        <div class="shrink-0 p-4 border-t-4 border-text space-y-3">

            <div class="flex items-center gap-3 p-3 border-4 border-text bg-surface-hover">
                <div class="w-10 h-10 shrink-0 overflow-hidden rounded-full border-2 border-text">
                    <?php if (!empty($currentProfile->avatar)): ?>
                        <img src="/uploads/avatars/<?= htmlspecialchars($currentProfile->avatar) ?>"
                            alt="<?= htmlspecialchars($currentProfile->name ?? 'Avatar') ?>"
                            class="w-full h-full object-cover">
                    <?php else: ?>
                        <img src="/uploads/avatars/default-avatar.png" alt="Default Avatar"
                            class="w-full h-full object-cover">
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <p class="font-black text-xs truncate text-text">
                        <?= htmlspecialchars($currentProfile->name ?? 'Username') ?>
                    </p>
                    <p class="text-[10px] truncate text-text-muted">
                        <?= htmlspecialchars($currentUser->email ?? '') ?>
                    </p>
                </div>
            </div>

            <form action="/logout" method="post" class="w-full">
                <button type="submit" class="w-full flex items-center justify-center gap-2 p-3 bg-cta text-cta-text
                   border-4 border-text font-black uppercase text-xs
                   hover:bg-cta-hover transition-all cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Overlay mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-text/60 z-40 hidden lg:hidden"></div>

    <!-- ============================== -->
    <!-- MOBILE TOP BAR -->
    <!-- ============================== -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-30 h-16
               bg-surface
               border-b-4 border-text
               flex items-center justify-between px-4">

        <a href="/" class="flex items-center gap-2 group">
            <div class="w-12 h-12 shrink-0 flex items-center justify-center
                border-2 border-transparent
                group-hover:rotate-12 transition-transform">
                <img src="/logo.png" alt="Logo IKSPI" class="w-10 h-10 object-contain" />
            </div>

            <span class="font-display text-xl uppercase tracking-tighter text-text">
                TUBAN
            </span>
        </a>

        <button id="sidebar-open" class="w-9 h-9 flex items-center justify-center
           bg-primary text-text-invert
           border-2 border-text
           shadow-brutal-sm
           hover:translate-x-[3px] hover:translate-y-[3px]
           hover:shadow-none
           transition-all duration-150
           cursor-pointer">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
    </header>

    <!-- ============================== -->
    <!-- KONTEN UTAMA -->
    <!-- ============================== -->
    <main class="lg:ml-72 pt-16 lg:pt-0 min-h-screen flex flex-col">
        <div class="flex-1">