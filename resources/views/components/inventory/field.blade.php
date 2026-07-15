@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => '',
    'options' => null,       // for select: [value => label] or collection handled by caller
    'textarea' => false,
    'step' => null,
])

@php
    $val = old($name, $value);
    $base = 'h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
    $ok = ' border-gray-300 focus:border-brand-300 dark:border-gray-700 dark:focus:border-brand-800';
    $bad = ' border-error-500 focus:border-error-500';
    $cls = $base . ($errors->has($name) ? $bad : $ok);
@endphp

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
        {{ $label }}@if ($required)<span class="text-error-500">*</span>@endif
    </label>

    @if ($textarea)
        <textarea name="{{ $name }}" rows="3" placeholder="{{ $placeholder }}"
            class="{{ str_replace('h-11', 'min-h-[80px]', $cls) }}">{{ $val }}</textarea>
    @elseif ($options !== null)
        <select name="{{ $name }}" class="{{ $cls }}">
            <option value="">— Select —</option>
            @foreach ($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" @selected((string) $val === (string) $optValue)>{{ $optLabel }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $val }}"
            placeholder="{{ $placeholder }}" @if ($step) step="{{ $step }}" @endif
            {{ $attributes->class($cls) }} />
    @endif

    @error($name)
        <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
    @enderror
</div>
