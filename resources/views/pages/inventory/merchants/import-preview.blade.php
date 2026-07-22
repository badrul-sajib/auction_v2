@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Import Preview" />

    <form method="POST" action="{{ route('inventory.merchants.import') }}" x-data="{ rows: {{ count($rows) }} }">
        @csrf

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Review before importing</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="rows"></span> row(s). Edit any cell below. Rows without a name are skipped; existing merchants are matched by name.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.merchants.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</a>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Confirm Import</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            @foreach ($columns as $col)
                                <th class="px-3 py-3 whitespace-nowrap">{{ str_replace('_', ' ', $col) }}</th>
                            @endforeach
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($rows as $i => $row)
                            <tr class="align-top">
                                <td class="px-3 py-2 text-gray-400">{{ $i + 1 }}</td>
                                @foreach ($columns as $col)
                                    <td class="px-3 py-2">
                                        <input type="text" name="rows[{{ $i }}][{{ $col }}]" value="{{ $row[$col] }}"
                                            @class([
                                                'h-9 w-full min-w-[140px] rounded-lg border bg-transparent px-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 border-gray-300 dark:border-gray-700',
                                                'min-w-[200px]' => $col === 'address',
                                                'border-error-400' => $col === 'name' && $row[$col] === '',
                                            ]) />
                                    </td>
                                @endforeach
                                <td class="px-3 py-2">
                                    <button type="button" @click="$el.closest('tr').remove(); rows--"
                                        class="rounded-lg border border-error-300 px-2.5 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40">Remove</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>
@endsection
