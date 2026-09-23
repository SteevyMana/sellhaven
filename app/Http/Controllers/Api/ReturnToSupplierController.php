<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ReturnToSupplier;
use App\Models\ReturnToSupplierDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnToSupplierController extends Controller
{
    private array $rules = [
        'purchase_id' => 'required|exists:purchases,id',
        'status' => 'required|in:Pending,Confirmed',
        'date' => 'required|date',
        'note' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.qty' => 'required|integer|min:1',
        'items.*.reason' => 'required|in:Defective,Wrong Item,Excess Stock,Expired,Other',
    ];

    public function index()
    {
        return ReturnToSupplier::with(['supplier', 'purchase', 'details.product'])->latest('date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $purchase = Purchase::with('details')->findOrFail($data['purchase_id']);

        if ($purchase->status !== 'Received') {
            abort(422, 'Solo se puede devolver mercancía de una compra ya recibida.');
        }

        return DB::transaction(function () use ($data, $purchase) {
            $return = ReturnToSupplier::create([
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'status' => $data['status'],
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
            ]);

            $this->saveDetails($return, $purchase, $data['items']);

            return $return->load('supplier', 'purchase', 'details.product');
        });
    }

    public function show(ReturnToSupplier $returnToSupplier)
    {
        return $returnToSupplier->load('supplier', 'purchase', 'details.product');
    }

    public function update(Request $request, ReturnToSupplier $returnToSupplier)
    {
        if ($returnToSupplier->status === 'Confirmed') {
            abort(422, 'No se puede editar una devolución ya confirmada.');
        }

        $data = $request->validate($this->rules);
        $purchase = Purchase::with('details')->findOrFail($data['purchase_id']);

        if ($purchase->status !== 'Received') {
            abort(422, 'Solo se puede devolver mercancía de una compra ya recibida.');
        }

        return DB::transaction(function () use ($data, $returnToSupplier, $purchase) {
            $returnToSupplier->details()->delete();

            $returnToSupplier->update([
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'status' => $data['status'],
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
            ]);

            $this->saveDetails($returnToSupplier, $purchase, $data['items'], excludeReturnId: $returnToSupplier->id);

            return $returnToSupplier->load('supplier', 'purchase', 'details.product');
        });
    }

    public function destroy(ReturnToSupplier $returnToSupplier)
    {
        if ($returnToSupplier->status === 'Confirmed') {
            abort(422, 'No se puede eliminar una devolución ya confirmada.');
        }

        $returnToSupplier->delete();

        return response()->json(null, 204);
    }

    /**
     * Valida y guarda los items. El tope de cada producto es:
     *   min(cantidad comprada en esa orden − ya devuelto y confirmado de esa orden, stock actual)
     * El stock solo se resta si el status final es "Confirmed".
     */
    private function saveDetails(ReturnToSupplier $return, Purchase $purchase, array $items, ?int $excludeReturnId = null): void
    {
        foreach ($items as $item) {
            $purchasedDetail = $purchase->details->firstWhere('product_id', $item['product_id']);

            if (!$purchasedDetail) {
                abort(422, "El producto seleccionado no pertenece a la compra #{$purchase->id}.");
            }

            $alreadyReturned = ReturnToSupplierDetail::whereHas('returnToSupplier', function ($q) use ($purchase, $excludeReturnId) {
                $q->where('purchase_id', $purchase->id)->where('status', 'Confirmed');
                if ($excludeReturnId) {
                    $q->where('id', '!=', $excludeReturnId);
                }
            })->where('product_id', $item['product_id'])->sum('qty');

            $remainingFromOrder = $purchasedDetail->quantity - $alreadyReturned;

            $product = Product::findOrFail($item['product_id']);
            $maxAllowed = min($remainingFromOrder, $product->stock);

            if ($item['qty'] > $maxAllowed) {
                abort(422, "No se puede devolver {$item['qty']} de \"{$product->name}\". Máximo disponible: {$maxAllowed} (comprado: {$purchasedDetail->quantity}, ya devuelto: {$alreadyReturned}, stock actual: {$product->stock}).");
            }

            $stockBefore = $product->stock;
            $stockAfter = $return->status === 'Confirmed' ? $stockBefore - $item['qty'] : $stockBefore;

            $return->details()->create([
                'product_id' => $item['product_id'],
                'reason' => $item['reason'],
                'qty' => $item['qty'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);

            if ($return->status === 'Confirmed') {
                $product->decrement('stock', $item['qty']);
            }
        }
    }
}