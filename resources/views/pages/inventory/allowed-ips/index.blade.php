@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="IP Whitelist" />
    @include('pages.inventory.partials.flash')

    <!-- Master switch -->
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">IP Whitelist</h3>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                @if ($featureEnabled)
                    <span class="font-medium text-success-600">Enabled</span> — restrictions below are enforced on customer order links.
                @else
                    <span class="font-medium text-gray-500">Disabled</span> — order links are open to everyone (the list is ignored).
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('inventory.allowed-ips.feature-toggle') }}">
            @csrf @method('PATCH')
            <button type="submit"
                @class([
                    'relative inline-flex h-7 w-12 items-center rounded-full transition-colors',
                    'bg-brand-500' => $featureEnabled,
                    'bg-gray-300 dark:bg-gray-700' => ! $featureEnabled,
                ])
                role="switch" aria-checked="{{ $featureEnabled ? 'true' : 'false' }}" title="Toggle IP whitelist">
                <span @class([
                    'inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform',
                    'translate-x-6' => $featureEnabled,
                    'translate-x-1' => ! $featureEnabled,
                ])></span>
            </button>
        </form>
    </div>

    <div class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4 text-sm dark:border-brand-500/30 dark:bg-brand-500/10">
        <p class="text-brand-700 dark:text-brand-300">
            <span class="font-medium">How it works:</span>
            when this list is <span class="font-medium">empty</span>, customer order links are open to everyone.
            Add one or more IPs/ranges and ordering is allowed <span class="font-medium">only</span> from them. The admin panel is never affected.
        </p>
        <p class="mt-1 text-brand-700 dark:text-brand-300">Your current IP: <span class="font-mono font-medium">{{ request()->ip() }}</span></p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Add form -->
        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Add IP</h3>
                <form method="POST" action="{{ route('inventory.allowed-ips.store') }}" class="space-y-4" x-data>
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">IP or CIDR range</label>
                        <input type="text" name="value" x-ref="ipInput" value="{{ old('value') }}"
                            placeholder="203.0.113.5 or 203.0.113.0/24"
                            class="h-11 w-full rounded-lg border {{ $errors->has('value') ? 'border-error-500' : 'border-gray-300 dark:border-gray-700' }} bg-transparent px-4 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90" />
                        @error('value')<p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>@enderror
                        <button type="button" @click="$refs.ipInput.value = '{{ request()->ip() }}'"
                            class="mt-2 text-xs font-medium text-brand-600 hover:underline">Use my IP ({{ request()->ip() }})</button>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Label <span class="text-error-500">*</span></label>
                        <input type="text" name="label" value="{{ old('label') }}" placeholder="e.g. Shop counter"
                            class="h-11 w-full rounded-lg border {{ $errors->has('label') ? 'border-error-500' : 'border-gray-300 dark:border-gray-700' }} bg-transparent px-4 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90" />
                        @error('label')<p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Add to Whitelist</button>
                </form>
            </div>
        </div>

        <!-- List -->
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Whitelisted IPs</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">IP / Range</th>
                                <th class="px-5 py-3">Label</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($ips as $ip)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-4 font-mono font-medium text-gray-800 dark:text-white/90">{{ $ip->value }}</td>
                                    <td class="px-5 py-4">{{ $ip->label ?: '—' }}</td>
                                    <td class="px-5 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-success-50 text-success-600 dark:bg-success-500/15' => $ip->is_active,
                                            'bg-gray-100 text-gray-500 dark:bg-white/5' => ! $ip->is_active,
                                        ])>{{ $ip->is_active ? 'Active' : 'Disabled' }}</span>
                                    </td>
                                    <td class="px-5 py-4" x-data="{ editOpen: false }">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" @click="editOpen = true"
                                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">Edit</button>
                                            <form method="POST" action="{{ route('inventory.allowed-ips.toggle', $ip) }}">
                                                @csrf @method('PATCH')
                                                <button class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">{{ $ip->is_active ? 'Disable' : 'Enable' }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('inventory.allowed-ips.destroy', $ip) }}" onsubmit="return confirm('Remove this IP?')">
                                                @csrf @method('DELETE')
                                                <button class="rounded-lg border border-error-300 px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 dark:border-error-500/40">Delete</button>
                                            </form>
                                        </div>

                                        <!-- Edit modal -->
                                        <div x-show="editOpen" x-cloak @keydown.escape.window="editOpen = false"
                                            class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
                                            <div @click.outside="editOpen = false"
                                                class="w-full max-w-md rounded-2xl bg-white p-6 text-left dark:bg-gray-900">
                                                <h4 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Edit Whitelisted IP</h4>
                                                <form method="POST" action="{{ route('inventory.allowed-ips.update', $ip) }}" class="space-y-4">
                                                    @csrf @method('PATCH')
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">IP or CIDR range</label>
                                                        <input type="text" name="value" value="{{ $ip->value }}"
                                                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                                    </div>
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Label <span class="text-error-500">*</span></label>
                                                        <input type="text" name="label" value="{{ $ip->label }}" required
                                                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="editOpen = false"
                                                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Cancel</button>
                                                        <button type="submit"
                                                            class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No IPs whitelisted — order links are open to everyone.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4">{{ $ips->links() }}</div>
            </div>
        </div>
    </div>
@endsection
