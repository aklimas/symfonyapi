<?php

namespace App\State;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CountryRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        // TODO Implement DTO

        /*if($operation instanceof GetCollection){
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->countryRepository->findAll();
            } else {
                return $this->countryRepository->findBy(['verified' => true]);
            }
        }*/

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'ID');
        $activeWorksheet->setCellValue('B2', 'Typ');
        $activeWorksheet->setCellValue('C3', 'Znacznik');


        // Generate a unique filename for the spreadsheet
        $filename = 'example_spreadsheet_' . date('YmdHis') . '.xlsx';
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;


        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Create the response with the generated Excel file
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        return $response;

    }
}
