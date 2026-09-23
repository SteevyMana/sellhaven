<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReturnController extends Controller
{
    private array $rules = [
        'customer_id' => 'required|exists:customers,id',
        'sale_id' => 'nullable|exists:sales,id|prohibits:order_id',
        'order_id' => 'nullable|exists:orders,id|prohibits:sale_id',
        'status' => 'required|in:Pending,Approved,Rejected',
        'date' => 'required|date',
        'note' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.qty' => 'required|integer|min:1',
        'items.*.reason' => 'required|in:Defective,Wrong Item,Changed Mind,Damaged in Shipping,Other',
    ];

    public function index()
    {
        return CustomerReturn::with(['customer', 'sale', 'order', 'details.product'])->latest('date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $this->assertLinkedSourceValid($data);

        return DB::transaction(function () use ($data) {
            $return = CustomerReturn::create([
                'customer_id' => $data['customer_id'],
                'sale_id' => $data['sale_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'status' => $data['status'],
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
            ]);

            $this->saveDetails($return, $data['items']);

            return $return->load('customer', 'sale', 'order', 'details.product');
        });
    }

    public function show(CustomerReturn $customerReturn)
    {
        return $customerReturn->load('customer', 'sale', 'order', 'details.product');
    }

    public function update(Request $request, CustomerReturn $customerReturn)
    {
        if ($customerReturn->status !== 'Pending') {
            abort(422, 'No se puede editar una devolución ya aprobada o rechazada.');
        }

        $data = $request->validate($this->rules);
        $this->assertLinkedSourceValid($data);

        return DB::transaction(function () use ($data, $customerReturn) {
            $customerReturn->details()->delete();

            $customerReturn->update([
                'customer_id' => $data['customer_id'],
                'sale_id' => $data['sale_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'status' => $data['status'],
                'date' => $data['date'],
                'note' => $data['note'] ?? null,
            ]);

            $this->saveDetails($customerReturn, $data['items'], excludeReturnId: $customerReturn->id);

            return $customerReturn->load('customer', 'sale', 'order', 'details.product');
        });
    }

    public function destroy(CustomerReturn $customerReturn)
    {
        if ($customerReturn->status !== 'Pending') {
            abort(422, 'No se puede eliminar una devolución ya aprobada o rechazada.');
        }

        $customerReturn->delete();

        return response()->json(null, 204);
    }

    private function assertLinkedSourceValid(array $data): void
    {
        if (!empty($data['sale_id']) && Sale::find($data['sale_id'])->status !== 'Completed') {
            abort(422, 'Solo se puede vincular a una venta ya completada.');
        }
        if (!empty($data['order_id']) && Order::find($data['order_id'])->status !== 'Delivered') {
            abort(422, 'Solo se puede vincular a un pedido ya entregado.');
        }
    }

    /**
     * Si viene vinculado a Sale/Order, topa cada producto a lo vendido
     * en esa línea menos lo ya devuelto y aprobado de la misma. El stock
     * solo se suma si el status final es "Approved".
     */
    private function saveDetails(CustomerReturn $return, array $items, ?int $excludeReturnId = null): void
    {
        foreach ($items as $item) {
            if ($return->sale_id || $return->order_id) {
                $column = $return->sale_id ? 'sale_id' : 'order_id';
                $sourceId = $return->sale_id ?? $return->order_id;

                $source = $return->sale_id ? Sale::with('details')->find($sourceId) : Order::with('details')->find($sourceId);
                $soldDetail = $source->details->firstWhere('product_id', $item['product_id']);

                if (!$soldDetail) {
                    abort(422, "El producto seleccionado no pertenece a esta " . ($return->sale_id ? "venta" : "orden") . ".");
                }

                $alreadyReturned = CustomerReturnDetail::whereHas('customerReturn', function ($q) use ($column, $sourceId, $excludeReturnId) {
                    $q->where($column, $sourceId)->where('status', 'Approved');
                    if ($excludeReturnId) {
                        $q->where('id', '!=', $excludeReturnId);
                    }
                })->where('product_id', $item['product_id'])->sum('qty');

                $maxAllowed = $soldDetail->quantity - $alreadyReturned;

                if ($item['qty'] > $maxAllowed) {
                    abort(422, "No se puede devolver {$item['qty']} unidades. Máximo disponible: {$maxAllowed}.");
                }
            }

            $product = Product::findOrFail($item['product_id']);
            $stockBefore = $product->stock;
            $stockAfter = $return->status === 'Approved' ? $stockBefore + $item['qty'] : $stockBefore;

            $return->details()->create([
                'product_id' => $item['product_id'],
                'reason' => $item['reason'],
                'qty' => $item['qty'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);

            if ($return->status === 'Approved') {
                $product->increment('stock', $item['qty']);
            }
        }
    }
}