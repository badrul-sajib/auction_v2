@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New Purchase" />

    <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        x-data="{
            quantity: {{ (int) old('quantity', 0) }},
            total: {{ (float) old('total_cost', 0) }},
            get unitCost() {
                return this.quantity > 0 ? (this.total / this.quantity).toFixed(2) : '0.00';
            }
        }">
        <form method="POST" action="{{ route('inventory.purchases.store') }}" class="space-y-5">
            @csrf

            <x-inventory.field label="Product" name="product_id" :value="old('product_id')" required
                :options="$products->pluck('name', 'id')" />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-inventory.field label="Merchant" name="merchant_id" :value="old('merchant_id')"
                    :options="$merchants->pluck('name', 'id')" />
                <x-inventory.field label="Warehouse" name="warehouse_id" :value="old('warehouse_id')" required
                    :options="$warehouses->pluck('name', 'id')" />
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                <x-inventory.field label="Quantity" name="quantity" type="number" :value="old('quantity')" required placeholder="e.g. 50"
                    x-model.number="quantity" />
                <x-inventory.field label="Total Cost" name="total_cost" type="number" step="0.01" :value="old('total_cost')" required placeholder="0.00"
                    x-model.number="total" />
                <x-inventory.field label="Purchase Date" name="purchased_at" type="date" :value="old('purchased_at', now()->toDateString())" />
            </div>

            <x-inventory.field label="Note" name="note" :value="old('note')" placeholder="Optional note / reference" />

            @if ($products->isEmpty() || $warehouses->isEmpty())
                <p class="text-sm text-warning-600">You need at least one product and one warehouse first.</p>
            @endif

            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 text-xs text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                <span>Adds the quantity to warehouse stock and sets the product's cost to the calculated unit cost.</span>
                <span class="ml-4 shrink-0 font-medium text-gray-700 dark:text-gray-300">
                    Unit cost: <span x-text="unitCost"></span>
                </span>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Record Purchase</button>
                <a href="{{ route('inventory.purchases.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
            </div>
        </form>
    </div>
@endsection
