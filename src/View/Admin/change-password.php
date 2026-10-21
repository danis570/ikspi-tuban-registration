<div class="p-4 sm:p-6 lg:p-8">

    <div class="max-w-xl mx-auto">

        <!-- Card -->
        <div class="bg-surface border-4 border-text shadow-brutal p-6 sm:p-8">

            <!-- Heading -->
            <div class="mb-6">
                <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
                    Account Setting
                </h1>
                <p class="mt-1 text-sm text-text-muted">Ubah password akun kamu.</p>
            </div>

            <!-- Error -->
            <?php if (!empty($model['error'])): ?>
                <div class="flex items-start gap-3 p-4 mb-5
                            bg-danger-soft text-danger
                            border-4 border-text shadow-brutal-sm
                            font-bold text-sm">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <span><?= htmlspecialchars($model['error']) ?></span>
                </div>
            <?php endif; ?>

            <!-- Flash -->
            <?php if (!empty($_SESSION['flash']['message'])): ?>
                <div class="flex items-start gap-3 p-4 mb-5
                            bg-success-soft text-success
                            border-4 border-text shadow-brutal-sm
                            font-bold text-sm">
                    <i data-lucide="check-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <span><?= htmlspecialchars($_SESSION['flash']['message']) ?></span>
                </div>
                <?php unset($_SESSION['flash']['message']); ?>
            <?php endif; ?>

            <!-- Form -->
            <form action="/auth/password" method="post" class="space-y-5" novalidate>

                <!-- Password lama -->
                <div>
                    <label for="old_password"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                        Password Lama
                    </label>
                    <input type="password" id="old_password" name="old_password" autocomplete="current-password"
                        value="<?= $_POST['old_password'] ?? '' ?>" placeholder="Masukkan password saat ini" class="w-full px-4 py-3 bg-surface text-text
                               border-4 border-text
                               font-medium text-sm
                               placeholder:text-text-muted/60
                               focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary
                               transition-all">
                </div>

                <!-- Password baru -->
                <div>
                    <label for="new_password"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                        Password Baru
                    </label>
                    <input type="password" id="new_password" name="new_password" autocomplete="new-password" required
                        value="<?= $_POST['new_password'] ?? '' ?>" placeholder="Password baru" class="w-full px-4 py-3 bg-surface text-text
                               border-4 border-text
                               font-medium text-sm
                               placeholder:text-text-muted/60
                               focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary
                               transition-all">
                </div>

                <!-- Konfirmasi password -->
                <div>
                    <label for="confirm_password"
                        class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                        Konfirmasi Password Baru
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"
                        value="<?= $_POST['confirm_password'] ?? '' ?>" placeholder="Ulangi password baru" class="w-full px-4 py-3 bg-surface text-text
                               border-4 border-text
                               font-medium text-sm
                               placeholder:text-text-muted/60
                               focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary
                               transition-all">
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2
                               px-5 py-3 bg-cta text-cta-text
                               border-4 border-text shadow-brutal-sm
                               font-black uppercase text-sm tracking-wider
                               hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none
                               transition-all duration-150">
                    <i data-lucide="key-round" class="w-4 h-4"></i>
                    <span>Update Password</span>
                </button>

            </form>

        </div>

    </div>
</div>