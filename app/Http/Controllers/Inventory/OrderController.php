<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['product_id', 'merchant_id', 'status', 'date_from', 'date_to']);

        $orders = Order::with(['product.merchant', 'paymentMethod'])
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->when($filters['merchant_id'] ?? null, fn ($q, $v) =>
                $q->whereHas('product', fn ($p) => $p->where('merchant_id', $v)))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.inventory.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'products' => Product::orderBy('name')->get(),
            'merchants' => Merchant::orderBy('name')->get(),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['product.category', 'product.merchant', 'paymentMethod', 'auction']);

        return view('pages.inventory.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,delivered'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $data['status'];

        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            // Deduct on-hand stock when an order becomes delivered; restore it if moved back.
            if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
                $order->product?->reduceStock($order->quantity);
            } elseif ($oldStatus === 'delivered' && $newStatus !== 'delivered') {
                $order->product?->increaseStock($order->quantity);
            }

            $order->update(['status' => $newStatus]);
        });

        return redirect()->route('inventory.orders.index')->with('status', 'Order status updated.');
    }
}
