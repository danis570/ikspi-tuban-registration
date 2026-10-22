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

        $administrativeAreaRepository = new AdministrativeAreaRepository(
            Database::getConnection()
        );

        foreach ($registrations as $item) {
            $registration = $item['registration'];
            $period = $item['period'];

            $areaNames = [
                'province' => $administrativeAreaRepository
                    ->findByCode($registration->provinceCode)?->name
                    ?? $registration->provinceCode,

                'regency' => $administrativeAreaRepository
                    ->findByCode($registration->regencyCode)?->name
                    ?? $registration->regencyCode,

                'district' => $administrativeAreaRepository
                    ->findByCode($registration->districtCode)?->name
                    ?? $registration->districtCode,

                'village' => $administrativeAreaRepository
                    ->findByCode($registration->villageCode)?->name
                    ?? $registration->villageCode,
            ];

            $content .= $this->renderTemplate('pdf-data.php', [
                'registration' => $registration,
                'period' => $period,
                'areaNames' => $areaNames,
            ]);

            $content .= $this->renderTemplate('pdf-photos.php', [
                'registration' => $registration,
                'smallPhotoPath' => $this->getPhotoPath(
                    $registration->smallPhoto
                ),
                'largePhotoPath' => $this->getPhotoPath(
                    $registration->largePhoto
                ),
            ]);
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
