<?php
$menus = [
    ['label' => 'About', 'href' => '/about', 'type' => 'link', 'key' => 'about'],
    ['label' => 'Login', 'href' => '/login', 'type' => 'button', 'key' => 'login'],
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'User management Template' ?></title>

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

<body class="min-h-screen bg-bg text-text-body">

    <nav id="navbar"
        class="fixed top-0 inset-x-0 z-50 transition-all duration-300 bg-surface/80 backdrop-blur-md border-b border-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">

                <!-- Brand -->
                <a href="/" class="flex-shrink-0 text-xl md:text-2xl font-display text-text tracking-tight">
                    IKSPI <span class="text-primary">TUBAN</span>
                </a>

                <!-- Desktop links (dari $menus) -->
                <ul class="hidden md:flex items-center gap-8 lg:gap-10">
                    <?php foreach ($menus as $m): ?>
                        <?php $isCurrent = ($m['type'] === 'link' && ($current ?? '') === $m['key']); ?>
                        <li>
                            <?php if ($m['type'] === 'button'): ?>
                                <a href="<?= $m['href'] ?>"
                                    class="inline-flex items-center rounded-full bg-cta px-5 py-2.5 text-sm font-semibold text-cta-text shadow-sm hover:bg-cta-hover active:bg-primary-dark transition-colors">
                                    <?= $m['label'] ?>
                                </a>
                            <?php else: ?>
                                <a href="<?= $m['href'] ?>" class="text-sm lg:text-[15px] font-medium transition-colors
                                        <?= $isCurrent
                                            ? 'text-primary font-semibold'
                                            : 'text-text-muted hover:text-text' ?>">
                                    <?= $m['label'] ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Mobile menu button -->
                <button id="mobileMenuBtn" aria-label="Toggle menu" aria-expanded="false"
                    class="md:hidden relative w-9 h-9 flex flex-col items-center justify-center gap-1.5 group">
                    <span
                        class="block h-0.5 w-6 rounded-full bg-text transition-all duration-300 origin-center group-[.active]:rotate-45 group-[.active]:translate-y-2"></span>
                    <span
                        class="block h-0.5 w-6 rounded-full bg-text transition-all duration-300 group-[.active]:opacity-0"></span>
                    <span
                        class="block h-0.5 w-6 rounded-full bg-text transition-all duration-300 origin-center group-[.active]:-rotate-45 group-[.active]:-translate-y-2"></span>
                </button>
            </div>
        </div>

        <!-- MENU MOBILE -->
        <div id="navLinks"
            class="md:hidden max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-surface border-t border-border">
            <ul class="flex flex-col px-4 py-3 gap-1">
                <?php foreach ($menus as $m): ?>
                    <?php $isCurrent = ($m['type'] === 'link' && ($current ?? '') === $m['key']); ?>
                    <li class="<?= $m['type'] === 'button' ? 'pt-2' : '' ?>">
                        <?php if ($m['type'] === 'button'): ?>
                            <a href="<?= $m['href'] ?>"
                                class="nav-link block text-center rounded-full bg-cta px-5 py-2.5 text-base font-semibold text-cta-text hover:bg-cta-hover transition-colors">
                                <?= $m['label'] ?>
                            </a>
                        <?php else: ?>
                            <a href="<?= $m['href'] ?>" class="nav-link block rounded-lg px-3 py-2.5 text-base transition-colors
                                    <?= $isCurrent
                                        ? 'bg-primary-soft text-primary font-semibold'
                                        : 'text-text-body hover:bg-surface-hover hover:text-text' ?>">
                                <?= $m['label'] ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <main class="pt-16 md:pt-20">