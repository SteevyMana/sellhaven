<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    // "Refunded" NO es un valor aceptado aquí — solo se llega a él vía refund().
    private array $rules = [
        'customer_id' => 'nullable|exists:customers,id',
        'payment_method' => 'required|in:Cash,Credit Card,Debit Card,Transfer',
        'status' => 'required|in:Pending,Completed',
        'date' => 'required|date',
        'amount_paid' => 'required|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
    ];

    public function index()
    {
        return Sale::with(['customer', 'details.product'])->latest('date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);
        $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);

        if ($data['status'] === 'Completed') {
            $this->assertSufficientStock($data['items']);
            $this->assertSufficientPayment($data['amount_paid'], $total);
        }

        return DB::transaction(function () use ($data, $total) {
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'date' => $data['date'],
                'amount_paid' => $data['amount_paid'],
                'total' => $total,
            ]);

            foreach ($data['items'] as $item) {
                $sale->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            if ($sale->status === 'Completed') {
                $this->applyStockDecrement($data['items']);
            }

            return $sale->load('customer', 'details.product');
        });
    }

    public function show(Sale $sale)
    {
        return $sale->load('customer', 'details.product');
    }

    public function update(Request $request, Sale $sale)
    {
        // Completed y Refunded son estados finales: no se editan, ni siquiera para cambiar el status.
        if (in_array($sale->status, ['Completed', 'Refunded'])) {
            abort(422, 'No se puede editar una venta ya completada o reembolsada.');
        }

        $data = $request->validate($this->rules);
        $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);

        if ($data['status'] === 'Completed') {
            $this->assertSufficientStock($data['items']);
            $this->assertSufficientPayment($data['amount_paid'], $total);
        }

        return DB::transaction(function () use ($data, $sale, $total) {
            $sale->details()->delete();

            $sale->update([
                'customer_id' => $data['customer_id'] ?? null,
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'date' => $data['date'],
                'amount_paid' => $data['amount_paid'],
                'total' => $total,
            ]);

            foreach ($data['items'] as $item) {
                $sale->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            if ($sale->status === 'Completed') {
                $this->applyStockDecrement($data['items']);
            }

            return $sale->load('customer', 'details.product');
        });
    }

    public function destroy(Sale $sale)
    {
        if (in_array($sale->status, ['Completed', 'Refunded'])) {
            abort(422, 'No se puede eliminar una venta ya completada o reembolsada.');
        }

        $sale->delete();

        return response()->json(null, 204);
    }

    /**
     * Única forma de llegar a "Refunded". Revierte el stock que la venta
     * había restado y bloquea el registro para siempre (no vuelve a Pending).
     */
    public function refund(Sale $sale)
    {
        if ($sale->status !== 'Completed') {
            abort(422, 'Solo se puede reembolsar una venta que ya fue completada.');
        }

        return DB::transaction(function () use ($sale) {
            foreach ($sale->details as $detail) {
                Product::where('id', $detail->product_id)->increment('stock', $detail->quantity);
            }

            $sale->update(['status' => 'Refunded']);

            return $sale->load('customer', 'details.product');
        });
    }

    private function assertSufficientStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product || $product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => ["No hay suficiente stock de \"" . ($product->name ?? 'este producto') . "\" (disponible: " . ($product->stock ?? 0) . ", solicitado: {$item['quantity']})."],
                ]);
            }
        }
    }

    private function assertSufficientPayment(float $amountPaid, float $total): void
    {
        if ($amountPaid < $total) {
            throw ValidationException::withMessages([
                'amount_paid' => ["El monto pagado (\${$amountPaid}) es menor al total (\${$total})."],
            ]);
        }
    }

    private function applyStockDecrement(array $items): void
    {
        foreach ($items as $item) {
            Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
        }
    }
}