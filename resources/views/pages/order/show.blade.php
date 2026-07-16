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
                        @php
                            // Use the auction's remaining stock if it's capped, otherwise the product's on-hand stock.
                            $available = $auction->remainingStock() ?? $product->totalStock();
                        @endphp
                        <div class="flex items-start justify-between gap-2">
                            <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</h1>
                            <span @class([
                                'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium',
                                'bg-success-50 text-success-600 dark:bg-success-500/15' => $available > 5,
                                'bg-warning-50 text-warning-600 dark:bg-warning-500/15' => $available > 0 && $available <= 5,
                                'bg-error-50 text-error-600 dark:bg-error-500/15' => $available <= 0,
                            ])>{{ $available > 0 ? $available . ' available' : 'Sold out' }}</span>
                        </div>
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
                <form method="POST" action="{{ route('order.store', $auction->token) }}" class="space-y-4 p-5"
                    x-data="{
                        qty: {{ (int) old('quantity', 1) }},
                        unit: {{ $product->currentPrice() }},
                        method: '{{ old('payment_method_id') }}',
                        agentType: '{{ old('agent_type', 'admin') }}',
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
                        <input type="number" name="quantity" min="1" @if ($available > 0) max="{{ $available }}" @endif x-model.number="qty" required
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
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Collected by</label>
                            <div class="mb-3 flex gap-3">
                                <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700"
                                    :class="agentType === 'admin' ? 'border-brand-500 ring-2 ring-brand-500/20' : ''">
                                    <input type="radio" name="agent_type" value="admin" x-model="agentType" class="text-brand-500" />
                                    Admin
                                </label>
                                <label class="flex flex-1 cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700"
                                    :class="agentType === 'rider' ? 'border-brand-500 ring-2 ring-brand-500/20' : ''">
                                    <input type="radio" name="agent_type" value="rider" x-model="agentType" class="text-brand-500" />
                                    Rider
                                </label>
                            </div>

                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                x-text="(agentType === 'rider' ? 'Rider' : 'Admin') + ' ID'"></label>
                            <input type="text" name="agent_id" value="{{ old('agent_id') }}"
                                :placeholder="'ID of the ' + (agentType === 'rider' ? 'rider' : 'admin') + ' collecting payment'"
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

            @php $remaining = $auction->remainingStock(); @endphp
            @if ($remaining !== null)
                <p class="mt-4 text-center text-xs text-gray-400">{{ $remaining }} unit(s) left in this auction.</p>
            @endif
            @if ($auction->expires_at)
                <p class="mt-1 text-center text-xs text-gray-400">Valid until {{ $auction->expires_at->format('M j, Y g:i A') }}.</p>
            @endif
        </div>
    </div>
@endsection
