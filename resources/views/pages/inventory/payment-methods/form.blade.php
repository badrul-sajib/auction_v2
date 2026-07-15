<x-common.page-breadcrumb :pageTitle="$heading" />

<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if (($method_verb ?? 'POST') !== 'POST')
            @method($method_verb)
        @endif

        <x-inventory.field label="Name" name="name" :value="$method->name" required placeholder="e.g. bKash" />
        <x-inventory.field label="Instructions" name="instructions" :value="$method->instructions" textarea
            placeholder="Shown to the customer on the order page (e.g. Send money to 01XXXXXXXXX)" />

        <div class="grid grid-cols-1 gap-3">
            <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="requires_transaction" value="1" @checked(old('requires_transaction', $method->requires_transaction))
                    class="h-4 w-4 rounded border-gray-300" />
                Ask customer for a <strong>Transaction Number</strong> (bKash / Nagad / Rocket)
            </label>
            <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="requires_agent" value="1" @checked(old('requires_agent', $method->requires_agent))
                    class="h-4 w-4 rounded border-gray-300" />
                Ask customer for an <strong>Admin/Rider ID</strong> (Due / pay on delivery)
            </label>
            <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $method->is_active))
                    class="h-4 w-4 rounded border-gray-300" />
                Active (shown on order pages)
            </label>
        </div>

        <x-inventory.field label="Sort Order" name="sort_order" type="number" :value="$method->sort_order ?? 0" placeholder="0" />

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save</button>
            <a href="{{ route('inventory.payment-methods.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
        </div>
    </form>
</div>
