@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Payments" />
    @include('pages.inventory.partials.flash')

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Collected (confirmed)</p>
            <p class="mt-1 text-2xl font-semibold text-success-600">{{ number_format($totalCollected, 2) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
            <p class="mt-1 text-2xl font-semibold text-warning-600">{{ number_format($totalPending, 2) }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Payment Records</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Order #</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Method</th>
                        <th class="px-5 py-3">Reference</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($payments as $payment)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4">{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">#{{ $payment->id }}</td>
                            <td class="px-5 py-4">{{ $payment->customer_name }}</td>
                            <td class="px-5 py-4">{{ $payment->product?->name }}</td>
                            <td class="px-5 py-4">{{ $payment->paymentMethod?->name ?: '—' }}</td>
                            <td class="px-5 py-4">
                                @if ($payment->transaction_number)
                                    <span class="text-gray-500 dark:text-gray-400">TxN:</span> {{ $payment->transaction_number }}
                                @elseif ($payment->agent_id)
                                    <span class="text-gray-500 dark:text-gray-400">{{ ucfirst($payment->agent_type ?? 'Agent') }}:</span> {{ $payment->agent_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-medium">{{ number_format($payment->total, 2) }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-warning-50 text-warning-600 dark:bg-warning-500/15' => $payment->status === 'pending',
                                    'bg-success-50 text-success-600 dark:bg-success-500/15' => $payment->status === 'confirmed',
                                    'bg-error-50 text-error-600 dark:bg-error-500/15' => $payment->status === 'cancelled',
                                    'bg-brand-50 text-brand-600 dark:bg-brand-500/15' => $payment->status === 'delivered',
                                ])>{{ ucfirst($payment->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No payments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $payments->links() }}</div>
    </div>
@endsection
