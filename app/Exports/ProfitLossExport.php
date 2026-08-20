<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements
    FromArray,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithStrictNullComparison
{
    protected array $data;
    protected array $months;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->months = $data['months'] ?? [];
    }

    /**
     * Pastikan nilai yang masuk ke cell selalu numerik.
     * Menangani kasus null, string kosong (''), maupun value non-numerik
     * yang tidak tertangkap oleh operator null coalescing (??).
     */
    private function toNumber(mixed $value): int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return 0;
        }

        return $value;
    }

    public function array(): array
    {
        $rows = [];

        /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

        $rows[] = ['PT. TRANS BERJAYA KHATULISTIWA'];
        $rows[] = ['LAPORAN PROFIT / LOSS (LABA RUGI)'];

        $periodStart = $this->months[0] ?? '-';
        $periodEnd = !empty($this->months)
            ? end($this->months)
            : '-';

        $rows[] = [
            'Periode',
            "{$periodStart} s/d {$periodEnd}"
        ];

        $rows[] = [null];

        /*
    |--------------------------------------------------------------------------
    | Table Header
    |--------------------------------------------------------------------------
    */

        $rows[] = array_merge(
            ['Category'],
            $this->months
        );

        /*
    |--------------------------------------------------------------------------
    | INCOME
    |--------------------------------------------------------------------------
    */

        $rows[] = ['INCOME'];

        foreach ($this->data['income_categories'] ?? [] as $category) {

            $row = [$category['name']];

            foreach ($this->months as $month) {

                // toNumber() menangani null, string kosong, dan non-numerik -> 0
                $row[] = $this->toNumber($category['amounts'][$month] ?? null);
            }

            $rows[] = $row;
        }

        /*
    |--------------------------------------------------------------------------
    | TOTAL INCOME
    |--------------------------------------------------------------------------
    */

        $totalIncomeRow = ['TOTAL INCOME'];

        foreach ($this->months as $month) {

            $totalIncomeRow[] =
                $this->toNumber($this->data['total_income'][$month] ?? null);
        }

        $rows[] = $totalIncomeRow;

        $rows[] = [null];

        /*
    |--------------------------------------------------------------------------
    | EXPENSE
    |--------------------------------------------------------------------------
    */

        $rows[] = ['EXPENSE'];

        foreach ($this->data['expense_categories'] ?? [] as $category) {

            $row = [$category['name']];

            foreach ($this->months as $month) {

                // toNumber() menangani null, string kosong, dan non-numerik -> 0
                $row[] = $this->toNumber($category['amounts'][$month] ?? null);
            }

            $rows[] = $row;
        }

        /*
    |--------------------------------------------------------------------------
    | TOTAL EXPENSE
    |--------------------------------------------------------------------------
    */

        $totalExpenseRow = ['TOTAL EXPENSE'];

        foreach ($this->months as $month) {

            $totalExpenseRow[] =
                $this->toNumber($this->data['total_expense'][$month] ?? null);
        }

        $rows[] = $totalExpenseRow;

        $rows[] = [null];

        /*
    |--------------------------------------------------------------------------
    | NET INCOME
    |--------------------------------------------------------------------------
    */

        $netIncomeRow = ['NET INCOME'];

        foreach ($this->months as $month) {

            $netIncomeRow[] =
                $this->toNumber($this->data['net_income'][$month] ?? null);
        }

        $rows[] = $netIncomeRow;

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getSheetView()->setShowZeros(true);
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        /*
        |--------------------------------------------------------------------------
        | Company Name
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells("A1:{$lastColumn}1");

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Report Title
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells("A2:{$lastColumn}2");

        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle('A3')->getFont()->setBold(true);

        /*
        |--------------------------------------------------------------------------
        | Table Header
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => 'solid',
                'color' => [
                    'rgb' => 'D9EAF7',
                ],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                ],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Number Format
        |--------------------------------------------------------------------------
        */

        if ($lastColumn !== 'A' && $lastRow >= 6) {
            $sheet
                ->getStyle("B6:{$lastColumn}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        /*
        |--------------------------------------------------------------------------
        | Section & Total Styling
        |--------------------------------------------------------------------------
        */

        for ($row = 6; $row <= $lastRow; $row++) {

            $label = $sheet->getCell("A{$row}")->getValue();

            // Income / Expense
            if (in_array($label, ['INCOME', 'EXPENSE'], true)) {

                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'color' => [
                                'rgb' => 'E8E8E8',
                            ],
                        ],
                        'alignment' => [
                            'vertical' => 'center',
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => 'thin',
                            ],
                            'bottom' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);
            }

            // Total Income / Expense
            if (
                $label === 'TOTAL INCOME' ||
                $label === 'TOTAL EXPENSE'
            ) {

                $sheet
                    ->getStyle("A{$row}:{$lastColumn}{$row}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => 'thin',
                            ],
                            'bottom' => [
                                'borderStyle' => 'thin',
                            ],
                        ],
                    ]);
            }

            // Net Income
            if ($label === 'NET INCOME') {

                $sheet
                    ->getStyle("A{$row}:{$lastColumn}{$row}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'color' => [
                                'rgb' => 'DFF0D8',
                            ],
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => 'medium',
                            ],
                            'bottom' => [
                                'borderStyle' => 'medium',
                            ],
                        ],
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Borders
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle("A5:{$lastColumn}{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        /*
        |--------------------------------------------------------------------------
        | Alignment
        |--------------------------------------------------------------------------
        */

        if ($lastColumn !== 'A') {

            $sheet
                ->getStyle("B6:{$lastColumn}{$lastRow}")
                ->getAlignment()
                ->setHorizontal('right');
        }

        /*
        |--------------------------------------------------------------------------
        | Freeze Header
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane('B6');

        /*
        |--------------------------------------------------------------------------
        | Row Height
        |--------------------------------------------------------------------------
        */

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);

        return [];
    }

    public function title(): string
    {
        return 'Profit Loss';
    }
}
