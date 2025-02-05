@props([
    'link' => '',
    'icon' => '',
    'amount' => '',
    'title' => '',
])

<a class="widget-card widget-card--secondary h-100" href="{{ $link }}">
    <div class="widget-card__body">
        <h5 class="widget-card__balance text-center {{ $amount < 0 ? 'text-danger' : '' }}">{{ $amount }}</h5>
        <span class="widget-card__balance-text fw-bold text-center"><i class="{{ $icon }}"></i> {{ __($title) }}</span>
    </div>
</a>
