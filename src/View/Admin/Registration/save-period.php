<?php

use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodType;

$isUpdate = isset($period) && $period !== null;

$value = static function (string $property) use ($isUpdate, $period): string {
    if (!$isUpdate || !isset($period->{$property})) {
        return '';
    }

    $result = $period->{$property};

    if ($result instanceof DateTimeInterface) {
        return $result->format('Y-m-d');
    }

    if ($result instanceof BackedEnum) {
        return (string) $result->value;
    }

    if (is_bool($result)) {
        return $result ? '1' : '0';
    }

    return (string) $result;
};
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

    <!-- FLASH MESSAGE -->
    <?php if (!empty($_SESSION['flash']['message'])): ?>
        <div class="p-4 bg-success-soft text-success border-4 border-text
                    shadow-brutal-sm font-bold text-sm">
            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
        </div>
        <?php unset($_SESSION['flash']['message']); ?>
    <?php endif; ?>

    <!-- ERROR -->
    <?php if (!empty($_SESSION['flash']['error'])): ?>
        <div class="p-4 bg-danger-soft text-danger border-4 border-text
                shadow-brutal-sm font-bold text-sm">
            <?= htmlspecialchars($_SESSION['flash']['error']) ?>
        </div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>

    <!-- HEADER -->
    <div>
        <h1 class="font-display text-2xl sm:text-3xl uppercase tracking-tight text-text">
            <?= $isUpdate ? 'Update Registration Period' : 'Add Registration Period' ?>
        </h1>

        <p class="mt-1 text-sm text-text-muted">
            <?= $isUpdate
                ? 'Perbarui informasi periode pendaftaran.'
                : 'Tambahkan periode pendaftaran baru.' ?>
        </p>
    </div>

    <!-- FORM -->
    <form action="/registration/period/save" method="POST" class="bg-surface border-4 border-text shadow-brutal p-6 sm:p-8
               space-y-6 max-w-2xl">

        <?php if ($isUpdate): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars((string) $period->id) ?>">
        <?php endif; ?>

        <!-- TINGKAT -->
        <div>
            <label for="level" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tingkat
            </label>

            <select id="level" name="level" required
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
                <option value="">Pilih Tingkat</option>

                <?php foreach (RegistrationPeriodLevel::cases() as $level): ?>
                    <option value="<?= htmlspecialchars($level->value) ?>" <?= $value('level') === $level->value ? 'selected' : '' ?>>
                        <?= htmlspecialchars($level->value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- TANGGAL -->
        <div>
            <label for="date" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tanggal Pelaksanaan
            </label>

            <input type="date" id="date" name="date" value="<?= htmlspecialchars($value('date')) ?>" required
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- WAKTU -->
        <div>
            <label for="time" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Jam
            </label>

            <input type="text" id="time" name="time" value="<?= htmlspecialchars($value('time')) ?>"
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

            <input type="text" id="location" name="location" value="<?= htmlspecialchars($value('location')) ?>"
                placeholder="Lokasi kegiatan"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- ANGKATAN -->
        <div>
            <label for="batch" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Angkatan
            </label>

            <input type="number" id="batch" name="batch" value="<?= htmlspecialchars($value('batch')) ?>"
                placeholder="Contoh: 140" class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- JENIS -->
        <div>
            <label for="type" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Tipe Pengesahan
            </label>

            <select id="type" name="type" required
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
                <option value="">Pilih Jenis</option>

                <?php foreach (RegistrationPeriodType::cases() as $type): ?>
                    <option value="<?= htmlspecialchars($type->value) ?>" <?= $value('type') === $type->value ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type->value) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- KELAS -->
        <div>
            <label for="class" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Kelas
            </label>

            <input type="text" id="class" name="class" value="<?= htmlspecialchars($value('class')) ?>"
                placeholder="Kelas atau tingkatan"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- WILAYAH -->
        <div>
            <label for="region" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Wilayah
            </label>

            <input type="text" id="region" name="region" value="<?= htmlspecialchars($value('region')) ?>"
                placeholder="Wilayah pendaftaran"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
        </div>

        <!-- STATUS -->
        <div>
            <label for="isActive" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Status
            </label>

            <select id="isActive" name="isActive" required
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm">
                <option value="1" <?= $value('isActive') === '1' ? 'selected' : (!$isUpdate ? 'selected' : '') ?>>
                    Aktif
                </option>
                <option value="0" <?= $value('isActive') === '0' ? 'selected' : '' ?>>
                    Tidak Aktif
                </option>
            </select>
        </div>

        <!-- INFORMASI -->
        <div>
            <label for="information" class="block mb-1.5 font-black uppercase text-xs tracking-wider text-text">
                Informasi
            </label>

            <textarea id="information" name="information" rows="4" placeholder="Informasi tambahan tentang pendaftaran"
                class="w-full px-4 py-3 bg-surface text-text border-4 border-text text-sm"><?= htmlspecialchars($value('information')) ?></textarea>
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
                <span><?= $isUpdate ? 'Update Periode' : 'Simpan Periode' ?></span>
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