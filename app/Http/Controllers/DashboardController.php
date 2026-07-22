<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Withdrawal;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

        // Orders count + month-over-month change
        $ordersThisMonth = Order::where('created_at', '>=', $startOfMonth)->count();
        $ordersLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        // Unique customers (by phone) + month-over-month change
        $customersThisMonth = Order::where('created_at', '>=', $startOfMonth)->distinct('customer_phone')->count('customer_phone');
        $customersLastMonth = Order::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->distinct('customer_phone')->count('customer_phone');

        // Revenue (confirmed or delivered orders)
        $revenueThisMonth = (float) Order::paid()->where('created_at', '>=', $startOfMonth)->sum('total');
        $todayRevenue = (float) Order::paid()->whereDate('created_at', $now->toDateString())->sum('total');

        // Monthly sales for the current year (revenue per month)
        $monthlyTotals = Order::paid()
            ->whereYear('created_at', $now->year)
            ->selectRaw('MONTH(created_at) as m, SUM(total) as t')
            ->groupBy('m')
            ->pluck('t', 'm');

        $monthlySales = collect(range(1, 12))
            ->map(fn ($m) => round((float) ($monthlyTotals[$m] ?? 0), 2))
            ->all();

        // Monthly target progress
        $targetAmount = 20000;
        $targetPercent = $targetAmount > 0 ? min(100, round($revenueThisMonth / $targetAmount * 100, 2)) : 0;

        return view('pages.dashboard.ecommerce', [
            'ordersCount' => Order::count(),
            'ordersChange' => $this->percentChange($ordersThisMonth, $ordersLastMonth),
            'customersCount' => Order::distinct('customer_phone')->count('customer_phone'),
            'customersChange' => $this->percentChange($customersThisMonth, $customersLastMonth),
            'productsCount' => Product::count(),
            'revenueThisMonth' => $revenueThisMonth,
            'todayRevenue' => $todayRevenue,
            'targetAmount' => $targetAmount,
            'targetPercent' => $targetPercent,
            'monthlySales' => $monthlySales,
            'recentOrders' => Order::with('product')->latest()->take(6)->get(),
            'currentBalance' => Withdrawal::availableBalance(),
        ]);
    }

    private function percentChange(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round(($current - $previous) / $previous * 100, 2);
    }
}
