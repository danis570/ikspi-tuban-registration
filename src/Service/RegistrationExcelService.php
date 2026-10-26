<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationDressSize;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Repository\AdministrativeAreaRepository;
use DateTime;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Throwable;

class RegistrationExcelService
{
    private string $publicDirectory;
    private string $uploadDirectory;

    public function __construct()
    {
        $srcDirectory = dirname(__DIR__);

        $this->publicDirectory = realpath(
            $srcDirectory . '/../public'
        ) ?: throw new RuntimeException(
                'Direktori public tidak ditemukan.'
            );

        $this->uploadDirectory =
            $this->publicDirectory
            . DIRECTORY_SEPARATOR
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'registrations';
    }

    /**
     * @param array<int, array{
     *     registration: Registration,
     *     period: RegistrationPeriod
     * }> $registrations
     */
    public function generate(array $registrations): string
    {
        if ($registrations === []) {
            throw new RuntimeException(
                'Tidak ada peserta untuk dibuatkan Excel.'
            );
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pendaftaran');

        // 1. SUSUNAN HEADER BARU
        $headers = [
            'NO',
            'TINGKAT',
            'ANGKATAN',
            'NAMA',
            'TEMPAT / TANGGAL LAHIR',
            'ALAMAT DESA, RT/RW, KECAMATAN',
            'PEKERJAAN',
            'TEMPAT LATIHAN',
            'NAMA AYAH KANDUNG',
            'NAMA IBU KANDUNG',
            'NAMA PELATIH',
            'ALAMAT PELATIH',
            'UKURAN BAJU', // Kolom M (Judul Utama)
            '',            // Kolom N (Di-merge)
            '',            // Kolom O (Di-merge)
            '',            // Kolom P (Di-merge)
            'FOTO KECIL',
            'FOTO BESAR',
        ];

        // Kolom terakhir sekarang adalah 'R'
        $lastColumn = 'R';

        $sheet->fromArray($headers, null, 'A1');

        // --- MERGE CELL UNTUK HEADER UKURAN BAJU ---
        $sheet->mergeCells('M1:P1'); // Menggabungkan M1 sampai P1

        // Sub-header untuk baris ke-2
        $sheet->setCellValue('M2', 'M');
        $sheet->setCellValue('N2', 'L');
        $sheet->setCellValue('O2', 'XL');
        $sheet->setCellValue('P2', 'XXL');
        // ------------------------------------------

        // --- TAMBAHKAN KODE INI UNTUK MERGE HEADER LAINNYA ---
        // Daftar kolom yang headernya ingin ditengahkan (A sampai L, lalu Q dan R)
        $columnsToMerge = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'Q', 'R'];

        foreach ($columnsToMerge as $col) {
            // Menggabungkan baris 1 dan 2 untuk setiap kolom
            $sheet->mergeCells("{$col}1:{$col}2");
        }
        // ------------------------------------------------------

        $areaRepository = new AdministrativeAreaRepository(
            Database::getConnection()
        );

        // Data dimulai dari baris 3 karena header memakan 2 baris
        $row = 3;

        foreach ($registrations as $index => $item) {
            $registration = $item['registration'];
            $period = $item['period'];

            $areaNames = [
                'province' => $areaRepository->findByCode($registration->provinceCode)?->name ?? $registration->provinceCode,
                'regency' => $areaRepository->findByCode($registration->regencyCode)?->name ?? $registration->regencyCode,
                'district' => $areaRepository->findByCode($registration->districtCode)?->name ?? $registration->districtCode,
                'village' => $areaRepository->findByCode($registration->villageCode)?->name ?? $registration->villageCode,
            ];

            // Format Tanggal Lahir (Contoh: 10 JUNI 2011)
            $bulanIndo = [
                1 => 'JANUARI',
                'FEBRUARI',
                'MARET',
                'APRIL',
                'MEI',
                'JUNI',
                'JULI',
                'AGUSTUS',
                'SEPTEMBER',
                'OKTOBER',
                'NOVEMBER',
                'DESEMBER'
            ];

            $tglLahirIndo = '';
            if (!empty($registration->birthDate)) {
                try {
                    $dateObj = new \DateTime($registration->birthDate);
                    $tglLahirIndo = $dateObj->format('j') . ' ' . $bulanIndo[(int) $dateObj->format('n')] . ' ' . $dateObj->format('Y');
                } catch (\Exception $e) {
                    $tglLahirIndo = $registration->birthDate;
                }
            }

            // Gabungkan Tempat & Tanggal Lahir
            $ttl = strtoupper($registration->birthPlace . ', ' . $tglLahirIndo);

            // Gabungkan Alamat
            $specificAddress = trim($registration->specificAddress ?? '');

            $alamat = '';

            if ($specificAddress !== '') {
                $alamat .= strtoupper($specificAddress) . ', ';
            }

            $alamat .= 'DS. ' . strtoupper($areaNames['village'])
                . ' RT ' . $registration->rt
                . ' RW ' . $registration->rw
                . ' KEC. ' . strtoupper($areaNames['district'])
                . ' KAB. ' . strtoupper($areaNames['regency']);

            $dressSize = $registration->dressSize instanceof RegistrationDressSize
                ? $registration->dressSize->value
                : null;

            // Tulis nilai 1 atau 0 langsung ke sel Excel M sampai P.
            $sheet->setCellValue("M{$row}", (int) ($dressSize === 'M'));
            $sheet->setCellValue("N{$row}", (int) ($dressSize === 'L'));
            $sheet->setCellValue("O{$row}", (int) ($dressSize === 'XL'));
            $sheet->setCellValue("P{$row}", (int) ($dressSize === 'XXL'));

            // 2. SUSUNAN NILAI (VALUES) BARU
            $values = [
                $index + 1, // NO
                strtoupper((string) ($period->level->value ?? '')), // TINGKAT
                strtoupper((string) ($period->batch ?? '')), // ANGKATAN
                strtoupper((string) ($registration->fullName ?? '')), // NAMA
                $ttl, // TEMPAT / TANGGAL LAHIR
                $alamat, // ALAMAT
                strtoupper((string) ($registration->occupation ?? '')), // PEKERJAAN
                strtoupper((string) ($registration->trainingLocation ?? '')), // TEMPAT LATIHAN
                strtoupper((string) ($registration->fatherName ?? '')), // NAMA AYAH
                strtoupper((string) ($registration->motherName ?? '')), // NAMA IBU
                strtoupper((string) ($registration->coachName ?? '')), // NAMA PELATIH
                strtoupper((string) ($registration->coachAddress ?? '')), // ALAMAT PELATIH

            ];

            $sheet->fromArray($values, null, 'A' . $row);

            // Paksa RT/RW (jika ada) jadi teks, dan pastikan angka 0/1 ukuran baju tetap integer
            $sheet->getStyle("A{$row}:R{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            // Kembalikan format angka untuk NO dan Ukuran Baju agar tidak error "Number stored as text"
            $sheet->getStyle("A{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
            $sheet->getStyle("M{$row}:P{$row}")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);

            $smallPhotoPath = $this->getPhotoPath($registration->smallPhoto);
            $largePhotoPath = $this->getPhotoPath($registration->largePhoto);

            // 3. KOORDINAT FOTO BARU
            $this->addPhoto(
                $sheet,
                $smallPhotoPath,
                'Q' . $row, // Kolom Q
                70
            );

            $this->addPhoto(
                $sheet,
                $largePhotoPath,
                'R' . $row, // Kolom R
                100
            );

            // Tinggi baris cukup untuk foto besar.
            $sheet->getRowDimension($row)->setRowHeight(80);

            $row++;
        }

        $lastRow = $row - 1;

        // --- HEADER STYLING (Baris 1 & 2) ---
        $sheet->getStyle("A1:{$lastColumn}2")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'], // Teks hitam
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '8EA9DB'], // Warna Biru Muda (sesuai gambar)
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // Garis batas hitam
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(30);

        // --- ISI STYLING (Mulai baris 3) ---
        $sheet->getStyle("A3:{$lastColumn}{$lastRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'], // Garis batas hitam
                ],
            ],
        ]);

        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $sheet->freezePane('A3'); // Freeze baris 1 dan 2

        // 4. LEBAR KOLOM BARU
        $widths = [
            'A' => 5,  // NO
            'B' => 12, // TINGKAT
            'C' => 12, // ANGKATAN
            'D' => 30, // NAMA
            'E' => 30, // TEMPAT / TANGGAL LAHIR
            'F' => 45, // ALAMAT
            'G' => 15, // PEKERJAAN
            'H' => 20, // TEMPAT LATIHAN
            'I' => 25, // NAMA AYAH
            'J' => 25, // NAMA IBU
            'K' => 25, // NAMA PELATIH
            'L' => 30, // ALAMAT PELATIH
            'M' => 10, // M
            'N' => 10, // L
            'O' => 10, // XL
            'P' => 10, // XXL
            'Q' => 14, // FOTO KECIL
            'R' => 14, // FOTO BESAR
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Pengaturan cetak
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setFitToPage(true);

        $sheet->getPageMargins()->setTop(0.3);
        $sheet->getPageMargins()->setBottom(0.3);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);

        $sheet->getHeaderFooter()->setOddHeader('&C&BDATA PENDAFTARAN PESERTA');
        $sheet->getHeaderFooter()->setOddFooter('&LPendaftaran Peserta&RHalaman &P dari &N');

        $writer = new Xlsx($spreadsheet);
        ob_start();

        try {
            $writer->save('php://output');
            $excel = ob_get_clean();

            if ($excel === false) {
                throw new RuntimeException('Gagal membuat file Excel.');
            }

            return $excel;
        } catch (Throwable $exception) {
            ob_end_clean();
            throw $exception;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function addPhoto(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet,
        string $path,
        string $coordinate,
        int $height
    ): void {
        $drawing = new Drawing();
        $drawing->setName('Foto Peserta');
        $drawing->setDescription('Foto pendaftaran peserta');
        $drawing->setPath($path);
        $drawing->setHeight($height);
        $drawing->setCoordinates($coordinate);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(3);
        $drawing->setWorksheet($sheet);
    }

    private function getPhotoPath(?string $fileName): string
    {
        if (
            $fileName === null ||
            $fileName === '' ||
            basename($fileName) !== $fileName
        ) {
            throw new RuntimeException(
                'Nama file foto peserta tidak valid.'
            );
        }

        $path = realpath(
            $this->uploadDirectory
            . DIRECTORY_SEPARATOR
            . $fileName
        );

        $uploadDirectory = realpath($this->uploadDirectory);

        if (
            $path === false ||
            $uploadDirectory === false ||
            !is_file($path) ||
            !str_starts_with(
                $path,
                $uploadDirectory . DIRECTORY_SEPARATOR
            )
        ) {
            throw new RuntimeException(
                'Foto peserta tidak ditemukan: ' . $fileName
            );
        }

        return $path;
    }
}