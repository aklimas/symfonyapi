<?php

namespace App\State\User\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserPdfStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProviderInterface $itemProvider,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {


        $user = $this->itemProvider->provide($operation, $uriVariables, $context);

// Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $styleBodyArray = $this->getStyleBodyArray();
// Set document properties
        $spreadsheet->getProperties()->setCreator('Maarten Balliauw')
            ->setLastModifiedBy('Maarten Balliauw')
            ->setTitle('PDF Test Document')
            ->setSubject('PDF Test Document')
            ->setDescription('Test document for PDF, generated using PHP classes.')
            ->setKeywords('pdf php')
            ->setCategory('Test result file');

// Add some data
        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A1', 'First name')
            ->setCellValue('B1', $user->getFirstName())
            ->setCellValue('A2', 'Last name')
            ->setCellValue('B2', $user->getLastName())
            ->setCellValue('A3', 'Login')
            ->setCellValue('B3', $user->getLogin())
            ->setCellValue('A4', 'Email')
            ->setCellValue('B4', $user->getEmail())
            ->setCellValue('A5', 'Date Birthday')
            ->setCellValue('B5', $user->getDateBirthday() ? $user->getDateBirthday()->format('Y-m-d') : '-');




// Rename worksheet
        $activeWorksheet = $spreadsheet->getActiveSheet()->setTitle('Simple');
        $spreadsheet->getActiveSheet()->setShowGridLines(true);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        $activeWorksheet->getStyle('A1:B5')->applyFromArray($styleBodyArray);

        // Generate a unique filename for the spreadsheet
        $filename = 'example_spreadsheet_' . date('YmdHis') . '.pdf';
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $writer = new Mpdf($spreadsheet);
        $writer->save($filePath);

        // Create the response with the generated Excel file
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;

    }

    protected function getStyleBodyArray(): array
    {
        $styleBodyArray = [
            'font' => [
                'bold' => false,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ],
        ];
        return $styleBodyArray;
    }

}
