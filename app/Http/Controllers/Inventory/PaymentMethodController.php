<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('sort_order')->orderBy('name')->paginate(15);

        return view('pages.inventory.payment-methods.index', compact('methods'));
    }

    public function create()
    {
        return view('pages.inventory.payment-methods.create', ['method' => new PaymentMethod(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        PaymentMethod::create($this->validateData($request));

        return redirect()->route('inventory.payment-methods.index')->with('status', 'Payment method created.');
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('pages.inventory.payment-methods.edit', ['method' => $paymentMethod]);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $paymentMethod->update($this->validateData($request));

        return redirect()->route('inventory.payment-methods.index')->with('status', 'Payment method updated.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return redirect()->route('inventory.payment-methods.index')->with('status', 'Payment method deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'requires_transaction' => ['nullable', 'boolean'],
            'requires_agent' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['requires_transaction'] = $request->boolean('requires_transaction');
        $data['requires_agent'] = $request->boolean('requires_agent');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
