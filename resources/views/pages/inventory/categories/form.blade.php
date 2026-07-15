<x-common.page-breadcrumb :pageTitle="$heading" />

<div class="max-w-2xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if (($method ?? 'POST') !== 'POST')
            @method($method)
        @endif

        <x-inventory.field label="Name" name="name" :value="$category->name" required placeholder="e.g. Electronics" />
        <x-inventory.field label="Description" name="description" :value="$category->description" textarea placeholder="Optional description" />

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save</button>
            <a href="{{ route('inventory.categories.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
        </div>
    </form>
</div>
