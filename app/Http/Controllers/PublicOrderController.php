<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicOrderController extends Controller
{
    public function show(string $token)
    {
        $auction = Auction::with('product')->where('token', $token)->first();

        if (! $auction || ! $auction->isValid()) {
            return response()->view('pages.order.invalid', [], 410);
        }

        return view('pages.order.show', [
            'auction' => $auction,
            'product' => $auction->product,
            'paymentMethods' => PaymentMethod::active()->get(),
        ]);
    }

    public function store(Request $request, string $token)
    {
        $auction = Auction::with('product')->where('token', $token)->first();

        if (! $auction || ! $auction->isValid()) {
            return response()->view('pages.order.invalid', [], 410);
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'transaction_number' => ['nullable', 'string', 'max:100'],
            'agent_type' => ['nullable', 'in:admin,rider'],
            'agent_id' => ['nullable', 'string', 'max:100'],
        ]);

        // Enforce the auction's remaining stock.
        $remaining = $auction->remainingStock();
        if ($remaining !== null && $data['quantity'] > $remaining) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$remaining} unit(s) left in this auction.",
            ]);
        }

        $method = PaymentMethod::where('is_active', true)->findOrFail($data['payment_method_id']);

        // Enforce the extra field the selected method requires.
        if ($method->requires_transaction && blank($data['transaction_number'] ?? null)) {
            throw ValidationException::withMessages([
                'transaction_number' => 'Please enter the transaction number for ' . $method->name . '.',
            ]);
        }
        if ($method->requires_agent) {
            if (blank($data['agent_type'] ?? null)) {
                throw ValidationException::withMessages([
                    'agent_type' => 'Please choose Admin or Rider.',
                ]);
            }
            if (blank($data['agent_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'agent_id' => 'Please enter the ' . ucfirst($data['agent_type']) . ' ID.',
                ]);
            }
        }

        $unitPrice = $auction->product->currentPrice();

        $order = Order::create([
            'order_link_id' => $auction->id,
            'product_id' => $auction->product_id,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_address' => $data['customer_address'] ?? null,
            'quantity' => $data['quantity'],
            'unit_price' => $unitPrice,
            'total' => $unitPrice * $data['quantity'],
            'status' => 'pending',
            'payment_method_id' => $method->id,
            'transaction_number' => $method->requires_transaction ? $data['transaction_number'] : null,
            'agent_type' => $method->requires_agent ? $data['agent_type'] : null,
            'agent_id' => $method->requires_agent ? $data['agent_id'] : null,
        ]);

        return view('pages.order.thankyou', ['order' => $order, 'product' => $auction->product]);
    }
}
