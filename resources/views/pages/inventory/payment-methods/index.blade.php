@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Payment Methods" />
    @include('pages.inventory.partials.flash')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">All Payment Methods</h3>
            <a href="{{ route('inventory.payment-methods.create') }}"
                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">+ New Method</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Customer enters</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Order</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($methods as $method)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $method->name }}</td>
                            <td class="px-5 py-4">
                                @if ($method->requires_transaction)
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/15">Transaction No.</span>
                                @elseif ($method->requires_agent)
                                    <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-600 dark:bg-warning-500/15">Admin/Rider ID</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-success-50 text-success-600 dark:bg-success-500/15' => $method->is_active,
                                    'bg-gray-100 text-gray-500 dark:bg-white/5' => ! $method->is_active,
                                ])>{{ $method->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">{{ $method->sort_order }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('inventory.payment-methods.edit', $method) }}"
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">Edit</a>
                                    <form method="POST" action="{{ route('inventory.payment-methods.destroy', $method) }}"
                                        onsubmit="return confirm('Delete this payment method?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-error-300 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No payment methods yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $methods->links() }}</div>
    </div>
@endsection
