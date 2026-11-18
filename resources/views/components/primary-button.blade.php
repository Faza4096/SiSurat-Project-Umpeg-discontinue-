<button {{ $attributes->merge(['type' => 'submit', 'class' => 'gov-btn text-xs uppercase tracking-wider']) }}>
    {{ $slot }}
</button>
