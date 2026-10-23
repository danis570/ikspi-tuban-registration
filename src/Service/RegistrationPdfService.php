<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Repository\AdministrativeAreaRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use RuntimeException;
use Throwable;

class RegistrationPdfService
{
    private string $publicDirectory;
    private string $uploadDirectory;
    private string $viewDirectory;

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

        $this->viewDirectory =
            $srcDirectory
            . DIRECTORY_SEPARATOR
            . 'View'
            . DIRECTORY_SEPARATOR
            . 'Generate';
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
                'Tidak ada peserta untuk dibuatkan PDF.'
            );
        }

        $html = $this->buildHtml($registrations);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', $this->publicDirectory);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }


    private function buildHtml(array $registrations): string
    {
        $content = '';

        // Panggil DB Connection
        $pdo = Database::getConnection();

        $administrativeAreaRepository = new AdministrativeAreaRepository($pdo);

        $mappedData = [];

        foreach ($registrations as $item) {
            $registration = $item['registration'];
            $period = $item['period'];

            $areaNames = [
                'province' => $administrativeAreaRepository->findByCode($registration->provinceCode)?->name ?? $registration->provinceCode,
                'regency' => $administrativeAreaRepository->findByCode($registration->regencyCode)?->name ?? $registration->regencyCode,
                'district' => $administrativeAreaRepository->findByCode($registration->districtCode)?->name ?? $registration->districtCode,
                'village' => $administrativeAreaRepository->findByCode($registration->villageCode)?->name ?? $registration->villageCode,
            ];

            $alamatLengkap = "Desa/Kel. " . ($areaNames['village'] ?? '-') . " RT " . ($registration->rt ?? '-') . " RW " . ($registration->rw ?? '-') . ", Kec. " . ($areaNames['district'] ?? '-') . ", Kab. " . ($areaNames['regency'] ?? '-') . ", Prov. " . ($areaNames['province'] ?? '-');

            // --- AMBIL EMAIL DARI DATABASE BERDASARKAN userId ---
            $asalRanting = '-';
            try {
                $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $registration->userId]);
                $userEmail = $stmt->fetchColumn();

                if ($userEmail && str_contains($userEmail, '@')) {
                    // 1. Ambil teks sebelum '@'
                    $namaEmail = explode('@', $userEmail)[0];

                    // 2. Hapus kata "kecamatan" atau "kec." (tidak peka huruf besar/kecil)
                    $namaEmail = str_ireplace(['kecamatan', 'kec.', 'kec'], '', $namaEmail);

                    // 3. Ganti titik/underscore dengan spasi (jika ada)
                    $namaEmail = str_replace(['.', '_'], ' ', $namaEmail);

                    // 4. Bersihkan spasi berlebih di awal/akhir
                    $namaEmail = trim($namaEmail);

                    // 5. Buat jadi Title Case (Huruf Depan Kapital) -> "Palang"
                    $asalRanting = ucwords($namaEmail);
                }
            } catch (\Exception $e) {
                $asalRanting = '-';
            }
            // -----------------------------------------------------

            $smallPhotoPath = $this->getPhotoPath($registration->smallPhoto);
            $largePhotoPath = $this->getPhotoPath($registration->largePhoto);

            $mappedData[] = [
                'registration' => $registration,
                'period' => $period,
                'areaNames' => $areaNames,
                'smallPhotoPath' => $smallPhotoPath,
                'largePhotoPath' => $largePhotoPath,
                'alamatLengkap' => $alamatLengkap,
                'asalRanting' => $asalRanting, // <--- Dikirim ke view
            ];
        }

        // 1. Render Halaman 1 (Tabel Rekap)
        $content = $this->renderTemplate('pdf-data.php', [
            'registrations' => $mappedData,
        ]);

        // 2. Render Halaman 2+ (Grid Foto 2x2)
        $content .= '<div class="page-break"></div>';

        $totalData = count($mappedData);
        foreach ($mappedData as $index => $data) {

            // Tentukan nomor urut
            $noUrut = $index + 1;

            // Jika index adalah 0, 4, 8, ... (awal halaman baru), buat tabel baru
            if ($index % 4 === 0) {
                $content .= '<table class="grid-table">';
            }

            // Jika index adalah 0, 2, 4, 6, ... (awal baris baru), buat <tr> baru
            if ($index % 2 === 0) {
                $content .= '<tr>';
            }

            // Render kartu di dalam <td>
            $content .= '<td class="grid-cell">';

            // TAMBAHKAN 'noUrut' KE DALAM DATA YANG DIKIRIM
            $content .= $this->renderTemplate('pdf-photos.php', array_merge($data, [
                'noUrut' => $noUrut
            ]));

            $content .= '</td>';

            // Jika index adalah 1, 3, 5, 7, ... (akhir baris), tutup </tr>
            if ($index % 2 === 1 || $index === $totalData - 1) {
                $content .= '</tr>';
            }

            // Jika index adalah 3, 7, 11, ... (akhir halaman) DAN bukan data terakhir, tutup tabel dan buat halaman baru
            if (($index + 1) % 4 === 0 && ($index + 1) < $totalData) {
                $content .= '</table>';
                $content .= '<div class="page-break"></div>';
            }

            // Jika ini data terakhir, tutup tabel
            if ($index === $totalData - 1) {
                $content .= '</table>';
            }
        }

        return $this->renderTemplate('pdf-layout.php', [
            'content' => $content,
        ]);
    }


    private function renderTemplate(
        string $fileName,
        array $data
    ): string {
        $path = $this->viewDirectory
            . DIRECTORY_SEPARATOR
            . $fileName;

        if (!is_file($path)) {
            throw new RuntimeException(
                "Template PDF tidak ditemukan: {$fileName}"
            );
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $path;

            return ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
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
