@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Purchases" />
    @include('pages.inventory.partials.flash')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Purchase History</h3>
            <a href="{{ route('inventory.purchases.create') }}"
                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">+ New Purchase</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Merchant</th>
                        <th class="px-5 py-3">Warehouse</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3 text-right">Unit Cost</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($purchases as $purchase)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4">{{ optional($purchase->purchased_at)->format('Y-m-d') ?: $purchase->created_at->format('Y-m-d') }}</td>
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $purchase->product?->name }}</td>
                            <td class="px-5 py-4">{{ $purchase->merchant?->name ?: '—' }}</td>
                            <td class="px-5 py-4">{{ $purchase->warehouse?->name }}</td>
                            <td class="px-5 py-4 text-right">{{ $purchase->quantity }}</td>
                            <td class="px-5 py-4 text-right">{{ number_format($purchase->unit_cost, 2) }}</td>
                            <td class="px-5 py-4 text-right font-medium">{{ number_format($purchase->total_cost, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No purchases yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $purchases->links() }}</div>
    </div>
@endsection
