@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Orders" />
    @include('pages.inventory.partials.flash')

    @php $hasFilters = collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty(); @endphp

    <form method="GET" action="{{ route('inventory.orders.index') }}" class="mb-6 space-y-4">
        <!-- Search card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
            <div class="flex gap-2">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                    placeholder="Order ID or phone number…"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                <button type="submit" class="shrink-0 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">Search</button>
            </div>
        </div>

        <!-- Filters card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Item</label>
                <select name="product_id"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All items</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Merchant</label>
                <select name="merchant_id"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All merchants</option>
                    @foreach ($merchants as $merchant)
                        <option value="{{ $merchant->id }}" @selected(($filters['merchant_id'] ?? '') == $merchant->id)>{{ $merchant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                <select name="status"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All statuses</option>
                    @foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500 dark:text-gray-400">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Apply Filters</button>
            @if ($hasFilters)
                <a href="{{ route('inventory.orders.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Clear</a>
            @endif
        </div>
        </div>
    </form>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Customer Orders</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($orders as $order)
                        <tr class="text-gray-700 dark:text-gray-300 align-top">
                            <td class="px-5 py-4">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $order->product?->name }}</td>
                            <td class="px-5 py-4">
                                {{ $order->customer_name }}
                                @if ($order->customer_address)
                                    <span class="block text-xs text-gray-400">{{ $order->customer_address }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">{{ $order->customer_phone }}</td>
                            <td class="px-5 py-4 text-right">{{ $order->quantity }}</td>
                            <td class="px-5 py-4 text-right font-medium">{{ number_format($order->total, 2) }}</td>
                            <td class="px-5 py-4">
                                {{ $order->paymentMethod?->name ?: '—' }}
                                @if ($order->transaction_number)
                                    <span class="block text-xs text-gray-400">TxN: {{ $order->transaction_number }}</span>
                                @elseif ($order->agent_id)
                                    <span class="block text-xs text-gray-400">{{ ucfirst($order->agent_type ?? 'Agent') }}: {{ $order->agent_id }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('inventory.orders.status', $order) }}" class="flex items-center gap-2">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()"
                                        @class([
                                            'rounded-lg border px-2 py-1 text-xs font-medium',
                                            'border-warning-300 text-warning-600' => $order->status === 'pending',
                                            'border-success-300 text-success-600' => $order->status === 'confirmed',
                                            'border-error-300 text-error-600' => $order->status === 'cancelled',
                                            'border-brand-300 text-brand-600' => $order->status === 'delivered',
                                        ])>
                                        <option value="pending" @selected($order->status === 'pending')>Pending</option>
                                        <option value="confirmed" @selected($order->status === 'confirmed')>Confirmed</option>
                                        <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                                        <option value="delivered" @selected($order->status === 'delivered')>Delivered</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('inventory.orders.show', $order) }}"
                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $orders->links() }}</div>
    </div>
@endsection
