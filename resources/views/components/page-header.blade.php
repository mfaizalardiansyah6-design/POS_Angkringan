@props(['title', 'eyebrow' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'page-head reveal']) }}>
    <div>
        @if ($eyebrow)
            <div class="eyebrow">{{ $eyebrow }}</div>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if ($description)
            <p class="page-sub">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="page-head__actions">{{ $actions }}</div>
    @endisset
</div>
