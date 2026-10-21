<div class="min-h-[calc(100vh-4rem)] lg:min-h-[calc(100vh-5rem)] flex items-center justify-center px-4 sm:px-6 py-10">

    <div class="w-full max-w-md">

        <!-- Card -->
        <div class="bg-surface border-4 border-text shadow-brutal p-6 sm:p-8">

            <!-- Heading -->
            <div class="text-center mb-6">
                <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
                    Register
                </h1>
                <p class="mt-1 text-sm text-text-muted">Registrasikan pengguna baru.</p>
            </div>

            <!-- Error -->
            <?php if (!empty($error)): ?>
                <div class="flex items-start gap-3 p-3 mb-5
                            bg-danger-soft text-danger
                            border-4 border-text shadow-brutal-sm
                            font-bold text-sm">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="/register" method="post" class="space-y-5" novalidate>

                <!-- Email -->
                <div>
                    <label for="email" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                        Email
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="you@example.com" class="w-full px-4 py-3 bg-surface text-text
                               border-4 border-text
                               font-medium text-sm
                               placeholder:text-text-muted/60
                               focus:outline-none focus:ring-4 focus:ring-primary-ring focus:border-primary
                               transition-all">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                        Password
                    </label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                        placeholder="Password" class="w-full px-4 py-3 bg-surface text-text
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
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Register</span>
                </button>

            </form>

        </div>

    </div>
</div>