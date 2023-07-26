<?php

namespace App\Trait;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

trait SpreadsheetStyle
{
    protected function getStyleHeaderArray(): array
    {
        return [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THICK,
                ],
                'right' => [
                    'borderStyle' => Border::BORDER_THICK,
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_THICK,
                ],
                'left' => [
                    'borderStyle' => Border::BORDER_THICK,
                ],
            ],
        ];
    }

    protected function getStyleBodyArray(): array
    {
        return [
            'font' => [
                'bold' => false,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
                'right' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
                'left' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                ],
            ],
        ];
    }
}
