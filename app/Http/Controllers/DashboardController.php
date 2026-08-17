<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coa;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalTransactionsCount = Transaction::count();
        $totalCategoriesCount = Category::count();
        $totalCoasCount = Coa::count();

        // Calculate all-time Income and Expense
        $incomeTxSum = Transaction::whereHas('coa.category', function ($q) {
            $q->where('type', 'income');
        })->sum(DB::raw('credit - debit'));

        $expenseTxSum = Transaction::whereHas('coa.category', function ($q) {
            $q->where('type', 'expense');
        })->sum(DB::raw('debit - credit'));

        $netIncome = $incomeTxSum - $expenseTxSum;

        // Recent 5 transactions
        $recentTransactions = Transaction::with(['coa.category'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Monthly stats for charts
        $months = Transaction::select(DB::raw("TO_CHAR(date, 'YYYY-MM') as month"))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('month')
            ->toArray();

        if (empty($months)) {
            $months = ['2022-01', '2022-02', '2022-03'];
        }

        $monthlyChartData = [];
        foreach ($months as $m) {
            $inc = Transaction::whereHas('coa.category', function ($q) {
                $q->where('type', 'income');
            })->where(DB::raw("TO_CHAR(date, 'YYYY-MM')"), $m)->sum(DB::raw('credit - debit'));

            $exp = Transaction::whereHas('coa.category', function ($q) {
                $q->where('type', 'expense');
            })->where(DB::raw("TO_CHAR(date, 'YYYY-MM')"), $m)->sum(DB::raw('debit - credit'));

            $monthlyChartData[] = [
                'month' => $m,
                'income' => floatval($inc),
                'expense' => floatval($exp),
                'net' => floatval($inc - $exp),
            ];
        }

        // Category breakdown for chart
        $categories = Category::all();
        $categoryBreakdown = [];
        foreach ($categories as $cat) {
            $sum = Transaction::whereHas('coa', function ($q) use ($cat) {
                $q->where('category_id', $cat->id);
            })->get()->sum(function ($tx) use ($cat) {
                return $cat->type === 'income' ? ($tx->credit - $tx->debit) : ($tx->debit - $tx->credit);
            });

            $categoryBreakdown[] = [
                'name' => $cat->name,
                'type' => $cat->type,
                'total' => floatval($sum),
            ];
        }

        return response()->json([
            'counts' => [
                'transactions' => $totalTransactionsCount,
                'categories' => $totalCategoriesCount,
                'coas' => $totalCoasCount,
            ],
            'summary' => [
                'total_income' => floatval($incomeTxSum),
                'total_expense' => floatval($expenseTxSum),
                'net_income' => floatval($netIncome),
            ],
            'recent_transactions' => $recentTransactions,
            'monthly_chart' => $monthlyChartData,
            'category_breakdown' => $categoryBreakdown,
        ]);
    }
}
