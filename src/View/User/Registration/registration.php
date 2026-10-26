<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <?php if (empty($periods)): ?>
        <!-- Empty State -->
        <div class="bg-surface border-4 border-text shadow-brutal p-6 sm:p-8 text-center max-w-2xl">
            <i data-lucide="info" class="w-10 h-10 mx-auto text-text-muted mb-3"></i>
            <p class="font-black uppercase tracking-wider text-text-muted">
                Belum ada periode pendaftaran yang dibuka.
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-3xl">
            <?php foreach ($periods as $period): ?>
                <?php
                // Tentukan URL dan gaya berdasarkan tingkatannya
                $isLevelI = ($period->level->value === 'I');
                $url = $isLevelI ? '/registration/level/i' : '/registration/level/ii';

                // Kelas styling neobrutalism
                $cardClass = $isLevelI
                    ? 'bg-primary text-text-invert'
                    : 'bg-surface text-text';

                $iconClass = $isLevelI
                    ? 'text-text-invert'
                    : 'text-primary';
                ?>

                <a href="<?= $url ?>"
                    class="group flex flex-col items-center justify-center p-6 <?= $cardClass ?> border-4 border-text shadow-brutal hover:translate-x-[4px] hover:translate-y-[4px] hover:shadow-none transition-all duration-150">
                    <?php if ($isLevelI): ?>
                        <i data-lucide="tally-1"
                            class="w-8 h-8 mb-3 <?= $iconClass ?> group-hover:-translate-y-1 transition-transform"></i>
                    <?php else: ?>
                        <i data-lucide="tally-2"
                            class="w-8 h-8 mb-3 <?= $iconClass ?> group-hover:-translate-y-1 transition-transform"></i>
                    <?php endif; ?>

                    <span class="font-display text-xl uppercase tracking-wider text-center">
                        Registrasi<br>Tingkat <?= htmlspecialchars($period->level->value) ?>
                        Angkatan <?= htmlspecialchars($period->batch) ?>
                    </span>
                    <?php if (!empty($period->information)): ?>

                        <?php
                        $items = preg_split(
                            '/\s*\d+\.\s*/',
                            trim($period->information),
                            -1,
                            PREG_SPLIT_NO_EMPTY
                        );
                        ?>

                        <ol class="mt-4 w-full list-decimal list-outside pl-6 space-y-2 text-left text-sm text-white">
                            <?php foreach ($items as $item): ?>
                                <li>
                                    <?= htmlspecialchars(trim($item), ENT_QUOTES, 'UTF-8') ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>

                    <?php endif; ?>
                </a>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>