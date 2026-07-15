@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Products" />
    @include('pages.inventory.partials.flash')

    @if (session('order_link'))
        <div x-data="{ link: '{{ session('order_link') }}', copied: false }"
            class="mb-5 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 dark:border-brand-500/30 dark:bg-brand-500/10">
            <p class="mb-2 text-sm font-medium text-brand-700 dark:text-brand-300">Order link ready — share it with your customer:</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly :value="link" x-ref="linkInput"
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
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">All Products</h3>
            <a href="{{ route('inventory.products.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                + New Product
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">SKU</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Merchant</th>
                        <th class="px-5 py-3 text-right">Price</th>
                        <th class="px-5 py-3 text-right">Cost</th>
                        <th class="px-5 py-3 text-right">In Stock</th>
                        <th class="px-5 py-3 text-center">Order Link</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($products as $product)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/5">
                                        @if ($product->imageUrl())
                                            <img src="{{ $product->imageUrl() }}" class="h-full w-full object-cover" alt="{{ $product->name }}" />
                                        @else
                                            <span class="flex h-full w-full items-center justify-center text-[10px] text-gray-400">—</span>
                                        @endif
                                    </span>
                                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">{{ $product->sku }}</td>
                            <td class="px-5 py-4">{{ $product->category?->name ?: '—' }}</td>
                            <td class="px-5 py-4">{{ $product->merchant?->name ?: '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                @if ($product->offer_price !== null)
                                    <span class="text-gray-400 line-through">{{ number_format($product->price, 2) }}</span>
                                    <span class="font-medium text-success-600">{{ number_format($product->offer_price, 2) }}</span>
                                @else
                                    {{ number_format($product->price, 2) }}
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right text-gray-500 dark:text-gray-400">{{ $product->cost !== null ? number_format($product->cost, 2) : '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                @php $qty = (int) ($product->total_stock ?? 0); @endphp
                                <span @class([
                                    'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    'bg-success-50 text-success-600 dark:bg-success-500/15' => $qty > 10,
                                    'bg-warning-50 text-warning-600 dark:bg-warning-500/15' => $qty > 0 && $qty <= 10,
                                    'bg-error-50 text-error-600 dark:bg-error-500/15' => $qty <= 0,
                                ])>{{ $qty }}</span>
                            </td>
                            <td class="px-5 py-4 text-center" x-data="{ open: false, validity: '7' }">
                                <button type="button" @click="open = true"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-brand-300 px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-500/40 dark:hover:bg-brand-500/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m6.656-1.828a4 4 0 000-5.656l3-3a4 4 0 015.656 5.656l-1.5 1.5" /></svg>
                                    Create
                                </button>

                                <!-- Modal -->
                                <div x-show="open" x-cloak @keydown.escape.window="open = false"
                                    class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
                                    <div @click.outside="open = false"
                                        class="w-full max-w-md rounded-2xl bg-white p-6 text-left dark:bg-gray-900">
                                        <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">Create Order Link</h4>
                                        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">{{ $product->name }}</p>

                                        <form method="POST" action="{{ route('inventory.order-links.store', $product) }}">
                                            @csrf
                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Link validity</label>
                                            <select name="validity" x-model="validity"
                                                class="mb-4 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                <option value="1">Expires in 1 day</option>
                                                <option value="7">Expires in 7 days</option>
                                                <option value="30">Expires in 30 days</option>
                                                <option value="never">Never expires</option>
                                                <option value="custom">Custom date &amp; time…</option>
                                            </select>

                                            <div x-show="validity === 'custom'" class="mb-4">
                                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Expiry date &amp; time</label>
                                                <input type="datetime-local" name="expires_at"
                                                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                            </div>

                                            <div class="flex justify-end gap-3">
                                                <button type="button" @click="open = false"
                                                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</button>
                                                <button type="submit"
                                                    class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Generate Link</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('inventory.products.edit', $product) }}"
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">Edit</a>
                                    <form method="POST" action="{{ route('inventory.products.destroy', $product) }}"
                                        onsubmit="return confirm('Delete this product?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-error-300 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No products yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $products->links() }}</div>
    </div>
@endsection
