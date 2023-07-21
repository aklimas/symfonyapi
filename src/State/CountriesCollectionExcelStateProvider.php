<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CountryRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CountriesCollectionExcelStateProvider extends AbstractController implements ProviderInterface
{
    public function __construct(
        private readonly CountryRepository $countryRepository
    )
    {
    }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        $styleHeaderArray = $this->getStyleHeaderArray();
        $styleBodyArray = $this->getStyleBodyArray();

        if(!$operation instanceof GetCollection){
            return [];
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $countryCollection =  $this->countryRepository->findAll();
        } else {
            $countryCollection =  $this->countryRepository->findBy(['verified' => true]);
        }

        if(!$countryCollection){
            return [];
        }

        $spreadsheet = new Spreadsheet();

        $activeWorksheet = $spreadsheet->getActiveSheet()->setPrintGridlines(true);
        $activeWorksheet->setCellValue('A1', 'Country');
        $activeWorksheet->setCellValue('B1', 'Count User');
        $activeWorksheet->getStyle('A1:B1')->applyFromArray($styleHeaderArray);

        $i = 2;
        foreach ($countryCollection as $country){
            $activeWorksheet->setCellValue('A'.$i, $country->getName());
            $activeWorksheet->setCellValue('B'.$i, $country->getCountUser());
            $i++;
        }

        $activeWorksheet->getStyle('A1:B'.$i)->applyFromArray($styleBodyArray);


        for ($i = 'A'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
            $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }

        // Generate a unique filename for the spreadsheet
        $filename = 'countries_' . date('YmdHis') . '.xlsx';
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $this->getBinaryFileResponse($filePath, $filename);
    }

    /**
     * @return array
     */
    protected function getStyleHeaderArray(): array
    {
        $styleHeaderArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
            ],
        ];
        return $styleHeaderArray;
    }

    /**
     * @return array
     */
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

    /**
     * @param string $filePath
     * @param string $filename
     * @return BinaryFileResponse
     */
    protected function getBinaryFileResponse(string $filePath, string $filename): BinaryFileResponse
    {
        // Create the response with the generated Excel file
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        return $response;
    }
}
