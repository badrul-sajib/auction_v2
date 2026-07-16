@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Withdrawals" />
    @include('pages.inventory.partials.flash')

    <div x-data="{ open: false }"
        class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Current Balance (available to withdraw)</p>
                <p class="mt-1 text-3xl font-semibold text-success-600">৳{{ number_format($balance, 2) }}</p>
                <p class="mt-1 text-xs text-gray-400">From {{ $pendingOrdersCount }} paid order(s) not yet withdrawn.</p>
            </div>
            <button type="button" @click="open = true" @disabled($pendingOrdersCount === 0)
                class="shrink-0 rounded-lg bg-brand-500 px-6 py-3 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-40">
                Withdraw Balance
            </button>
        </div>

        <!-- Withdraw modal -->
        <div x-show="open" x-cloak @keydown.escape.window="open = false"
            class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
            <div @click.outside="open = false"
                class="w-full max-w-md rounded-2xl bg-white p-6 text-left dark:bg-gray-900">
                <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">Confirm Withdrawal</h4>
                <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">A withdrawal invoice will be generated for these orders.</p>

                <div class="mb-4 space-y-2 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Amount</span>
                        <span class="font-semibold text-success-600">৳{{ number_format($balance, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Orders settled</span>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ $pendingOrdersCount }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Invoice #</span>
                        <span class="font-medium text-gray-800 dark:text-white/90">{{ \App\Models\Withdrawal::nextInvoiceNumber() }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('inventory.withdrawals.store') }}">
                    @csrf
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Note (optional)</label>
                    <input type="text" name="note" value="{{ old('note') }}" placeholder="e.g. Bank transfer ref"
                        class="mb-5 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="open = false"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</button>
                        <button type="submit"
                            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Confirm Withdraw</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Withdrawal History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Invoice #</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3 text-right">Orders</th>
                        <th class="px-5 py-3 text-right">Amount</th>
                        <th class="px-5 py-3">Note</th>
                        <th class="px-5 py-3 text-right">Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($withdrawals as $withdrawal)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $withdrawal->invoice_number }}</td>
                            <td class="px-5 py-4">{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-5 py-4 text-right">{{ $withdrawal->orders_count }}</td>
                            <td class="px-5 py-4 text-right font-medium">৳{{ number_format($withdrawal->amount, 2) }}</td>
                            <td class="px-5 py-4">{{ $withdrawal->note ?: '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('inventory.withdrawals.invoice', $withdrawal) }}"
                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">View / Download</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No withdrawals yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4">{{ $withdrawals->links() }}</div>
    </div>
@endsection
