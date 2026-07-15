<x-common.page-breadcrumb :pageTitle="$heading" />

<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if (($method ?? 'POST') !== 'POST')
            @method($method)
        @endif

        <x-inventory.field label="Name" name="name" :value="$merchant->name" required placeholder="e.g. Acme Trading" />
        <x-inventory.field label="Email" name="email" type="email" :value="$merchant->email" placeholder="contact@example.com" />
        <x-inventory.field label="Phone" name="phone" :value="$merchant->phone" placeholder="+1 555 0100" />
        <x-inventory.field label="Address" name="address" :value="$merchant->address" placeholder="Street, City" />

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save</button>
            <a href="{{ route('inventory.merchants.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
        </div>
    </form>
</div>
