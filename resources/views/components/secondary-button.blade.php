<button {{ $attributes->merge(['type' => 'button', 'class' => 'gov-btn-outline text-xs uppercase tracking-wider']) }}>
    {{ $slot }}
</button>
