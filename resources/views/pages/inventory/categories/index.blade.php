@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Categories" />
    @include('pages.inventory.partials.flash')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">All Categories</h3>
            <a href="{{ route('inventory.categories.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                + New Category
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Slug</th>
                        <th class="px-5 py-3">Products</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($categories as $category)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-4 font-medium text-gray-800 dark:text-white/90">{{ $category->name }}</td>
                            <td class="px-5 py-4">{{ $category->slug }}</td>
                            <td class="px-5 py-4">{{ $category->products_count }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('inventory.categories.edit', $category) }}"
                                        class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">Edit</a>
                                    <form method="POST" action="{{ route('inventory.categories.destroy', $category) }}"
                                        onsubmit="return confirm('Delete this category?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-error-300 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4">{{ $categories->links() }}</div>
    </div>
@endsection
