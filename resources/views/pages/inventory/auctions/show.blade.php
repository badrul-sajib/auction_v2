@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$auction->title ?: 'Auction #' . $auction->id" />
    @include('pages.inventory.partials.flash')

    @php $remaining = $auction->remainingStock(); @endphp

    <div class="mb-5">
        <a href="{{ route('inventory.auctions.index', $auction->product) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">← Back to auctions</a>
    </div>

    <!-- Summary tiles -->
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Stock / Remaining</p>
            <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $auction->stock ?? '∞' }} / {{ $remaining === null ? '∞' : $remaining }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Units Sold</p>
            <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $auction->soldQuantity() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Orders</p>
            <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $orders->total() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Revenue (confirmed)</p>
            <p class="mt-1 text-2xl font-semibold text-success-600">{{ number_format($auction->revenue(), 2) }}</p>
        </div>
    </div>

    <!-- Auction meta -->
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{ link: '{{ $auction->url() }}', copied: false }">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Product</p>
                <p class="font-medium text-gray-800 dark:text-white/90">{{ $auction->product?->name }}</p>
                <p class="mt-1 text-xs text-gray-400">
                    Status: {{ $auction->statusLabel() }} ·
                    {{ $auction->expires_at ? 'Valid until ' . $auction->expires_at->format('M j, Y g:i A') : 'Never expires' }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" readonly :value="link"
                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                <button type="button" @click="navigator.clipboard.writeText(link); copied = true; setTimeout(() => copied = false, 1500)"
                    class="h-10 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">
                    <span x-show="!copied">Copy</span><span x-show="copied">Copied!</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Orders for this auction -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Orders from this Auction</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($orders as $order)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $order->customer_name }}</td>
                            <td class="px-5 py-4">{{ $order->customer_phone }}</td>
                            <td class="px-5 py-4 text-right">{{ $order->quantity }}</td>
                            <td class="px-5 py-4 text-right font-medium">{{ number_format($order->total, 2) }}</td>
                            <td class="px-5 py-4">{{ $order->paymentMethod?->name ?: '—' }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-warning-50 text-warning-600 dark:bg-warning-500/15' => $order->status === 'pending',
                                    'bg-success-50 text-success-600 dark:bg-success-500/15' => $order->status === 'confirmed',
                                    'bg-error-50 text-error-600 dark:bg-error-500/15' => $order->status === 'cancelled',
                                    'bg-brand-50 text-brand-600 dark:bg-brand-500/15' => $order->status === 'delivered',
                                ])>{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('inventory.orders.show', $order) }}"
                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No orders from this auction yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $orders->links() }}</div>
    </div>
@endsection
