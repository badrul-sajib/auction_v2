@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Order #{{ $order->id }}" />
    @include('pages.inventory.partials.flash')

    <div class="mb-5 flex items-center justify-between">
        <a href="{{ route('inventory.orders.index') }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back to orders
        </a>

        <form method="POST" action="{{ route('inventory.orders.status', $order) }}" class="flex items-center gap-2">
            @csrf @method('PATCH')
            <label class="text-sm text-gray-500 dark:text-gray-400">Status</label>
            <select name="status" onchange="this.form.submit()"
                @class([
                    'rounded-lg border px-3 py-1.5 text-sm font-medium',
                    'border-warning-300 text-warning-600' => $order->status === 'pending',
                    'border-success-300 text-success-600' => $order->status === 'confirmed',
                    'border-error-300 text-error-600' => $order->status === 'cancelled',
                ])>
                <option value="pending" @selected($order->status === 'pending')>Pending</option>
                <option value="confirmed" @selected($order->status === 'confirmed')>Confirmed</option>
                <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: item + customer + payment -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Product -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Item</h3>
                <div class="flex gap-4">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/5">
                        @if ($order->product?->imageUrl())
                            <img src="{{ $order->product->imageUrl() }}" class="h-full w-full object-cover" alt="{{ $order->product->name }}" />
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $order->product?->name ?: '—' }}</p>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            SKU: {{ $order->product?->sku ?: '—' }}
                            @if ($order->product?->category) · {{ $order->product->category->name }} @endif
                        </p>
                        @if ($order->product?->merchant)
                            <p class="text-sm text-gray-500 dark:text-gray-400">Merchant: {{ $order->product->merchant->name }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <div class="flex justify-between py-1 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Unit price</span>
                        <span class="text-gray-800 dark:text-white/90">{{ number_format($order->unit_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Quantity</span>
                        <span class="text-gray-800 dark:text-white/90">{{ $order->quantity }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
                        <span class="font-medium text-gray-800 dark:text-white/90">Total</span>
                        <span class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Customer</h3>
                <dl class="grid grid-cols-1 gap-y-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-gray-400">Name</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase text-gray-400">Phone</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->customer_phone }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase text-gray-400">Delivery Address</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->customer_address ?: '—' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Payment -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Payment</h3>
                <dl class="grid grid-cols-1 gap-y-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs uppercase text-gray-400">Method</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->paymentMethod?->name ?: '—' }}</dd>
                    </div>
                    @if ($order->transaction_number)
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Transaction Number</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->transaction_number }}</dd>
                        </div>
                    @endif
                    @if ($order->agent_id)
                        <div>
                            <dt class="text-xs uppercase text-gray-400">Admin / Rider ID</dt>
                            <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->agent_id }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Right: summary -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Summary</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Order #</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Status</dt>
                        <dd>
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-warning-50 text-warning-600 dark:bg-warning-500/15' => $order->status === 'pending',
                                'bg-success-50 text-success-600 dark:bg-success-500/15' => $order->status === 'confirmed',
                                'bg-error-50 text-error-600 dark:bg-error-500/15' => $order->status === 'cancelled',
                            ])>{{ ucfirst($order->status) }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Placed</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Last updated</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->updated_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Source</dt>
                        <dd class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->orderLink ? 'Order link' : 'Manual' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
