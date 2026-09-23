<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockEntryController extends Controller
{
    private array $rules = [
        'supplier_id' => 'nullable|exists:suppliers,id',
        'purchase_id' => 'required_if:reason,Purchase Order|nullable|exists:purchases,id',
        'reason' => 'required|in:Purchase Order,Manual Adjustment,Return from Customer,Initial Stock',
        'reference' => 'nullable|string|max:255',
        'date' => 'required|date',
        'note' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
    ];

    public function index()
    {
        return StockEntry::with(['supplier', 'purchase', 'details.product'])->latest('date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);


        if (($data['reason'] ?? null) === 'Purchase Order') {
            $purchase = Purchase::findOrFail($data['purchase_id']);
            if ($purchase->status === 'Received') {
                abort(422, 'Esta compra ya fue recibida anteriormente.');
            }
        }
        return DB::transaction(function () use ($data) {
            $entry = StockEntry::create([
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_id' => $data['purchase_id'] ?? null,
                'reason' => $data['reason'],
                'reference' => $data['reference'] ?? null,
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $entry->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
            }

            // El recibo de mercancía es lo que marca la orden como recibida —
            // el usuario ya no tiene que ir a cambiarle el status a mano.
            if (!empty($data['purchase_id'])) {
                Purchase::where('id', $data['purchase_id'])->update(['status' => 'Received']);
            }

            return $entry->load('supplier', 'purchase', 'details.product');
        });
    }

    public function show(StockEntry $stockEntry)
    {
        return $stockEntry->load('supplier', 'purchase', 'details.product');
    }

    public function update(Request $request, StockEntry $stockEntry)
    {
        $data = $request->validate($this->rules);

        if (($data['reason'] ?? null) === 'Purchase Order') {
            $purchase = Purchase::findOrFail($data['purchase_id']);
            if ($purchase->status === 'Received') {
                abort(422, 'Esta compra ya fue recibida anteriormente.');
            }
        }

        return DB::transaction(function () use ($data, $stockEntry) {
            foreach ($stockEntry->details as $detail) {
                Product::where('id', $detail->product_id)->decrement('stock', $detail->quantity);
            }

            $stockEntry->details()->delete();

            $stockEntry->update([
                'supplier_id' => $data['supplier_id'] ?? null,
                'purchase_id' => $data['purchase_id'] ?? null,
                'reason' => $data['reason'],
                'reference' => $data['reference'] ?? null,
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $stockEntry->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
                Product::where('id', $item['product_id'])->increment('stock', $item['quantity']);
            }

            if (!empty($data['purchase_id'])) {
                Purchase::where('id', $data['purchase_id'])->update(['status' => 'Received']);
            }

            return $stockEntry->load('supplier', 'purchase', 'details.product');
        });
    }

    public function destroy(StockEntry $stockEntry)
    {
        DB::transaction(function () use ($stockEntry) {
            foreach ($stockEntry->details as $detail) {
                Product::where('id', $detail->product_id)->decrement('stock', $detail->quantity);
            }
            $stockEntry->delete();
        });

        return response()->json(null, 204);
    }
}