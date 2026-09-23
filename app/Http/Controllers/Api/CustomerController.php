<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::withCount('orders')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'status' => 'in:Active,Inactive',
        ]);

        return Customer::create($data);
    }

    public function show(Customer $customer)
    {
        return $customer->loadCount('orders');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'status' => 'in:Active,Inactive',
        ]);

        $customer->update($data);

        return $customer;
    }

    public function destroy(Customer $customer)
    {
        $hasHistory = $customer->orders()->exists() || $customer->sales()->exists() || $customer->customerReturns()->exists();
        if ($hasHistory) {
                abort(422, 'you cannot delete a customer that has order or sales history.');
            }

        $customer->delete();

        return response()->json(null, 204);
    }
}