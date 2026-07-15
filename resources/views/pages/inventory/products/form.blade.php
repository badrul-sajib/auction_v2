<x-common.page-breadcrumb :pageTitle="$heading" />

<div class="max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="POST" action="{{ $action }}" class="space-y-5" enctype="multipart/form-data">
        @csrf
        @if (($method ?? 'POST') !== 'POST')
            @method($method)
        @endif

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <x-inventory.field label="Name" name="name" :value="$product->name" required placeholder="e.g. Wireless Mouse" />
            <x-inventory.field label="SKU" name="sku" :value="$product->sku" required placeholder="e.g. WM-001" />

            <x-inventory.field label="Category" name="category_id" :value="$product->category_id"
                :options="$categories->pluck('name', 'id')" />
            <x-inventory.field label="Merchant" name="merchant_id" :value="$product->merchant_id"
                :options="$merchants->pluck('name', 'id')" />

            <x-inventory.field label="Regular Price" name="price" type="number" step="0.01" :value="$product->price" required placeholder="0.00" />
            <x-inventory.field label="Offer Price" name="offer_price" type="number" step="0.01" :value="$product->offer_price" placeholder="0.00" />
        </div>

        <x-inventory.field label="Description" name="description" :value="$product->description" textarea placeholder="Optional description" />

        @unless ($product->exists)
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                <h5 class="mb-1 text-sm font-semibold text-gray-800 dark:text-white/90">Opening Stock <span class="font-normal text-gray-400">(optional)</span></h5>
                <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">Set the initial on-hand quantity. Later changes come from Purchases, Adjustments and Transfers.</p>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <x-inventory.field label="Warehouse" name="opening_warehouse_id" :value="old('opening_warehouse_id')"
                        :options="$warehouses->pluck('name', 'id')" />
                    <x-inventory.field label="Quantity" name="opening_quantity" type="number" :value="old('opening_quantity')" placeholder="0" />
                    <x-inventory.field label="Unit Cost" name="opening_cost" type="number" step="0.01" :value="old('opening_cost')" placeholder="0.00" />
                </div>
            </div>
        @endunless

        <div x-data="{ preview: null }">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Product Image</label>

            <div class="flex items-start gap-4">
                <div class="h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/5">
                    <img x-show="preview" :src="preview" class="h-full w-full object-cover" alt="Preview" />
                    @if ($product->imageUrl())
                        <img x-show="!preview" src="{{ $product->imageUrl() }}" class="h-full w-full object-cover" alt="{{ $product->name }}" />
                    @else
                        <div x-show="!preview" class="flex h-full w-full items-center justify-center text-xs text-gray-400">No image</div>
                    @endif
                </div>

                <div class="flex-1">
                    <input type="file" name="image" accept="image/png,image/jpeg,image/jpg,image/webp"
                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                    <p class="mt-1.5 text-xs text-gray-400">PNG, JPG or WEBP up to 2 MB.</p>

                    @if ($product->image)
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300" />
                            Remove current image
                        </label>
                    @endif

                    @error('image')
                        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save</button>
            <a href="{{ route('inventory.products.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
        </div>
    </form>
</div>
