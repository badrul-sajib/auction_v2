@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Auctions" />
    @include('pages.inventory.partials.flash')

    @if (session('auction_link'))
        <div x-data="{ link: '{{ session('auction_link') }}', copied: false }"
            class="mb-5 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 dark:border-brand-500/30 dark:bg-brand-500/10">
            <p class="mb-2 text-sm font-medium text-brand-700 dark:text-brand-300">Auction link ready — share it with your customers:</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly :value="link"
                    class="h-10 flex-1 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                <button type="button"
                    @click="navigator.clipboard.writeText(link); copied = true; setTimeout(() => copied = false, 2000)"
                    class="h-10 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">
                    <span x-show="!copied">Copy</span>
                    <span x-show="copied">Copied!</span>
                </button>
                <a :href="link" target="_blank"
                    class="h-10 rounded-lg border border-gray-300 px-4 text-sm font-medium leading-10 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Open</a>
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Auctions — {{ $product->name }}</h3>
                <a href="{{ route('inventory.products.index') }}" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">← Back to products</a>
            </div>
            <a href="{{ route('inventory.auctions.create', $product) }}"
                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">+ Create Auction</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Auction</th>
                        <th class="px-5 py-3 text-right">Stock</th>
                        <th class="px-5 py-3 text-right">Sold</th>
                        <th class="px-5 py-3 text-right">Remaining</th>
                        <th class="px-5 py-3 text-right">Orders</th>
                        <th class="px-5 py-3 text-right">Revenue</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Valid Until</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($auctions as $auction)
                        @php
                            $sold = (int) ($auction->sold_quantity ?? 0);
                            $remaining = $auction->stock === null ? null : max(0, $auction->stock - $sold);
                        @endphp
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4">
                                <a href="{{ route('inventory.auctions.show', $auction) }}" class="font-medium text-brand-600 hover:underline">
                                    {{ $auction->title ?: 'Auction #' . $auction->id }}
                                </a>
                                <span class="block text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($auction->token, 12) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">{{ $auction->stock ?? '∞' }}</td>
                            <td class="px-5 py-4 text-right">{{ $sold }}</td>
                            <td class="px-5 py-4 text-right">{{ $remaining === null ? '∞' : $remaining }}</td>
                            <td class="px-5 py-4 text-right">{{ $auction->orders_count }}</td>
                            <td class="px-5 py-4 text-right font-medium">{{ number_format($auction->revenue_sum ?? 0, 2) }}</td>
                            <td class="px-5 py-4">
                                @php
                                    $label = ! $auction->is_active ? 'Inactive'
                                        : ($auction->isExpired() ? 'Expired'
                                        : (($remaining !== null && $remaining <= 0) ? 'Sold out' : 'Active'));
                                @endphp
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-success-50 text-success-600 dark:bg-success-500/15' => $label === 'Active',
                                    'bg-error-50 text-error-600 dark:bg-error-500/15' => $label === 'Expired' || $label === 'Sold out',
                                    'bg-gray-100 text-gray-500 dark:bg-white/5' => $label === 'Inactive',
                                ])>{{ $label }}</span>
                            </td>
                            <td class="px-5 py-4">{{ $auction->expires_at ? $auction->expires_at->format('Y-m-d H:i') : 'Never' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" x-data
                                        @click="navigator.clipboard.writeText('{{ $auction->url() }}'); $el.textContent='Copied'; setTimeout(() => $el.textContent='Copy link', 1500)"
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">Copy link</button>
                                    <a href="{{ route('inventory.auctions.show', $auction) }}"
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">View</a>
                                    <form method="POST" action="{{ route('inventory.auctions.toggle', $auction) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">{{ $auction->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('inventory.auctions.destroy', $auction) }}" onsubmit="return confirm('Delete this auction?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-error-300 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No auctions yet. Create one to start selling.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $auctions->links() }}</div>
    </div>
@endsection
