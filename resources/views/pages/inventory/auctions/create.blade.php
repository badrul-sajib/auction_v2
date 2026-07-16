@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Create Auction" />

    <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{ validity: '{{ old('validity', '7') }}', unlimited: {{ old('stock') ? 'false' : 'true' }} }">
        <div class="mb-5 flex items-center gap-3">
            <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/5">
                @if ($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" class="h-full w-full object-cover" alt="{{ $product->name }}" />
                @endif
            </div>
            <div>
                <p class="font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Price: {{ number_format($product->currentPrice(), 2) }}
                    @if ($product->offer_price !== null)<span class="ml-1 text-xs">(offer)</span>@endif
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('inventory.auctions.store', $product) }}" class="space-y-5">
            @csrf

            <x-inventory.field label="Title (optional)" name="title" :value="old('title')" placeholder="e.g. Eid Special Auction" />

            <div x-data="{ stock: '{{ old('stock') }}' }">
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">Stock for this auction</label>
                    <span @class([
                        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                        'bg-success-50 text-success-600 dark:bg-success-500/15' => $availableStock > 0,
                        'bg-error-50 text-error-600 dark:bg-error-500/15' => $availableStock <= 0,
                    ])>In stock: {{ $availableStock }}</span>
                </div>

                <label class="mb-2 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <input type="checkbox" x-model="unlimited" class="h-4 w-4 rounded border-gray-300" />
                    Unlimited (no stock cap)
                </label>

                <div class="flex items-center gap-2">
                    <input type="number" name="stock" min="1" max="{{ $availableStock }}" x-model="stock" x-bind:disabled="unlimited"
                        placeholder="e.g. {{ min(50, max(1, $availableStock)) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 disabled:opacity-40 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    @if ($availableStock > 0)
                        <button type="button" x-show="!unlimited" @click="stock = {{ $availableStock }}"
                            class="h-11 shrink-0 rounded-lg border border-gray-300 px-3 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Use all ({{ $availableStock }})</button>
                    @endif
                </div>
                @error('stock')<p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-400">Choose from the {{ $availableStock }} unit(s) on hand. Orders can't exceed the auction's stock.</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Validity</label>
                <select name="validity" x-model="validity"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="1">Expires in 1 day</option>
                    <option value="7">Expires in 7 days</option>
                    <option value="30">Expires in 30 days</option>
                    <option value="never">Never expires</option>
                    <option value="custom">Custom date &amp; time…</option>
                </select>
                @error('validity')<p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div x-show="validity === 'custom'" x-cloak>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Expiry date &amp; time</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                @error('expires_at')<p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Create Auction</button>
                <a href="{{ route('inventory.auctions.index', $product) }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
            </div>
        </form>
    </div>
@endsection
