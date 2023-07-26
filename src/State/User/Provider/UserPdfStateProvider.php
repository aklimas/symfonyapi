<?php

namespace App\State\User\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Trait\SpreadsheetStyle;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserPdfStateProvider implements ProviderInterface
{
    use SpreadsheetStyle;

    public function __construct(
        private readonly ProviderInterface $itemProvider,
    ) {
    }

    /**
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var User $user */
        $user = $this->itemProvider->provide($operation, $uriVariables, $context);

        $spreadsheet = new Spreadsheet();
        $styleBodyArray = $this->getStyleBodyArray();

        $this->setHeaderPdf($spreadsheet);
        $this->addValueToPdf($spreadsheet, $user);

        $activeWorksheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getActiveSheet();

        $spreadsheet->setActiveSheetIndex(0);

        $activeWorksheet->getStyle('A1:B5')->applyFromArray($styleBodyArray);

        $filename = 'user_'.date('YmdHis').'.pdf';
        $filePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

        $writer = new Mpdf($spreadsheet);
        $writer->save($filePath);

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);

        return $response;
    }

    private function setHeaderPdf(Spreadsheet $spreadsheet): void
    {
        $spreadsheet->getProperties()
            ->setCreator('Adam Klimas')
            ->setLastModifiedBy('Adam Klimas')
            ->setTitle('PDF Test Document')
            ->setSubject('PDF Test Document')
            ->setDescription('Test document for PDF, generated using PHP classes.')
            ->setKeywords('pdf php')
            ->setCategory('Test result file');
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addValueToPdf(Spreadsheet $spreadsheet, User $user): void
    {
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
    }
}
