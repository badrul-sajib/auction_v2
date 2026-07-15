<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\OrderLink;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderLinkController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'validity' => ['required', 'in:1,7,30,never,custom'],
            'expires_at' => ['nullable', 'required_if:validity,custom', 'date', 'after:now'],
        ]);

        $expiresAt = match ($data['validity']) {
            'never' => null,
            'custom' => $data['expires_at'],
            default => now()->addDays((int) $data['validity']),
        };

        $link = OrderLink::create([
            'product_id' => $product->id,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return redirect()
            ->route('inventory.products.index')
            ->with('order_link', $link->url())
            ->with('status', 'Order link created for "' . $product->name . '".');
    }

    public function destroy(OrderLink $orderLink)
    {
        $orderLink->delete();

        return redirect()->route('inventory.products.index')->with('status', 'Order link removed.');
    }
}
