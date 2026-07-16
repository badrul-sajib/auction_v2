<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::latest()->paginate(15);
        $balance = Withdrawal::availableBalance();
        $pendingOrdersCount = Order::whereIn('status', Withdrawal::PAID_STATUSES)
            ->whereNull('withdrawal_id')->count();

        return view('pages.inventory.withdrawals.index', compact('withdrawals', 'balance', 'pendingOrdersCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $withdrawal = DB::transaction(function () use ($request) {
            $orders = Order::whereIn('status', Withdrawal::PAID_STATUSES)
                ->whereNull('withdrawal_id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return null;
            }

            $withdrawal = Withdrawal::create([
                'invoice_number' => Withdrawal::nextInvoiceNumber(),
                'amount' => $orders->sum('total'),
                'orders_count' => $orders->count(),
                'note' => $request->input('note'),
            ]);

            Order::whereIn('id', $orders->pluck('id'))->update(['withdrawal_id' => $withdrawal->id]);

            return $withdrawal;
        });

        if (! $withdrawal) {
            return back()->with('status', 'No available balance to withdraw.');
        }

        return redirect()
            ->route('inventory.withdrawals.invoice', $withdrawal)
            ->with('status', "Withdrawal {$withdrawal->invoice_number} created.");
    }

    public function invoice(Withdrawal $withdrawal)
    {
        $withdrawal->load(['orders.product.merchant', 'orders.paymentMethod']);

        return view('pages.inventory.withdrawals.invoice', compact('withdrawal'));
    }
}
