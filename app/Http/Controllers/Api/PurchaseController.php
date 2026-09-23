<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    private array $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'status' => 'required|in:Pending,Ordered,Cancelled', // Received no es válido aquí
        'date' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.cost' => 'required|numeric|min:0',
    ];

    public function index()
    {
        return Purchase::with(['supplier', 'details.product'])->latest('date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);

        $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['cost']);

        $purchase = Purchase::create([
            'supplier_id' => $data['supplier_id'],
            'status' => $data['status'],
            'date' => $data['date'],
            'total' => $total,
        ]);

        foreach ($data['items'] as $item) {
            $purchase->details()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);
        }

        return $purchase->load('supplier', 'details.product');
    }

    public function show(Purchase $purchase)
    {
        return $purchase->load('supplier', 'details.product');
    }

    public function update(Request $request, Purchase $purchase)
    {

        if ($purchase->status === 'Received') {
            abort(422, 'You cannot edit a purchase that has already been received. Use a stock adjustment to correct it.');
        }
        $data = $request->validate($this->rules);

        $purchase->details()->delete();

        $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['cost']);

        $purchase->update([
            'supplier_id' => $data['supplier_id'],
            'status' => $data['status'],
            'date' => $data['date'],
            'total' => $total,
        ]);

        foreach ($data['items'] as $item) {
            $purchase->details()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'cost' => $item['cost'],
            ]);
        }

        return $purchase->load('supplier', 'details.product');
    }

    public function destroy(Purchase $purchase)
    {

        if ($purchase->status === 'Received') {
            abort(422, 'You cannot delete a purchase that has already been received.');
        }
        $purchase->delete();

        return response()->json(null, 204);
    }
}