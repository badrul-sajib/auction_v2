@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
        class="mb-5 flex items-center justify-between rounded-lg border border-success-500 bg-success-50 px-4 py-3 text-sm text-success-600 dark:bg-success-500/10">
        <span>{{ session('status') }}</span>
        <button type="button" @click="show = false" class="text-success-600">&times;</button>
    </div>
@endif
