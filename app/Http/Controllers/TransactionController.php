<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['coa.category'])->orderBy('date', 'desc')->orderBy('id', 'desc');

        if ($request->has('start_date') && $request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->has('coa_id') && $request->coa_id) {
            $query->where('coa_id', $request->coa_id);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('coa', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        // Summary calculations over all filtered data before pagination slice
        $summaryQuery = clone $query;
        $totalDebit = (float) (clone $summaryQuery)->sum('debit');
        $totalCredit = (float) (clone $summaryQuery)->sum('credit');
        $totalNet = $totalCredit - $totalDebit;

        $perPage = $request->input('per_page', 10);

        if ($perPage === 'all' || (int) $perPage === -1) {
            $transactions = $query->get();
            return response()->json([
                'data' => $transactions,
                'total' => $transactions->count(),
                'per_page' => $transactions->count(),
                'current_page' => 1,
                'last_page' => 1,
                'from' => $transactions->isEmpty() ? 0 : 1,
                'to' => $transactions->count(),
                'summary' => [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'net' => $totalNet,
                ]
            ]);
        }

        $paginated = $query->paginate((int) $perPage);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'from' => $paginated->firstItem() ?? 0,
            'to' => $paginated->lastItem() ?? 0,
            'summary' => [
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'net' => $totalNet,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'coa_id' => 'required|exists:coas,id',
            'description' => 'nullable|string|max:500',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        $validated['debit'] = $validated['debit'] ?? 0;
        $validated['credit'] = $validated['credit'] ?? 0;

        $transaction = Transaction::create($validated);
        return response()->json($transaction->load('coa.category'), 201);
    }

    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load('coa.category'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'coa_id' => 'required|exists:coas,id',
            'description' => 'nullable|string|max:500',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        $validated['debit'] = $validated['debit'] ?? 0;
        $validated['credit'] = $validated['credit'] ?? 0;

        $transaction->update($validated);
        return response()->json($transaction->load('coa.category'));
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
