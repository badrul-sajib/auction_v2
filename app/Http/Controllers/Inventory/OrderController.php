<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['product', 'paymentMethod'])->latest()->paginate(15);

        return view('pages.inventory.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['product.category', 'product.merchant', 'paymentMethod', 'orderLink']);

        return view('pages.inventory.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled'],
        ]);

        $order->update($data);

        return redirect()->route('inventory.orders.index')->with('status', 'Order status updated.');
    }
}
