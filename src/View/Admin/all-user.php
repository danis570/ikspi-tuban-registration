<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- ============================================ -->
    <!-- FLASH MESSAGE (sukses/info)                   -->
    <!-- ============================================ -->
    <?php if (!empty($_SESSION['flash']['message'])): ?>
        <div class="flex items-start gap-3 p-4 bg-success-soft text-success
                    border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <span><?= htmlspecialchars($_SESSION['flash']['message']) ?></span>
        </div>
        <?php unset($_SESSION['flash']['message']); ?>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- ERROR                                          -->
    <!-- ============================================ -->
    <?php if (!empty($error)): ?>
        <div class="flex items-start gap-3 p-4 bg-danger-soft text-danger
                    border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- HEADER: judul + tombol Register               -->
    <!-- ============================================ -->
    <div class="space-y-3">
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            Semua Pengguna
        </h1>

        <div class="flex w-full">
            <a href="/register" class="ml-auto inline-flex items-center justify-center gap-2
                   px-5 py-2.5 bg-primary text-text-invert
                   border-4 border-text shadow-brutal-sm
                   font-black uppercase text-sm
                   hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none
                   transition-all duration-150">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Register</span>
            </a>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- TABLE                                         -->
    <!-- ============================================ -->
    <div class="bg-surface border-4 border-text shadow-brutal overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-text text-text-invert">
                    <tr>
                        <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3 w-16">No</th>
                        <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3 w-20">ID</th>
                        <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">Email</th>
                        <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3 w-32">Role</th>
                        <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-text">

                    <?php foreach ($users as $index => $value): ?>
                        <tr class="hover:bg-surface-hover transition-colors">

                            <!-- No -->
                            <td class="px-4 py-3 font-bold text-text-muted">
                                <?= $index + 1 ?>
                            </td>

                            <!-- ID -->
                            <td class="px-4 py-3 font-bold text-text">
                                <?= htmlspecialchars((string) $value->id) ?>
                            </td>

                            <!-- Email -->
                            <td class="px-4 py-3 text-text-body break-all">
                                <?= htmlspecialchars($value->email) ?>
                            </td>

                            <!-- Role badge -->
                            <td class="px-4 py-3">
                                <?php
                                $role = $value->role->value;
                                $badge = match ($role) {
                                    'admin' => 'bg-danger-soft text-danger',
                                    'editor' => 'bg-warning-soft text-warning',
                                    default => 'bg-info-soft text-info',
                                };
                                ?>
                                <span class="inline-flex items-center px-2.5 py-1
                                             border-2 border-text font-black uppercase text-[10px] tracking-wider
                                             <?= $badge ?>">
                                    <?= htmlspecialchars($role) ?>
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-3">
                                <?php if ($value->role->value !== 'admin'): ?>
                                    <form action="/user/delete" method="post" onsubmit="return confirm('Hapus user ini?');">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $value->id) ?>">
                                        <button type="submit" class="inline-flex items-center gap-1.5
                                                       px-3 py-1.5 bg-danger text-text-invert
                                                       border-2 border-text shadow-brutal-sm
                                                       font-black uppercase text-[10px] tracking-wider
                                                       hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none
                                                       transition-all duration-150">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-text-muted">
                                        —
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Empty state -->
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-text-muted font-bold">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                                Belum ada user.
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>