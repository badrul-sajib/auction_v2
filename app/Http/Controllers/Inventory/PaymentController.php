<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Order;

class PaymentController extends Controller
{
    public function index()
    {
        $query = Order::with(['product', 'paymentMethod'])->whereNotNull('payment_method_id');

        $payments = (clone $query)->latest()->paginate(15);

        $totalCollected = (clone $query)->whereIn('status', ['confirmed', 'delivered'])->sum('total');
        $totalPending = (clone $query)->where('status', 'pending')->sum('total');

        return view('pages.inventory.payments.index', compact('payments', 'totalCollected', 'totalPending'));
    }
}
