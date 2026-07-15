@extends('layouts.fullscreen-layout')

@section('content')
    <div class="min-h-screen bg-gray-50 py-10 dark:bg-gray-900">
        <div class="mx-auto max-w-lg px-4">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <!-- Product -->
                <div class="flex gap-4 border-b border-gray-100 p-5 dark:border-gray-800">
                    <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-white/5">
                        @if ($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" class="h-full w-full object-cover" alt="{{ $product->name }}" />
                        @endif
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</h1>
                        <div class="mt-1 flex items-center gap-2">
                            @if ($product->offer_price !== null)
                                <span class="text-gray-400 line-through">{{ number_format($product->price, 2) }}</span>
                                <span class="text-lg font-semibold text-success-600">{{ number_format($product->offer_price, 2) }}</span>
                            @else
                                <span class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>
                        @if ($product->description)
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $product->description }}</p>
                        @endif
                    </div>
                </div>

                <!-- Order form -->
                <form method="POST" action="{{ route('order.store', $link->token) }}" class="space-y-4 p-5"
                    x-data="{
                        qty: {{ (int) old('quantity', 1) }},
                        unit: {{ $product->currentPrice() }},
                        method: '{{ old('payment_method_id') }}',
                        methods: {{ Illuminate\Support\Js::from($paymentMethods->mapWithKeys(fn ($m) => [$m->id => ['requires_transaction' => $m->requires_transaction, 'requires_agent' => $m->requires_agent, 'instructions' => $m->instructions]])) }},
                        get selected() { return this.methods[this.method] || null; }
                    }">
                    @csrf
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Place your order</h2>

                    @if ($errors->any())
                        <div class="rounded-lg border border-error-300 bg-error-50 px-4 py-3 text-sm text-error-600 dark:bg-error-500/10">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Your Name</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Delivery Address</label>
                        <textarea name="customer_address" rows="2"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('customer_address') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Quantity</label>
                        <input type="number" name="quantity" min="1" x-model.number="qty" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment Method</label>
                        <select name="payment_method_id" x-model="method" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">— Select payment method —</option>
                            @foreach ($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>

                        <p x-show="selected && selected.instructions" x-cloak x-text="selected?.instructions"
                            class="mt-1.5 text-xs text-gray-500 dark:text-gray-400"></p>

                        <div x-show="selected && selected.requires_transaction" x-cloak class="mt-3">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Transaction Number</label>
                            <input type="text" name="transaction_number" value="{{ old('transaction_number') }}"
                                placeholder="e.g. 8N7A6B5C4D"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>

                        <div x-show="selected && selected.requires_agent" x-cloak class="mt-3">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Admin / Rider ID</label>
                            <input type="text" name="agent_id" value="{{ old('agent_id') }}"
                                placeholder="ID of the person collecting payment"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total</span>
                        <span class="text-lg font-semibold text-gray-800 dark:text-white/90"
                            x-text="(qty > 0 ? (qty * unit).toFixed(2) : '0.00')"></span>
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white hover:bg-brand-600">
                        Confirm Order
                    </button>
                </form>
            </div>

            @if ($link->expires_at)
                <p class="mt-4 text-center text-xs text-gray-400">This order link is valid until {{ $link->expires_at->format('M j, Y g:i A') }}.</p>
            @endif
        </div>
    </div>
@endsection
