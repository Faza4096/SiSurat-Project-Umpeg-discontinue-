@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'gov-input']) }}>
