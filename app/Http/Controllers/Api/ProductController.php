<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('category')->get();
    }

    public function store(Request $request)
    {
        // Al crear, "stock" sí se acepta: es el punto de partida (ej. "Initial Stock").
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        return Product::create($data)->load('category');
    }

    public function show(Product $product)
    {
        return $product->load('category');
    }

    public function update(Request $request, Product $product)
    {
        // "stock" ya NO se acepta aquí. Solo cambia vía Stock Entries.
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product->update($data);

        return $product->load('category');
    }

    public function destroy(Product $product)
    {
        $hasHistory = $product->purchaseDetails()->exists() 
        || $product->stockEntryDetails()->exists()
        || $product->returnToSupplierDetails()->exists()
        || $product->customerReturnDetails()->exists();

        if ($hasHistory) {
            abort(422, 'you cannot delete a product that has purchase or stock entry history.');
        }

        $product->delete();

        return response()->json(null, 204);
    }
}