@extends('layouts.fullscreen-layout')

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gray-50 p-4 dark:bg-gray-900">
        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-error-50 text-error-600 dark:bg-error-500/15">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <h1 class="mb-2 text-xl font-semibold text-gray-800 dark:text-white/90">Link no longer available</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                This order link has expired or is no longer active. Please contact the seller for a new link.
            </p>
        </div>
    </div>
@endsection
