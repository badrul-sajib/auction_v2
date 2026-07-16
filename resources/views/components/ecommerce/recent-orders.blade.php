@props(['orders' => null])

@php
    $orders = $orders ?? collect();

    $statusClasses = function ($status) {
        $base = 'rounded-full px-2 py-0.5 text-theme-xs font-medium capitalize';
        return match ($status) {
            'confirmed' => $base . ' bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500',
            'delivered' => $base . ' bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
            'pending' => $base . ' bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-orange-400',
            'cancelled' => $base . ' bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
            default => $base . ' bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
        };
    };
@endphp

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
    <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Orders</h3>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.orders.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                See all
            </a>
        </div>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="min-w-full">
            <thead>
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    <th class="py-3 text-left"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Product</p></th>
                    <th class="py-3 text-left"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Customer</p></th>
                    <th class="py-3 text-left"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Total</p></th>
                    <th class="py-3 text-left"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="py-3 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="h-[44px] w-[44px] overflow-hidden rounded-md border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/5">
                                    @if ($order->product?->imageUrl())
                                        <img src="{{ $order->product->imageUrl() }}" alt="{{ $order->product->name }}" class="h-full w-full object-cover" />
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $order->product?->name ?: '—' }}</p>
                                    <span class="text-gray-500 text-theme-xs dark:text-gray-400">Qty: {{ $order->quantity }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                            <p class="text-gray-700 text-theme-sm dark:text-gray-300">{{ $order->customer_name }}</p>
                            <span class="text-gray-500 text-theme-xs dark:text-gray-400">{{ $order->customer_phone }}</span>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                            <p class="text-gray-700 text-theme-sm dark:text-gray-300">{{ number_format($order->total, 2) }}</p>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                            <span class="{{ $statusClasses($order->status) }}">{{ $order->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td colspan="4" class="py-8 text-center text-gray-500 text-theme-sm dark:text-gray-400">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
