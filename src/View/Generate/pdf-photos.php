<?php
/** @var Registration $registration */
/** @var string $smallPhotoPath */
/** @var string $largePhotoPath */
/** @var int $noUrut */

$escape = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$getImageBase64 = static function ($path) {
    if (empty($path) || !file_exists($path))
        return '';
    $type = pathinfo($path, PATHINFO_EXTENSION);
    if ($type === 'jpg')
        $type = 'jpeg';
    return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
};
$imgKecil = $getImageBase64($smallPhotoPath ?? '');
$imgBesar = $getImageBase64($largePhotoPath ?? '');

$bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$ttlString = '-';
if (!empty($registration->birthDate)) {
    try {
        $dateObj = new DateTime($registration->birthDate);
        $ttlString = ($registration->birthPlace ?? '-') . ', ' . $dateObj->format('j') . ' ' . $bulanIndo[(int) $dateObj->format('n')] . ' ' . $dateObj->format('Y');
    } catch (Exception $e) {
        $ttlString = $registration->birthDate;
    }
}
?>

<!-- Satu Kartu Peserta -->
<div class="card-item">
    <!-- TAMPILKAN NOMOR URUT DI SINI -->
    <h4 style="margin: 0 0 8px 0; font-size: 12px; font-weight: bold;">
        <?= $noUrut ?>. <?= $escape($registration->fullName) ?>
    </h4>

    <!-- Data Diri -->
    <div class="card-data">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 40%;">Tempat, tanggal lahir</td>
                <td style="width: 3%;">:</td>
                <td><?= $escape($ttlString) ?></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td><?= $escape($alamatLengkap ?? '-') ?></td>
            </tr>
            <tr>
                <td>Nama ayah</td>
                <td>:</td>
                <td><?= $escape($registration->fatherName) ?></td>
            </tr>
            <tr>
                <td>Nama pelatih</td>
                <td>:</td>
                <td><?= $escape($registration->coachName) ?></td>
            </tr>
            <tr>
                <td>Asal ranting</td>
                <td>:</td>
                <td><?= $escape($asalRanting ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <!-- Bagian Foto (Kiri & Kanan) -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <tr>
            <!-- Foto Kecil: Lebar kolom dibuat lebih kecil (hanya selebar fotonya) -->
            <td style="width: 100px; vertical-align: top; padding-left: 0px; text-align: left;">
                <div style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">Foto 3&times;3 cm:</div>
                <?php if ($imgKecil): ?>
                    <img src="<?= $imgKecil ?>" class="img-kecil" style="margin: 0;">
                <?php else: ?>
                    <div
                        style="width: 60px; height: 60px; border: 1px solid #ccc; text-align: center; line-height: 60px; background: #eee; font-size: 12px;">
                        -
                    </div>
                <?php endif; ?>
            </td>

            <!-- Foto Besar: Otomatis akan berada di sebelah kanan foto kecil -->
            <td style="vertical-align: top; text-align: left; padding-left: 0px;">
                <div style="font-weight: bold; font-size: 12px; margin-bottom: 5px;">Foto 7&times;10 cm:</div>
                <?php if ($imgBesar): ?>
                    <img src="<?= $imgBesar ?>" class="img-besar" style="margin: 0;">
                <?php else: ?>
                    <div
                        style="width: 80px; height: 110px; border: 1px solid #ccc; text-align: center; line-height: 110px; background: #eee; font-size: 12px;">
                        -
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>