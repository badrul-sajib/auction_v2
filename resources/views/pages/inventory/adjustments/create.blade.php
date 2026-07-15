@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New Stock Adjustment" />

    <div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('inventory.adjustments.store') }}" class="space-y-5">
            @csrf

            <x-inventory.field label="Product" name="product_id" :value="old('product_id')" required
                :options="$products->pluck('name', 'id')" />
            <x-inventory.field label="Warehouse" name="warehouse_id" :value="old('warehouse_id')" required
                :options="$warehouses->pluck('name', 'id')" />

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <x-inventory.field label="Type" name="type" :value="old('type', 'add')" required
                    :options="['add' => 'Add (stock in)', 'remove' => 'Remove (stock out)']" />
                <x-inventory.field label="Quantity" name="quantity" type="number" :value="old('quantity')" required placeholder="e.g. 10" />
            </div>

            <x-inventory.field label="Reason" name="reason" :value="old('reason')" placeholder="e.g. Purchase, damage, correction" />

            @if ($products->isEmpty() || $warehouses->isEmpty())
                <p class="text-sm text-warning-600">You need at least one product and one warehouse first.</p>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Record Adjustment</button>
                <a href="{{ route('inventory.adjustments.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
            </div>
        </form>
    </div>
@endsection
