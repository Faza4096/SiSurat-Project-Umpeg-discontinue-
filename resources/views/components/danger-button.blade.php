<button {{ $attributes->merge(['type' => 'submit', 'class' => 'gov-btn-danger text-xs uppercase tracking-wider']) }}>
    {{ $slot }}
</button>
