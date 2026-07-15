@extends('layouts.fullscreen-layout')

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gray-50 p-4 dark:bg-gray-900">
        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/15">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h1 class="mb-2 text-xl font-semibold text-gray-800 dark:text-white/90">Order placed!</h1>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                Thank you, {{ $order->customer_name }}. We've received your order for
                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $order->quantity }} × {{ $product->name }}</span>.
                We'll contact you at {{ $order->customer_phone }} shortly.
            </p>
            <div class="rounded-lg bg-gray-50 px-4 py-3 text-left text-sm dark:bg-white/[0.02]">
                <div class="flex justify-between py-1"><span class="text-gray-500 dark:text-gray-400">Order #</span><span class="font-medium text-gray-800 dark:text-white/90">{{ $order->id }}</span></div>
                <div class="flex justify-between py-1"><span class="text-gray-500 dark:text-gray-400">Total</span><span class="font-medium text-gray-800 dark:text-white/90">{{ number_format($order->total, 2) }}</span></div>
            </div>
        </div>
    </div>
@endsection
