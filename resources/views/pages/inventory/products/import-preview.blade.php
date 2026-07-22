@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Import Preview" />

    <form method="POST" action="{{ route('inventory.products.import') }}" enctype="multipart/form-data"
        x-data="{ rows: {{ count($rows) }} }">
        @csrf

        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Review before importing</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <span x-text="rows"></span> row(s). Edit any cell below. Rows missing name, SKU or a numeric price are skipped.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.products.index') }}"
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
                            <tr class="align-top" x-show="true">
                                <td class="px-3 py-2 text-gray-400">{{ $i + 1 }}</td>
                                @foreach ($columns as $col)
                                    <td class="px-3 py-2">
                                        @if (isset($options[$col]))
                                            @php
                                                $opts = collect($options[$col]);
                                                $current = $row[$col];
                                                $isNew = $current !== '' && ! $opts->contains($current);
                                                $nullable = in_array($col, ['category', 'merchant']);
                                            @endphp
                                            <select name="rows[{{ $i }}][{{ $col }}]"
                                                class="h-9 w-full min-w-[150px] rounded-lg border border-gray-300 bg-transparent px-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                @if ($nullable)
                                                    <option value="" @selected($current === '')>— none —</option>
                                                @endif
                                                @if ($isNew)
                                                    <option value="{{ $current }}" selected>{{ $current }} (new)</option>
                                                @endif
                                                @foreach ($opts as $opt)
                                                    <option value="{{ $opt }}" @selected($current === $opt)>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @elseif ($col === 'image')
                                            <div class="min-w-[220px] space-y-1.5" x-data="{ preview: null }">
                                                <input type="text" name="rows[{{ $i }}][image]" value="{{ $row['image'] }}"
                                                    placeholder="Image URL (optional)"
                                                    class="h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                                <div class="flex items-center gap-2">
                                                    <template x-if="preview">
                                                        <img :src="preview" class="h-9 w-9 shrink-0 rounded object-cover" alt="" />
                                                    </template>
                                                    <input type="file" name="rows[{{ $i }}][image_file]" accept="image/png,image/jpeg,image/jpg,image/webp"
                                                        @change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                                                        class="block w-full text-xs text-gray-500 file:mr-2 file:rounded file:border-0 file:bg-gray-100 file:px-2 file:py-1 file:text-xs file:font-medium file:text-gray-700 dark:file:bg-white/10 dark:file:text-gray-300 dark:text-gray-400" />
                                                </div>
                                                <p class="text-[10px] text-gray-400">Upload overrides the URL.</p>
                                                @error("rows.{$i}.image_file")<p class="text-[10px] text-error-500">{{ $message }}</p>@enderror
                                            </div>
                                        @else
                                            <input type="text" name="rows[{{ $i }}][{{ $col }}]" value="{{ $row[$col] }}"
                                                @class([
                                                    'h-9 w-full min-w-[110px] rounded-lg border bg-transparent px-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 border-gray-300 dark:border-gray-700',
                                                    'min-w-[200px]' => $col === 'description',
                                                    'border-error-400' => in_array($col, ['name', 'sku']) && $row[$col] === '',
                                                ]) />
                                        @endif
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
