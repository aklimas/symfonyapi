<?php

namespace App\State\Country\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\CountryRepository;
use App\Trait\SpreadsheetStyle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CountryCollectionExportToExcel extends AbstractController implements ProviderInterface
{
    use SpreadsheetStyle;

    public function __construct(
        private readonly CountryRepository $countryRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (!$operation instanceof GetCollection) {
            return $this->json([
                'code' => 404,
                'message' => 'Not found',
            ]);
        }

        $countryCollection = $this->getCollection();
        if (!$countryCollection) {
            return $this->json([
                'code' => 200,
                'message' => 'Collection is empty',
            ]);
        }

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $this->setHeadSpreadsheet($spreadsheet);
        $this->setBodySpreadsheet($countryCollection, $activeWorksheet);

        list($filename, $filePath) = $this->generateAUniqueFilenameForTheSpreadsheet();

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $this->getBinaryFileResponse($filePath, $filename);
    }

    protected function getBinaryFileResponse(string $filePath, string $filename): BinaryFileResponse
    {
        // Create the response with the generated Excel file
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);

        return $response;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function setHeadSpreadsheet(Spreadsheet $spreadsheet): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        $styleHeaderArray = $this->getStyleHeaderArray();
        $activeWorksheet = $spreadsheet->getActiveSheet()->setPrintGridlines(true);
        $activeWorksheet->setCellValue('A1', 'Country');
        $activeWorksheet->setCellValue('B1', 'Count User');
        $activeWorksheet->getStyle('A1:B1')->applyFromArray($styleHeaderArray);

        return $activeWorksheet;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function setBodySpreadsheet(array $countryCollection, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $activeWorksheet): void
    {
        $i = 2;
        foreach ($countryCollection as $country) {
            $activeWorksheet->setCellValue('A'.$i, $country->getName());
            $activeWorksheet->setCellValue('B'.$i, $country->getCountUser());
            ++$i;
        }
        $activeWorksheet->getStyle('A1:B'.$i)->applyFromArray($this->getStyleBodyArray());
    }

    /**
     * @return \App\Entity\Country[]|array|object[]
     */
    protected function getCollection(): array
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $countryCollection = $this->countryRepository->findAll();
        } else {
            $countryCollection = $this->countryRepository->findBy(['verified' => true]);
        }

        return $countryCollection;
    }

    /**
     * @return string[]
     */
    protected function generateAUniqueFilenameForTheSpreadsheet(): array
    {
        // Generate a unique filename for the spreadsheet
        $filename = 'countries_'.date('YmdHis').'.xlsx';
        $filePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

        return [$filename, $filePath];
    }
}
