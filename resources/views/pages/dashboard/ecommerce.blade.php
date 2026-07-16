@extends('layouts.app')

@section('content')
  <script>
    window.__monthlySales = @json($monthlySales);
    window.__targetPercent = {{ $targetPercent }};
  </script>
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
      <x-ecommerce.ecommerce-metrics
        :customers="$customersCount" :customers-change="$customersChange"
        :orders="$ordersCount" :orders-change="$ordersChange" />

      <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Current Balance</span>
            <h4 class="mt-2 text-title-sm font-bold text-success-600">৳{{ number_format($currentBalance, 2) }}</h4>
            <p class="mt-1 text-xs text-gray-400">Available to withdraw</p>
          </div>
          <a href="{{ route('inventory.withdrawals.index') }}"
            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Withdraw</a>
        </div>
      </div>

      <x-ecommerce.monthly-sale />
    </div>
    <div class="col-span-12 xl:col-span-5">
        <x-ecommerce.monthly-target
          :percent="$targetPercent" :target="$targetAmount"
          :revenue="$revenueThisMonth" :today="$todayRevenue" />
    </div>

    <div class="col-span-12">
      <x-ecommerce.recent-orders :orders="$recentOrders" />
    </div>
  </div>
@endsection
