<?php
// HAPUS semua deklarasi variabel $registration tunggal di atas.

$escape = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$getImageBase64 = static function ($path) {
    if (empty($path) || !file_exists($path))
        return '';
    $type = pathinfo($path, PATHINFO_EXTENSION);
    if ($type === 'jpg')
        $type = 'jpeg';
    return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
};

$bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
?>

<!-- KOP SURAT (Hanya dirender sekali) -->
<div class="kop-rekap">
    <h1>IKS PI KERA SAKTI CABANG TUBAN</h1>
    <h2>REKAP DATA PENDAFTARAN CALON WARGA</h2>
    <div class="garis-kop"></div>
</div>

<div class="page-title">Rekap Data Calon Warga</div>

<table class="table-rekap">
    <thead>
        <tr>
            <th class="col-no">No</th>
            <th class="col-foto-kecil">Foto 3&times;3</th>
            <th class="col-foto-besar">Foto 7&times;10</th>
            <th class="col-nama">Nama Lengkap</th>
            <th class="col-ttl">Tempat, Tanggal Lahir</th>
            <th class="col-alamat">Alamat</th>
            <th class="col-ayah">Nama Ayah</th>
            <th class="col-pelatih">Nama Pelatih</th>
            <th class="col-ranting">Asal Ranting</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1;
        foreach ($registrations as $item): ?>
            <?php
            $reg = $item['registration'];
            $areaNames = $item['areaNames'];

            // Format TTL
            $ttlString = '-';
            if (!empty($reg->birthDate)) {
                try {
                    $dateObj = new DateTime($reg->birthDate);
                    $ttlString = ($reg->birthPlace ?? '-') . ', ' . $dateObj->format('j') . ' ' . $bulanIndo[(int) $dateObj->format('n')] . ' ' . $dateObj->format('Y');
                } catch (Exception $e) {
                    $ttlString = $reg->birthDate;
                }
            }

            // Format Alamat
            $alamatLengkap = "Desa/Kel. " . ($areaNames['village'] ?? '-') . " RT " . ($reg->rt ?? '-') . " RW " . ($reg->rw ?? '-') . ", Kec. " . ($areaNames['district'] ?? '-') . ", Kab. " . ($areaNames['regency'] ?? '-') . ", Prov. " . ($areaNames['province'] ?? '-');

            // Foto
            $imgKecil = $getImageBase64($item['smallPhotoPath'] ?? '');
            $imgBesar = $getImageBase64($item['largePhotoPath'] ?? '');
            ?>
            <tr>
                <td class="col-no"><?= $no++ ?></td>
                <td class="col-foto-kecil">
                    <?php if ($imgKecil): ?><img src="<?= $imgKecil ?>"
                            style="width: 40px; height: 40px; object-fit: cover;"><?php else: ?>-<?php endif; ?>
                </td>
                <td class="col-foto-besar">
                    <?php if ($imgBesar): ?><img src="<?= $imgBesar ?>"
                            style="width: 50px; height: 70px; object-fit: cover;"><?php else: ?>-<?php endif; ?>
                </td>
                <td class="col-nama"><?= $escape($reg->fullName) ?></td>
                <td class="col-ttl"><?= $escape($ttlString) ?></td>
                <td class="col-alamat"><?= $escape($alamatLengkap) ?></td>
                <td class="col-ayah"><?= $escape($reg->fatherName) ?></td>
                <td class="col-pelatih"><?= $escape($reg->coachName) ?></td>
                <td class="col-ranting">
                    <?= $escape($item['asalRanting'] ?? '-') ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>