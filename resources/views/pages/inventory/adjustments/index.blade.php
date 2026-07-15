@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Stock Adjustments" />
    @include('pages.inventory.partials.flash')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Adjustment History</h3>
            <a href="{{ route('inventory.adjustments.create') }}"
                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">+ New Adjustment</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Warehouse</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3 text-right">Qty</th>
                        <th class="px-5 py-3">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($adjustments as $adjustment)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4">{{ $adjustment->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $adjustment->product?->name }}</td>
                            <td class="px-5 py-4">{{ $adjustment->warehouse?->name }}</td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-success-50 text-success-600 dark:bg-success-500/15' => $adjustment->type === 'add',
                                    'bg-error-50 text-error-600 dark:bg-error-500/15' => $adjustment->type === 'remove',
                                ])>{{ ucfirst($adjustment->type) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">{{ $adjustment->type === 'add' ? '+' : '−' }}{{ $adjustment->quantity }}</td>
                            <td class="px-5 py-4">{{ $adjustment->reason ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No adjustments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $adjustments->links() }}</div>
    </div>
@endsection
