<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use Illuminate\Http\Request;

class CoaController extends Controller
{
    public function index()
    {
        $coas = Coa::with('category')->orderBy('code', 'asc')->get();
        return response()->json($coas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coas,code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        $coa = Coa::create($validated);
        return response()->json($coa->load('category'), 201);
    }

    public function show(Coa $coa)
    {
        return response()->json($coa->load(['category', 'transactions']));
    }

    public function update(Request $request, Coa $coa)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coas,code,' . $coa->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        $coa->update($validated);
        return response()->json($coa->load('category'));
    }

    public function destroy(Coa $coa)
    {
        $coa->delete();
        return response()->json(['message' => 'COA deleted successfully']);
    }
}
