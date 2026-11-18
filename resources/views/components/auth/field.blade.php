@props([
    'name',
    'label',
    'type' => 'text',
    'icon' => null,
    'value' => null,
    'placeholder' => null,
    'autocomplete' => 'off',
    'autofocus' => false,
    'help' => null,
])

<div>
    <label for="{{ $name }}" class="gov-label">{{ $label }}</label>

    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <x-icon :name="$icon" class="h-4 w-4" />
            </span>
        @endif

        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
               placeholder="{{ $placeholder }}" required autocomplete="{{ $autocomplete }}"
               @if ($autofocus) autofocus @endif
               {{ $attributes->merge(['class' => 'gov-input' . ($icon ? ' pl-9' : '')]) }}>
    </div>

    @if ($help)
        <p class="gov-help">{{ $help }}</p>
    @endif

    <x-input-error :messages="$errors->get($name)" class="mt-1.5 text-sm text-red-600" />
</div>
