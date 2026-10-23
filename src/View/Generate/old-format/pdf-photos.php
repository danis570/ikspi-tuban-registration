<?php

use Danish\IkspiTubanRegistration\Domain\Registration;

/** @var Registration $registration */
/** @var string $smallPhotoPath */
/** @var string $largePhotoPath */

$escape = static fn($value): string => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

// Konversi cm ke pixel (1cm = 37.795px pada 96 DPI)
$smallSize = 3 * 37.795; // 3x3 cm = ~113px
$largeWidth = 7 * 37.795; // 7 cm = ~264px
$largeHeight = 10 * 37.795; // 10 cm = ~378px
?>

<section class="data-page participant-page">

    <!-- Jarak dari atas kertas -->
    <div style="margin-top: 1cm;">

        <h2 style="text-align: center; margin-bottom: 30px; text-decoration: underline;">LAMPIRAN FOTO PESERTA</h2>

        <!-- FOTO KECIL (3x3) -->
        <div style="text-align: center; margin-bottom: 20px;">
            <p style="font-weight: bold; margin-bottom: 5px;">Foto Ukuran 3 x 3</p>

            <div
                style="border: 1px solid #000; width: <?= $smallSize ?>px; height: <?= $smallSize ?>px; margin: 0 auto; overflow: hidden; background-color: #f9f9f9;">
                <?php if (!empty($smallPhotoPath)): ?>
                    <img src="<?= $escape($smallPhotoPath) ?>" style="width: 100%; height: 100%; object-fit: cover;"
                        alt="Foto Kecil">
                <?php else: ?>
                    <div style="line-height: <?= $smallSize ?>px; color: #999; font-size: 10px;">Tidak ada foto</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- FOTO BESAR (7x10) -->
        <div style="text-align: center;">
            <p style="font-weight: bold; margin-bottom: 5px;">Foto Ukuran 7 x 10</p>

            <div
                style="border: 1px solid #000; width: <?= $largeWidth ?>px; height: <?= $largeHeight ?>px; margin: 0 auto; overflow: hidden; background-color: #f9f9f9;">
                <?php if (!empty($largePhotoPath)): ?>
                    <img src="<?= $escape($largePhotoPath) ?>" style="width: 100%; height: 100%; object-fit: cover;"
                        alt="Foto Besar">
                <?php else: ?>
                    <div style="line-height: <?= $largeHeight ?>px; color: #999; font-size: 10px;">Tidak ada foto</div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>