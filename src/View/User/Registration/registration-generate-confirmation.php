<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <div class="space-y-2">
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            Konfirmasi Generate
        </h1>

        <p class="text-sm text-text-muted">
            Periksa peserta sebelum membuat PDF atau Excel.
        </p>
    </div>

    <div class="bg-surface border-4 border-text shadow-brutal p-4">
        <p class="font-black uppercase text-sm">
            Total peserta dipilih:
            <?= count($registrations) ?>
        </p>
    </div>

    <div class="bg-surface border-4 border-text shadow-brutal overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-text text-text-invert">
                    <tr>
                        <th class="text-left px-4 py-3">No</th>
                        <th class="text-left px-4 py-3">Nama</th>
                        <th class="text-left px-4 py-3">Tingkat</th>
                        <th class="text-left px-4 py-3">Angkatan</th>
                    </tr>
                </thead>

                <tbody class="divide-y-4 divide-text">
                    <?php foreach ($registrations as $index => $data): ?>
                        <?php
                        $registration = $data['registration'];
                        $period = $data['period'];
                        ?>

                        <tr>
                            <td class="px-4 py-3 font-bold">
                                <?= $index + 1 ?>
                            </td>

                            <td class="px-4 py-3 font-bold">
                                <?= htmlspecialchars(
                                    $registration->fullName,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td class="px-4 py-3 font-bold">
                                <?= htmlspecialchars(
                                    $period->level->value,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td class="px-4 py-3 font-bold">
                                <?= htmlspecialchars(
                                    (string) $period->batch,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    $selectedIds = array_map(
        static fn(array $item): int => $item['registration']->id,
        $registrations
    );

    $selectedIdsJson = htmlspecialchars(
        json_encode($selectedIds, JSON_THROW_ON_ERROR),
        ENT_QUOTES,
        'UTF-8'
    );
    ?>

    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3">

        <a href="/registration/all" class="inline-flex items-center justify-center gap-2 px-5 py-3
                   bg-surface border-4 border-text shadow-brutal-sm
                   font-black uppercase text-sm
                   hover:translate-x-[3px] hover:translate-y-[3px]
                   hover:shadow-none transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar
        </a>

        <form action="/registration/generate/pdf" method="POST" target="_blank">
            <input type="hidden" name="selected_registration_ids" value="<?= $selectedIdsJson ?>">

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3
                       bg-primary text-white border-4 border-text
                       shadow-brutal-sm font-black uppercase text-sm
                       hover:translate-x-[3px] hover:translate-y-[3px]
                       hover:shadow-none transition-all">
                <i data-lucide="file-down" class="w-4 h-4"></i>
                Generate PDF
            </button>
        </form>

        <form action="/registration/generate/excel" method="POST">
            <input type="hidden" name="selected_registration_ids" value="<?= $selectedIdsJson ?>">

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3
                       bg-secondary text-text border-4 border-text
                       shadow-brutal-sm font-black uppercase text-sm
                       hover:translate-x-[3px] hover:translate-y-[3px]
                       hover:shadow-none transition-all">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                Download Excel
            </button>
        </form>

    </div>

</div>