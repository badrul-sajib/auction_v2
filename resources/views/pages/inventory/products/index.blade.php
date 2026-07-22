@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Products" />
    @include('pages.inventory.partials.flash')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800" x-data="{ importOpen: false }">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">All Products</h3>
            <div class="flex items-center gap-2">
                <button type="button" @click="importOpen = true"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0-12l-4 4m4-4l4 4" /></svg>
                    Import
                </button>
                <a href="{{ route('inventory.products.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    + New Product
                </a>
            </div>

            <!-- Import modal -->
            <div x-show="importOpen" x-cloak @keydown.escape.window="importOpen = false"
                class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
                <div @click.outside="importOpen = false"
                    class="w-full max-w-md rounded-2xl bg-white p-6 text-left dark:bg-gray-900">
                    <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">Bulk Import Products</h4>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Upload a CSV file. Existing SKUs are updated; new ones are created.</p>

                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600 dark:border-gray-800 dark:bg-white/[0.02] dark:text-gray-400">
                        Columns: <code>name, sku, category, merchant, price, offer_price, description, image, opening_stock, warehouse</code>.
                        The <code>image</code> column takes a public image URL (jpg/png/webp). <code>opening_stock</code> is only applied to newly created products.
                        <a href="{{ route('inventory.products.import.sample') }}" class="mt-1 inline-block font-medium text-brand-600 hover:underline">↓ Download demo CSV</a>
                    </div>

                    <form method="POST" action="{{ route('inventory.products.import.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" accept=".csv,text/csv" required
                            class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                        @error('file')<p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>@enderror

                        <div class="mt-5 flex justify-end gap-3">
                            <button type="button" @click="importOpen = false"
                                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</button>
                            <button type="submit"
                                class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Preview</button>
                        </div>
                    </form>
                </div>
            </div>
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
                        <th class="px-5 py-3 text-center">Auction</th>
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
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('inventory.auctions.index', $product) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-brand-300 px-3 py-1.5 text-xs font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-500/40 dark:hover:bg-brand-500/10">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    View
                                </a>
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
