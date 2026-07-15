@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Stock Levels" />
    @include('pages.inventory.partials.flash')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">On-hand by Warehouse</h3>
            <div class="flex gap-2">
                <a href="{{ route('inventory.adjustments.create') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Adjust</a>
                <a href="{{ route('inventory.transfers.create') }}"
                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Transfer</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">SKU</th>
                        <th class="px-5 py-3">Warehouse</th>
                        <th class="px-5 py-3 text-right">Quantity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($stocks as $stock)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $stock->product?->name }}</td>
                            <td class="px-5 py-4">{{ $stock->product?->sku }}</td>
                            <td class="px-5 py-4">{{ $stock->warehouse?->name }}</td>
                            <td class="px-5 py-4 text-right font-medium">{{ $stock->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">
                                No stock recorded yet. Use <a href="{{ route('inventory.adjustments.create') }}" class="text-brand-500">Adjust</a> to add stock.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $stocks->links() }}</div>
    </div>
@endsection
