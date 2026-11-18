@props(['label', 'icon' => 'check'])

<button type="submit" {{ $attributes->merge(['class' => 'gov-btn w-full']) }}>
    <x-icon :name="$icon" class="h-4 w-4" /> {{ $label }}
</button>
