@props(['value'])

<label {{ $attributes->merge(['class' => 'gov-label']) }}>
    {{ $value ?? $slot }}
</label>
