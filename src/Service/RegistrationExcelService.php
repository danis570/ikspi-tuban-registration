<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Repository\AdministrativeAreaRepository;
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

        $headers = [
            'TINGKAT',
            'ANGKATAN',
            'TANGGAL PERIODE',
            'WAKTU PERIODE',
            'LOKASI PERIODE',
            'JENIS PERIODE',
            'KELAS',
            'WILAYAH PERIODE',
            'STATUS PERIODE',
            'INFORMASI TAMBAHAN',
            'NO',
            'NAMA LENGKAP',
            'TEMPAT LAHIR',
            'TANGGAL LAHIR',
            'PROVINSI',
            'KABUPATEN/KOTA',
            'KECAMATAN',
            'DESA/KELURAHAN',
            'RT',
            'RW',
            'PEKERJAAN',
            'TEMPAT LATIHAN',
            'NAMA AYAH',
            'NAMA IBU',
            'NAMA PELATIH',
            'ALAMAT PELATIH',
            'FOTO KECIL',
            'FOTO BESAR',
        ];

        $lastColumn = 'AB';

        $sheet->fromArray($headers, null, 'A1');

        $areaRepository = new AdministrativeAreaRepository(
            Database::getConnection()
        );

        $row = 2;

        foreach ($registrations as $index => $item) {
            $registration = $item['registration'];
            $period = $item['period'];

            $areaNames = [
                'province' => $areaRepository
                    ->findByCode($registration->provinceCode)?->name
                    ?? $registration->provinceCode,

                'regency' => $areaRepository
                    ->findByCode($registration->regencyCode)?->name
                    ?? $registration->regencyCode,

                'district' => $areaRepository
                    ->findByCode($registration->districtCode)?->name
                    ?? $registration->districtCode,

                'village' => $areaRepository
                    ->findByCode($registration->villageCode)?->name
                    ?? $registration->villageCode,
            ];

            // Data periode dan peserta mengikuti PDF.
            $values = [
                $period->level->value,
                (string) $period->batch,
                $period->date->format('d-m-Y'),
                $period->time,
                $period->location,
                $period->type->value,
                $period->class,
                $period->region,
                $period->isActive ? 'Aktif' : 'Tidak aktif',
                $period->information ?? '-',

                $index + 1,
                $registration->fullName,
                $registration->birthPlace,
                $registration->birthDate,
                $areaNames['province'],
                $areaNames['regency'],
                $areaNames['district'],
                $areaNames['village'],
                $registration->rt,
                $registration->rw,
                $registration->occupation,
                $registration->trainingLocation,
                $registration->fatherName,
                $registration->motherName,
                $registration->coachName,
                $registration->coachAddress,
            ];

            // Ubah semua teks menjadi uppercase, kecuali yang bertipe angka (No)
            $valuesUppercase = array_map(function ($val) {
                // Jika tipenya string, ubah ke uppercase. Jika angka, biarkan.
                return is_string($val) ? strtoupper($val) : $val;
            }, $values);

            $sheet->fromArray(
                $valuesUppercase,
                null,
                'A' . $row
            );

            // Pertahankan RT/RW dan data lain sebagai teks.
            foreach (['S', 'T'] as $column) {
                $sheet->getCell($column . $row)
                    ->setValueExplicit(
                        (string) $sheet->getCell(
                            $column . $row
                        )->getValue(),
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
            }

            $smallPhotoPath = $this->getPhotoPath(
                $registration->smallPhoto
            );

            $largePhotoPath = $this->getPhotoPath(
                $registration->largePhoto
            );

            $this->addPhoto(
                $sheet,
                $smallPhotoPath,
                'AA' . $row,
                70
            );

            $this->addPhoto(
                $sheet,
                $largePhotoPath,
                'AB' . $row,
                100
            );

            // Tinggi baris cukup untuk foto besar.
            $sheet->getRowDimension($row)->setRowHeight(80);

            $row++;
        }

        $lastRow = $row - 1;

        // Header.
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '166534'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(36);

        // Isi tabel.
        $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Filter dan freeze header.
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $sheet->freezePane('A2');

        // Lebar kolom periode.
        $widths = [
            'A' => 10,
            'B' => 11,
            'C' => 16,
            'D' => 14,
            'E' => 24,
            'F' => 18,
            'G' => 14,
            'H' => 20,
            'I' => 16,
            'J' => 28,

            'K' => 7,
            'L' => 28,
            'M' => 18,
            'N' => 16,
            'O' => 22,
            'P' => 24,
            'Q' => 22,
            'R' => 22,
            'S' => 8,
            'T' => 8,
            'U' => 20,
            'V' => 24,
            'W' => 24,
            'X' => 24,
            'Y' => 24,
            'Z' => 32,
            'AA' => 14,
            'AB' => 14,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)
                ->setWidth($width);
        }

        // Pengaturan cetak.
        $sheet->getPageSetup()
            ->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            );

        $sheet->getPageSetup()
            ->setPaperSize(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3
            );

        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setFitToPage(true);

        $sheet->getPageMargins()->setTop(0.3);
        $sheet->getPageMargins()->setBottom(0.3);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setRight(0.2);

        $sheet->getHeaderFooter()->setOddHeader(
            '&C&BDATA PENDAFTARAN PESERTA'
        );

        $sheet->getHeaderFooter()->setOddFooter(
            '&LPendaftaran Peserta&RHalaman &P dari &N'
        );

        // Menghasilkan file XLSX dalam bentuk string.
        $writer = new Xlsx($spreadsheet);

        ob_start();

        try {
            $writer->save('php://output');
            $excel = ob_get_clean();

            if ($excel === false) {
                throw new RuntimeException(
                    'Gagal membuat file Excel.'
                );
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