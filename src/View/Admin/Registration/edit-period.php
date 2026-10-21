<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- ERROR -->
    <?php if (isset($error)): ?>
        <div class="p-4 bg-danger-soft text-danger border-4 border-text
                    shadow-brutal-sm font-bold text-sm">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- HEADER -->
    <div>
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            Edit
        </h1>

        <p class="mt-1 text-sm text-text-muted">
            Edit periode pendaftaran ini.
        </p>
    </div>

    <!-- FORM -->
    <form action="/registration/period/save" method="POST" class="bg-surface border-4 border-text shadow-brutal p-6 sm:p-8
               space-y-6 max-w-2xl">

        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $period->id) ?>">

        <!-- TINGKAT -->
        <div>
            <label for="level" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tingkat
            </label>

            <select id="level" name="level" class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">

                <option value="I" <?= $period->level->value === 'I' ? 'selected' : '' ?>>
                    I
                </option>

                <option value="II" <?= $period->level->value === 'II' ? 'selected' : '' ?>>
                    II
                </option>

            </select>
        </div>

        <!-- TANGGAL -->
        <div>
            <label for="date" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tanggal Pelaksanaan
            </label>

            <input type="date" id="date" name="date" value="<?= htmlspecialchars($period->date->format('Y-m-d')) ?>"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- WAKTU -->
        <div>
            <label for="time" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Jam
            </label>

            <input type="text" id="time" name="time" value="<?= htmlspecialchars($period->time) ?>"
                placeholder="Contoh: 08.00 WIB - selesai" class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm
                       placeholder:text-text-muted/60
                       focus:outline-none focus:ring-4 focus:ring-primary-ring
                       focus:border-primary transition-all">
        </div>

        <!-- LOKASI -->
        <div>
            <label for="location" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tempat
            </label>

            <input type="text" value="<?= htmlspecialchars($period->location) ?>" id="location" name="location"
                placeholder="Lokasi kegiatan"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- ANGKATAN -->
        <div>
            <label for="batch" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Angkatan
            </label>

            <input type="number" value="<?= htmlspecialchars($period->batch) ?>" id="batch" name="batch"
                placeholder="Contoh: 140" class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- JENIS -->
        <div>
            <label for="type" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tipe Pengesahan
            </label>

            <select id="type" name="type" class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
                <option value="JARAK DEKAT" <?= $period->type->value === 'JARAK DEKAT' ? 'selected' : '' ?>>JARAK DEKAT
                </option>
                <option value="JARAK JAUH" <?= $period->type->value === 'JARAK JAUH' ? 'selected' : '' ?>>JARAK JAUH
                </option>
            </select>
        </div>

        <!-- KELAS -->
        <div>
            <label for="class" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Kelas
            </label>

            <input type="text" value="<?= htmlspecialchars($period->class) ?>" id="class" name="class"
                placeholder="Kelas atau tingkatan"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- WILAYAH -->
        <div>
            <label for="region" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Wilayah
            </label>

            <input type="text" value="<?= htmlspecialchars($period->region) ?>" id="region" name="region"
                placeholder="Wilayah pendaftaran"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- STATUS -->
        <div>
            <label for="isActive" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Status
            </label>

            <select id="isActive" name="isActive"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
                <option value="1" <?= $period->isActive == 1 ? 'selected' : '' ?>>Aktif</option>
                <option value="0" <?= $period->isActive == 0 ? 'selected' : '' ?>>Tidak Aktif</option>
            </select>
        </div>

        <!-- INFORMASI -->
        <div>
            <label for="information" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Informasi
            </label>

            <textarea id="information" name="information" rows="4" placeholder="Informasi tambahan tentang pendaftaran"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm"><?= htmlspecialchars($period->information ?? '') ?></textarea>
        </div>

        <!-- ACTIONS -->
        <div class="border-t-2 border-dashed border-border-strong pt-6
                    flex flex-col sm:flex-row gap-3">

            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3
                       bg-cta text-cta-text border-4 border-text shadow-brutal-sm
                       font-black uppercase text-sm tracking-wider
                       hover:translate-x-[3px] hover:translate-y-[3px]
                       hover:shadow-none transition-all duration-150">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Simpan Periode</span>
            </button>

            <a href="/registration/period/all" class="inline-flex items-center justify-center gap-2 px-5 py-3
                       bg-surface text-text border-4 border-text
                       font-black uppercase text-sm tracking-wider
                       hover:bg-surface-hover transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Batal</span>
            </a>
        </div>

    </form>
</div>