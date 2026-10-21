<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- Flash Message -->
    <?php if (!empty($_SESSION['flash']['message'])): ?>
        <div class="flex items-start gap-3 p-4 bg-success-soft text-success
                    border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <span><?= htmlspecialchars($_SESSION['flash']['message']) ?></span>
        </div>
        <?php unset($_SESSION['flash']['message']); ?>
    <?php endif; ?>

    <!-- Error -->
    <?php if (!empty($error)): ?>
        <div class="flex items-start gap-3 p-4 bg-danger-soft text-danger
                    border-4 border-text shadow-brutal-sm font-bold text-sm">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="space-y-3">
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            Periode Registrasi
        </h1>

        <div class="flex w-full">
            <a href="/registration/period/save" class="ml-auto inline-flex items-center justify-center gap-2
                   px-5 py-2.5 bg-primary text-text-invert
                   border-4 border-text shadow-brutal-sm
                   font-black uppercase text-sm
                   hover:translate-x-[3px] hover:translate-y-[3px] hover:shadow-none
                   transition-all duration-150">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah</span>
            </a>
        </div>
    </div>

    <!-- Table -->
    <?php if (!empty($allPeriod)): ?>
        <div class="bg-surface border-4 border-text shadow-brutal overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-text text-text-invert">
                        <tr>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">
                                No
                            </th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">
                                Tingkat
                            </th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">
                                Angkatan
                            </th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">
                                Status
                            </th>
                            <th class="text-left font-black uppercase text-xs tracking-wider px-4 py-3">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y-4 divide-text">
                        <?php foreach ($allPeriod as $index => $period): ?>
                            <tr class="hover:bg-surface-hover transition-colors">

                                <!-- No -->
                                <td class="px-4 py-3 font-bold text-text-muted">
                                    <?= $index + 1 ?>
                                </td>

                                <!-- Tingkat -->
                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars($period->level->value) ?>
                                </td>

                                <!-- Angkatan -->
                                <td class="px-4 py-3 font-bold text-text">
                                    <?= htmlspecialchars((string) $period->batch) ?>
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3">
                                    <?php if ($period->isActive): ?>
                                        <span class="inline-flex items-center px-2.5 py-1
                                                     border-2 border-text font-black uppercase
                                                     text-[10px] tracking-wider
                                                     bg-success-soft text-success">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1
                                                     border-2 border-text font-black uppercase
                                                     text-[10px] tracking-wider
                                                     bg-surface-active text-text-muted">
                                            Tidak Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Action -->
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 flex-wrap">

                                        <a href="/registration/period/save?id=<?= (int) $period->id ?>"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-warning-soft text-black border-2 border-text shadow-brutal-sm font-black uppercase text-[10px] tracking-wider hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none transition-all duration-150">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </a>

                                        <form action="/registration/period/delete" method="POST" class="inline-block m-0"
                                            onsubmit="return confirm('Yakin ingin menghapus periode ini?')">

                                            <input type="hidden" name="id" value="<?= (int) $period->id ?>">

                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5
                                                           bg-danger text-text-invert
                                                           border-2 border-text shadow-brutal-sm
                                                           font-black uppercase text-[10px] tracking-wider
                                                           hover:translate-x-[2px] hover:translate-y-[2px]
                                                           hover:shadow-none transition-all duration-150">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Delete</span>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
    <?php elseif (empty($allPeriod)): ?>
        <div class="bg-surface border-4 border-text shadow-brutal p-8 text-center">
            <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-text-muted"></i>
            <p class="font-black uppercase tracking-wider text-text-muted">
                Belum ada periode pendaftaran.
            </p>
        </div>
    <?php endif; ?>

</div>