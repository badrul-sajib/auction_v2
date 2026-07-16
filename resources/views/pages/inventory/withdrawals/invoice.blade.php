@extends('layouts.app')

@push('scripts')
    <style>
        @media print {
            aside, header, .no-print { display: none !important; }
            body, .xl\:flex { display: block !important; }
            #invoice-sheet { border: none !important; box-shadow: none !important; }
            @page { margin: 12mm; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-5 flex items-center justify-between no-print">
        <a href="{{ route('inventory.withdrawals.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">← Back to withdrawals</a>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12v7a1 1 0 001 1h14a1 1 0 001-1v-7M12 3v12m0 0l-4-4m4 4l4-4"/></svg>
            Download / Print
        </button>
    </div>

    <div id="invoice-sheet" class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03]">
        <!-- Header -->
        <div class="flex items-start justify-between border-b border-gray-200 pb-6 dark:border-gray-800">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Withdrawal Invoice</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ config('app.name') }}</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $withdrawal->invoice_number }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $withdrawal->created_at->format('M j, Y g:i A') }}</p>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-2 gap-4 py-6">
            <div>
                <p class="text-xs uppercase text-gray-400">Orders settled</p>
                <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $withdrawal->orders_count }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs uppercase text-gray-400">Total withdrawn</p>
                <p class="text-lg font-semibold text-success-600">৳{{ number_format($withdrawal->amount, 2) }}</p>
            </div>
        </div>

        <!-- Orders table -->
        <table class="w-full text-left text-sm">
            <thead class="border-y border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                <tr>
                    <th class="py-2 pr-3">Order</th>
                    <th class="py-2 pr-3">Product / Merchant</th>
                    <th class="py-2 pr-3">Customer</th>
                    <th class="py-2 pr-3">Payment</th>
                    <th class="py-2 pr-3 text-right">Qty</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 align-top">
                @foreach ($withdrawal->orders as $order)
                    <tr class="text-gray-700 dark:text-gray-300">
                        <td class="py-3 pr-3">
                            #{{ $order->id }}
                            <span class="block text-xs text-gray-400">{{ $order->created_at->format('Y-m-d') }}</span>
                            <span class="block text-xs capitalize text-gray-400">{{ $order->status }}</span>
                        </td>
                        <td class="py-3 pr-3">
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ $order->product?->name ?: '—' }}</span>
                            <span class="block text-xs text-gray-400">SKU: {{ $order->product?->sku ?: '—' }}</span>
                            <span class="block text-xs text-gray-400">Merchant: {{ $order->product?->merchant?->name ?: '—' }}</span>
                        </td>
                        <td class="py-3 pr-3">
                            {{ $order->customer_name }}
                            <span class="block text-xs text-gray-400">{{ $order->customer_phone }}</span>
                            @if ($order->customer_address)
                                <span class="block text-xs text-gray-400">{{ $order->customer_address }}</span>
                            @endif
                        </td>
                        <td class="py-3 pr-3">
                            {{ $order->paymentMethod?->name ?: '—' }}
                            @if ($order->transaction_number)
                                <span class="block text-xs text-gray-400">TxN: {{ $order->transaction_number }}</span>
                            @elseif ($order->agent_id)
                                <span class="block text-xs text-gray-400">{{ ucfirst($order->agent_type ?? 'Agent') }}: {{ $order->agent_id }}</span>
                            @endif
                            <span class="block text-xs text-gray-400">Unit: ৳{{ number_format($order->unit_price, 2) }}</span>
                        </td>
                        <td class="py-3 pr-3 text-right">{{ $order->quantity }}</td>
                        <td class="py-3 text-right">৳{{ number_format($order->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-300 dark:border-gray-700">
                    <td colspan="5" class="py-3 pr-3 text-right font-semibold text-gray-800 dark:text-white/90">Total</td>
                    <td class="py-3 text-right text-lg font-bold text-gray-800 dark:text-white/90">৳{{ number_format($withdrawal->amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($withdrawal->note)
            <p class="mt-6 text-sm text-gray-500 dark:text-gray-400"><span class="font-medium">Note:</span> {{ $withdrawal->note }}</p>
        @endif

        <p class="mt-8 text-center text-xs text-gray-400">Generated by {{ config('app.name') }} · {{ now()->format('M j, Y') }}</p>
    </div>
@endsection
