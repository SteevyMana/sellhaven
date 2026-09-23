<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private array $rules = [
        'order_id' => 'nullable|required_without:purchase_id|prohibits:purchase_id|exists:orders,id',
        'purchase_id' => 'nullable|required_without:order_id|prohibits:order_id|exists:purchases,id',
        'method' => 'required|in:Cash,Credit Card,Debit Card,Transfer',
        'amount' => 'required|numeric|min:0.01',
        'status' => 'required|in:Paid,Pending,Failed',
        'date' => 'required|date',
        'note' => 'nullable|string',
    ];

    public function index()
    {
        return Payment::with(['order.customer', 'purchase.supplier'])->latest('date')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules);

        if ($data['status'] === 'Paid') {
            $this->assertNotCancelled($data);
            $this->assertNoOverpayment($data);
        }

        return Payment::create($data)->load('order.customer', 'purchase.supplier');
    }

    public function show(Payment $payment)
    {
        return $payment->load('order.customer', 'purchase.supplier');
    }

    public function update(Request $request, Payment $payment)
    {
        if ($payment->status === 'Paid') {
            abort(422, 'No se puede editar un pago ya marcado como Paid. Registra un pago nuevo para corregirlo.');
        }

        $data = $request->validate($this->rules);

        if ($data['status'] === 'Paid') {
            $this->assertNotCancelled($data);
            $this->assertNoOverpayment($data, excludePaymentId: $payment->id);
        }

        $payment->update($data);

        return $payment->load('order.customer', 'purchase.supplier');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->status === 'Paid') {
            abort(422, 'No se puede eliminar un pago ya marcado como Paid.');
        }

        $payment->delete();

        return response()->json(null, 204);
    }

    private function assertNotCancelled(array $data): void
    {
        if (!empty($data['order_id']) && Order::find($data['order_id'])->status === 'Cancelled') {
            abort(422, 'No se puede registrar un pago para una orden cancelada.');
        }
        if (!empty($data['purchase_id']) && Purchase::find($data['purchase_id'])->status === 'Cancelled') {
            abort(422, 'No se puede registrar un pago para una compra cancelada.');
        }
    }

    private function assertNoOverpayment(array $data, ?int $excludePaymentId = null): void
    {
        if (!empty($data['order_id'])) {
            $target = Order::find($data['order_id']);
            $column = 'order_id';
            $id = $data['order_id'];
        } else {
            $target = Purchase::find($data['purchase_id']);
            $column = 'purchase_id';
            $id = $data['purchase_id'];
        }

        $alreadyPaid = Payment::where($column, $id)
            ->where('status', 'Paid')
            ->when($excludePaymentId, fn ($q) => $q->where('id', '!=', $excludePaymentId))
            ->sum('amount');

        if ($alreadyPaid + $data['amount'] > $target->total) {
            $remaining = $target->total - $alreadyPaid;
            abort(422, "Este pago excede el saldo pendiente. Saldo restante: \${$remaining}.");
        }
    }
}