<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        // Default months if not specified
        $months = Transaction::select(DB::raw("TO_CHAR(date, 'YYYY-MM') as month"))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('month')
            ->toArray();

        if (empty($months)) {
            $months = ['2022-01', '2022-02', '2022-03'];
        }

        $categories = Category::orderBy('type', 'desc')->orderBy('id', 'asc')->get();

        $incomeCategoriesData = [];
        $expenseCategoriesData = [];

        $totalIncomeMonthly = array_fill_keys($months, 0);
        $totalExpenseMonthly = array_fill_keys($months, 0);

        foreach ($categories as $cat) {
            $catMonthlyData = ['id' => $cat->id, 'name' => $cat->name, 'type' => $cat->type, 'amounts' => []];

            foreach ($months as $m) {
                // Fetch transactions for this category and month
                $amount = Transaction::whereHas('coa', function ($q) use ($cat) {
                    $q->where('category_id', $cat->id);
                })
                ->where(DB::raw("TO_CHAR(date, 'YYYY-MM')"), $m)
                ->get()
                ->sum(function ($tx) use ($cat) {
                    return $cat->type === 'income' ? ($tx->credit - $tx->debit) : ($tx->debit - $tx->credit);
                });

                $catMonthlyData['amounts'][$m] = floatval($amount);

                if ($cat->type === 'income') {
                    $totalIncomeMonthly[$m] += floatval($amount);
                } else {
                    $totalExpenseMonthly[$m] += floatval($amount);
                }
            }

            if ($cat->type === 'income') {
                $incomeCategoriesData[] = $catMonthlyData;
            } else {
                $expenseCategoriesData[] = $catMonthlyData;
            }
        }

        $netIncomeMonthly = [];
        foreach ($months as $m) {
            $netIncomeMonthly[$m] = $totalIncomeMonthly[$m] - $totalExpenseMonthly[$m];
        }

        return response()->json([
            'months' => $months,
            'income_categories' => $incomeCategoriesData,
            'total_income' => $totalIncomeMonthly,
            'expense_categories' => $expenseCategoriesData,
            'total_expense' => $totalExpenseMonthly,
            'net_income' => $netIncomeMonthly,
        ]);
    }

    public function exportProfitLoss(Request $request)
    {
        $reportResponse = $this->profitLoss($request);
        $data = $reportResponse->getData(true);

        $months = $data['months'];
        
        $filename = 'Laporan_Profit_Loss_' . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($data, $months) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Title
            fputcsv($file, ['PT. TRANS BERJAYA KHATULISTIWA']);
            fputcsv($file, ['LAPORAN PROFIT / LOSS (LABA RUGI)']);
            fputcsv($file, ['Periode: ' . implode(' s/d ', [$months[0] ?? '', end($months) ?? ''])]);
            fputcsv($file, []);

            // Table Columns
            $headerRow = array_merge(['Category'], $months);
            fputcsv($file, $headerRow);

            // Income Section
            fputcsv($file, ['--- INCOME ---']);
            foreach ($data['income_categories'] as $incCat) {
                $row = [$incCat['name']];
                foreach ($months as $m) {
                    $row[] = number_format($incCat['amounts'][$m] ?? 0, 0, ',', '.');
                }
                fputcsv($file, $row);
            }

            // Total Income
            $totalIncRow = ['Total Income'];
            foreach ($months as $m) {
                $totalIncRow[] = number_format($data['total_income'][$m] ?? 0, 0, ',', '.');
            }
            fputcsv($file, $totalIncRow);
            fputcsv($file, []);

            // Expense Section
            fputcsv($file, ['--- EXPENSE ---']);
            foreach ($data['expense_categories'] as $expCat) {
                $row = [$expCat['name']];
                foreach ($months as $m) {
                    $row[] = number_format($expCat['amounts'][$m] ?? 0, 0, ',', '.');
                }
                fputcsv($file, $row);
            }

            // Total Expense
            $totalExpRow = ['Total Expense'];
            foreach ($months as $m) {
                $totalExpRow[] = number_format($data['total_expense'][$m] ?? 0, 0, ',', '.');
            }
            fputcsv($file, $totalExpRow);
            fputcsv($file, []);

            // Net Income Row
            $netIncRow = ['Net Income'];
            foreach ($months as $m) {
                $netIncRow[] = number_format($data['net_income'][$m] ?? 0, 0, ',', '.');
            }
            fputcsv($file, $netIncRow);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
