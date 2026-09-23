<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        return Supplier::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:suppliers,email',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'status' => 'in:Active,Inactive',
        ]);

        return Supplier::create($data);
    }

    public function show(Supplier $supplier)
    {
        return $supplier;
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('suppliers', 'email')->ignore($supplier->id)],
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'status' => 'in:Active,Inactive',
        ]);

        $supplier->update($data);

        return $supplier;
    }

    public function destroy(Supplier $supplier)
    {
        $hasHistory = $supplier->purchases()->exists() 
        || $supplier->stockEntries()->exists()
        || $supplier->returnToSupplierDetails()->exists();

        if ($hasHistory) {
            abort(422, 'you cannot delete a supplier that has purchase or stock entry history.');
        }

        $supplier->delete();

        return response()->json(null, 204);
    }
}