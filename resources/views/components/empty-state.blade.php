@props(['icon' => 'receipt', 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'empty']) }}>
    <div class="empty__icon">
        <x-icon :name="$icon" />
    </div>
    <div class="empty__title">{{ $title }}</div>
    @if ($description)
        <p class="empty__text">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="empty__action">{{ $slot }}</div>
    @endif
</div>
