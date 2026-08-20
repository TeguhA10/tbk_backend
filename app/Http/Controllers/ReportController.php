<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ProfitLossExport;
use Maatwebsite\Excel\Facades\Excel;

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

        $filename = 'Laporan_Profit_Loss_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new ProfitLossExport($data),
            $filename
        );
    }
}
