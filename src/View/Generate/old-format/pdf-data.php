<?php

use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;

/** @var Registration $registration */
/** @var RegistrationPeriod $period */
/** @var array $areaNames */

// --- PATH GAMBAR (Sesuaikan dengan lokasi file) ---
$kopSuratPath = __DIR__ . '/kop-surat.png';
$qrCodePath = __DIR__ . '/qr-code.png'; // Anggap gambar ini sudah termasuk stempel & ttd ketua

// Fungsi Base64
$getImage = static function (string $path): string {
    if (!file_exists($path))
        return '';
    $type = pathinfo($path, PATHINFO_EXTENSION);
    if ($type === 'jpg')
        $type = 'jpeg';
    return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
};

$kopBase64 = $getImage($kopSuratPath);
$qrBase64  = $getImage($qrCodePath);

$escape = static fn($value): string => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

// --- MAPPING DATA ---
$nama = $registration->fullName ?? '-';
// 1. Siapkan Array Bulan
$bulanIndo = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];

// 2. Format Tanggal Lahir
$tglLahirIndo = '-';
if (!empty($registration->birthDate)) {
    try {
        // Ubah string dari database menjadi object DateTime
        $dateObj = new DateTime($registration->birthDate);

        $d = $dateObj->format('j'); // Tanggal (tanpa 0 di depan)
        $m = (int) $dateObj->format('n'); // Bulan (1-12)
        $y = $dateObj->format('Y'); // Tahun

        $tglLahirIndo = $d . ' ' . $bulanIndo[$m] . ' ' . $y;
    } catch (Exception $e) {
        // Jika format tanggal di database berantakan/tidak valid, tampilkan aslinya
        $tglLahirIndo = $registration->birthDate;
    }
}

// 3. Gabungkan dengan Tempat Lahir
$ttl = ($registration->birthPlace ?? '-') . ', ' . $tglLahirIndo;
$village = $areaNames['village'] ?? '-';
$district = $areaNames['district'] ?? '-';
$regency = $areaNames['regency'] ?? '-';
$province = $areaNames['province'] ?? '-';
$rt = $registration->rt ?? '-';
$rw = $registration->rw ?? '-';

$alamatLengkap = "Desa/Kel. {$village} Rt. {$rt} Rw {$rw}, <br> Kec. {$district}, Kab. {$regency}, Prov. {$province}";
$pekerjaan = $registration->occupation ?? '-';
$pelatih = $registration->coachName ?? '-';
$tempatLatihan = $registration->trainingLocation ?? '-';
$namaAyah = $registration->fatherName ?? '-';
$namaIbu = $registration->motherName ?? '-';
$alamatPelatih = $registration->coachAddress ?? '-';

$namaHari = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

$hariInggris = ($period->date) ? $period->date->format('l') : '-';
$hari = isset($namaHari[$hariInggris]) ? $namaHari[$hariInggris] : $hariInggris;

$bulanIndo = [
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
];

if ($period->date) {
    $hariAngka = $period->date->format('j'); // 'j' menghasilkan angka tanpa leading zero (misal: 9, bukan 09)
    $bulanAngka = (int) $period->date->format('n'); // 'n' menghasilkan angka bulan (1-12)
    $tahun = $period->date->format('Y');

    $tanggal = $hariAngka . ' ' . $bulanIndo[$bulanAngka] . ' ' . $tahun;
} else {
    $tanggal = '-';
}

$waktu = $period->time ?? '-';
$lokasi = $period->location ?? '-';
$angkatan = $period->batch ?? '-';
$pengesahan = $period->type->value ?? '-';
$kelas = $period->class ?? '-';
$wilayah = $period->region ?? 'JATIM DAN JATENG';

?>

<section class="data-page">
    <!-- 1. KOP SURAT -->
    <img src="<?= $kopBase64 ?>" class="kop-surat" alt="Kop Surat">

    <!-- 2. HEADER (PULAU JAWA & JUDUL) -->
    <div class="header-section">
        <div class="box-pulau-jawa">Pulau Jawa</div>

        <div class="title-center">
            <h3>FORMULIR PENGESAHAN TK. I</h3>
            <h4>SABUK KUNING KE SABUK BIRU</h4>
        </div>
    </div>

    <!-- 3. DATA UTAMA (Format Titik Dua Sejajar) -->
    <div class="data-section">
        <table class="data-table">
            <tr>
                <td class="label">TANGGAL</td>
                <td class="colon">:</td>
                <td class="value"><?= $escape($tanggal) ?></td>
            </tr>
            <tr>
                <td class="label">HARI / JAM</td>
                <td class="colon">:</td>
                <td class="value"><?= $escape($hari) ?>, <?= $escape($waktu) ?></td>
            </tr>
            <tr>
                <td class="label">TEMPAT</td>
                <td class="colon">:</td>
                <td class="value"><?= $escape($lokasi) ?></td>
            </tr>
            <tr>
                <td class="label">ANGKATAN</td>
                <td class="colon">:</td>
                <td class="value" style="font-weight: bold;"><?= $escape($angkatan) ?></td>
            </tr>
            <tr>
                <td class="label">PENGESAHAN</td>
                <td class="colon">:</td>
                <td class="value"><?= $escape($pengesahan) ?></td>
            </tr>
            <tr>
                <td class="label">KELAS</td>
                <td class="colon">:</td>
                <td class="value"><?= $escape($kelas) ?></td>
            </tr>
            <tr>
                <td class="label">WILAYAH</td>
                <td class="colon">:</td>
                <td class="value"><?= $escape($wilayah) ?></td>
            </tr>
        </table>
    </div>

    <!-- 4. DATA DIRI -->
    <p style="margin: 10px 0 5px 0; font-size: 12px;">Yang bertanda tangan dibawah ini, kami :</p>

    <table class="data-diri-table">
        <tr>
            <td class="label">NAMA</td>
            <td class="colon">:</td>
            <td class="value"><?= $escape($nama) ?></td>
        </tr>
        <tr>
            <td class="label">TEMPAT/TGL. LAHIR</td>
            <td class="colon">:</td>
            <td class="value"><?= $escape($ttl) ?></td>
        </tr>
        <tr>
            <td class="label">ALAMAT</td>
            <td class="colon">:</td>
            <!-- Jika alamat panjang, kita bisa buat baris kedua. Di gambar, Kec & Kab turun ke bawah -->
            <td class="value">
                <?= $alamatLengkap ?><br>
            </td>
        </tr>
        <tr>
            <td class="label">PEKERJAAN</td>
            <td class="colon">:</td>
            <td class="value"><?= $escape($pekerjaan) ?></td>
        </tr>
        <tr>
            <td class="label">TEMPAT LATIHAN</td>
            <td class="colon">:</td>
            <td class="value"><?= $escape($tempatLatihan) ?></td>
        </tr>
        <tr>
            <!-- Di gambar digabung NAMA AYAH/IBU -->
            <td class="label">NAMA AYAH/IBU</td>
            <td class="colon">:</td>
            <td class="value"><?= $escape($namaAyah) ?>, <?= $escape($namaIbu) ?></td>
        </tr>
        <tr>
            <td class="label">NAMA PELATIH</td>
            <td class="colon">:</td>
            <td class="value"><?= $escape($pelatih) ?></td>
        </tr>
        <tr>
            <td class="label">ALAMAT PELATIH</td>
            <td class="colon">:</td>
            <td class="value"><?= $escape($alamatPelatih) ?></td>
        </tr>
    </table>

    <!-- 5. PERNYATAAN -->
    <p style="margin: 0 0 5px 0; font-size: 12px; text-align: justify;">
        Dengan ini menyatakan diri menjadi Warga IKS. PI KERA SAKTI TK. I dengan sukarela dan sanggup memenuhi
        persyaratan sebagai berikut :
    </p>

    <!-- Menggunakan <ol> agar nomornya otomatis urut -->
    <ol class="pernyataan-list" style="padding-left: 20px;">
        <li>Menyerahkan PAS FOTO setengah badan ukuran 3 x 3 sebanyak 2 lembar dengan Bigroun / selambu biru.</li>
        <li>Menyerahkan PAS FOTO satu badan ukuran 7 x 10 sebanyak 1 lembar, dibelakang foto ditulis identitas lengkap.
        </li>
        <li>Sudah berusia 13 tahun keatas.</li>
        <li>Tidak mempunyai sakit syaraf dan ayan.</li>
        <li>Tidak akan menuntut Perguruan apabila selama latihan / ujian mengalami keseleo, lecet ...dsb.</li>
        <li>Menyerahkan buah-buahan (bebas) sebagai penghormatan kepada Warga/Pendekar angkatan sebelumnya.</li>
        <li>Menyatakan diri tidak terikat / keluar dari Perguruan lain.</li>
        <li>Melampirkan foto copy <b>Serifikat Sabuk Kuning</b>.</li>
        <li style="margin-bottom: 5px;">Membayar biaya Pengesahan dengan rincian :</li>
    </ol>

    <!-- 6. RINCIAN BIAYA -->
    <table class="biaya-table" style="margin-left: 5%; margin-bottom: 0; width: 80%;">
        <tr>
            <td class="no">a).</td>
            <td class="desc">Untuk Pusat sebesar<br><span style="font-size: 9px; font-style: italic;">( guna Lambang,
                    Ijazah, Sabuk, KTA, Amalan, Padepokan, Kalender )</span></td>
            <td class="colon">:</td>
            <td class="rp">Rp.</td>
            <td class="amount">900.000</td>
        </tr>
        <tr>
            <td class="no">b).</td>
            <td class="desc">Biaya Oprasional Cabang dan Ranting</td>
            <td class="colon">:</td>
            <td class="rp">Rp.</td>
            <td class="amount">20.000</td>
        </tr>
        <tr>
            <td class="no">c).</td>
            <td class="desc">Biaya Pembinaan Ketua Ranting</td>
            <td class="colon">:</td>
            <td class="rp">Rp.</td>
            <td class="amount">50.000</td>
        </tr>
        <tr>
            <td class="no">d).</td>
            <td class="desc">Sumbangan Sekretariat Cabang</td>
            <td class="colon">:</td>
            <td class="rp">Rp.</td>
            <td class="amount">100.000</td>
        </tr>
        <tr>
            <td class="no">e).</td>
            <td class="desc">Data Taktis</td>
            <td class="colon">:</td>
            <td class="rp">Rp.</td>
            <!-- HAPUS border-bottom di sini, biarkan teksnya saja -->
            <td class="amount" style="padding-right: 110px;">30.000</td>
        </tr>

        <!-- TAMBAHKAN BARIS BARU KHUSUS UNTUK GARIS BAWAH -->
        <tr>
            <td></td>
            <td></td>
            <td colspan="2" style="border-bottom: 1px solid #000; height: 1px; padding: 0;"></td>
            <td style="border-bottom: 1px solid #000; padding: 0; height: 1px;"></td>
        </tr>
    </table>

    <!-- TOTAL BIAYA -->
    <table style="width: 100%; font-size: 10.5px; margin-top: 5px;">
        <tr>

            <td style="width: 43%; text-align: right; font-style: italic; font-weight: bold; padding-right: 10px;">
                JUMLAH BIAYA PENGESAHAN</td>
            <td style="width: 2%; text-align: center; font-style: italic; font-weight: bold;">:</td>
            <td style="width: 5%; text-align: left; font-weight: bold;">Rp.</td>
            <td style="width: 33%; text-align: left; font-weight: bold;">1.100.000</td>
        </tr>
    </table>

    <!-- 7. TANDA TANGAN -->
    <!-- 7. TANDA TANGAN -->
    <div class="ttd-container">

        <!-- Baris Atas: Kiri (Wali) & Kanan (Siswa) -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <!-- Sisi Kiri: Diubah menjadi text-align: center -->
                <td style="width: 50%; text-align: center; vertical-align: top; padding-right: 120px;">
                    <div class="ttd-title">Menyetujui<br>Orang Tua /Wali Murid</div>
                    <div style="height: 50px;"></div> <!-- Ruang untuk tanda tangan -->
                    <div class="ttd-name">SUMAILIK</div>
                </td>

                <!-- Sisi Kanan: Diubah menjadi text-align: center -->
                <td style="width: 50%; text-align: center; vertical-align: top; padding-left: 120px;">
                    <div class="ttd-title">Calon Warga<br>&nbsp;</div> <!-- &nbsp; agar sejajar dengan kiri -->
                    <div style="height: 50px;"></div> <!-- Ruang untuk tanda tangan -->
                    <div class="ttd-name"><?= $escape($nama) ?></div>
                </td>
            </tr>
        </table>

        <!-- Baris Bawah: Tengah (Ketua) -->
        <div class="ttd-center" style="margin-top: -40px;">
            <div class="ttd-center-title">
                Ketua<br>
                IKS PI KERA SAKTI<br>
                Cabang Tuban
            </div>

            <img src="<?= $qrBase64 ?>" class="img-qr" alt="QR Code Ketua">

            <div class="ttd-center-name">SUTAJI</div>
        </div>

    </div>
</section>