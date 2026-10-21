<section
    class="min-h-[calc(100vh-4rem)] md:min-h-[calc(100vh-5rem)] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
    <div class="w-full max-w-md">

        <!-- Card -->
        <div class="bg-surface border border-border rounded-2xl shadow-sm p-6 sm:p-8">

            <!-- Heading -->
            <div class="text-center mb-6">
                <h1 class="font-display uppercase text-2xl text-text tracking-tight">
                    <span class="text-primary">Masuk </span><span class="text-warning">Sistem</span>
                </h1>
                <p class="mt-1 text-sm text-text-muted">Masuk ke akun Anda untuk melanjutkan.</p>
            </div>

            <!-- Error -->
            <?php if (!empty($error)): ?>
                <div class="mb-5 rounded-lg bg-danger-soft border border-danger/20 px-4 py-3 text-sm text-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="/login" method="post" class="space-y-5" novalidate>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-text-body mb-1.5">
                        Email
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="you@example.com" class="w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-text
                                   placeholder:text-text-muted/60
                                   focus:outline-none focus:ring-2 focus:ring-primary-ring focus:border-primary
                                   transition-colors">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-text-body mb-1.5">
                        Password
                    </label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        placeholder="••••••••" class="w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-text
                                   placeholder:text-text-muted/60
                                   focus:outline-none focus:ring-2 focus:ring-primary-ring focus:border-primary
                                   transition-colors">
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full inline-flex items-center justify-center rounded-full bg-cta px-5 py-2.5
                               text-sm font-semibold text-cta-text shadow-sm
                               hover:bg-cta-hover active:bg-primary-dark
                               focus:outline-none focus:ring-2 focus:ring-primary-ring focus:ring-offset-2
                               transition-colors">
                    Login
                </button>

            </form>

        </div>

    </div>
</section>