<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private array $rules = [
        'customer_id' => 'required|exists:customers,id',
        'status' => 'required|in:Pending,Processing,Shipped,Delivered,Cancelled',
        'date' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
    ];

    public function index()
    {
        return Order::with(['customer', 'details.product'])->latest('date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);

        return DB::transaction(function () use ($data) {
            if ($data['status'] === 'Delivered') {
                $this->assertSufficientStock($data['items']);
            }

            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'status' => $data['status'],
                'date' => $data['date'],
                'total' => $total,
            ]);

            foreach ($data['items'] as $item) {
                $order->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            if ($data['status'] === 'Delivered') {
                $this->applyStockDecrement($data['items']);
            }

            return $order->load('customer', 'details.product');
        });
    }

    public function show(Order $order)
    {
        return $order->load('customer', 'details.product');
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate($this->rules);

        return DB::transaction(function () use ($data, $order) {
            if ($order->status === 'Delivered') {
                $this->revertStockDecrement($order->details);
            }

            $order->details()->delete();

            if ($data['status'] === 'Delivered') {
                $this->assertSufficientStock($data['items']);
            }

            $total = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['price']);

            $order->update([
                'customer_id' => $data['customer_id'],
                'status' => $data['status'],
                'date' => $data['date'],
                'total' => $total,
            ]);

            foreach ($data['items'] as $item) {
                $order->details()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            if ($data['status'] === 'Delivered') {
                $this->applyStockDecrement($data['items']);
            }

            return $order->load('customer', 'details.product');
        });
    }

    public function destroy(Order $order)
    {
        DB::transaction(function () use ($order) {
            if ($order->status === 'Delivered') {
                $this->revertStockDecrement($order->details);
            }
            $order->delete();
        });

        return response()->json(null, 204);
    }

    private function assertSufficientStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product || $product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => ["Not enough stock for \"" . ($product->name ?? 'this product') . "\" (available: " . ($product->stock ?? 0) . ", requested: {$item['quantity']})."],
                ]);
            }
        }
    }

    private function applyStockDecrement(array $items): void
    {
        foreach ($items as $item) {
            Product::where('id', $item['product_id'])->decrement('stock', $item['quantity']);
        }
    }

    private function revertStockDecrement($details): void
    {
        foreach ($details as $detail) {
            Product::where('id', $detail->product_id)->increment('stock', $detail->quantity);
        }
    }
}