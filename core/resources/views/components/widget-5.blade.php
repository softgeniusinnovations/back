@props([
    'link' => '',
    'title' => '',
    'value' => '',
    'icon' => '',
    'color' => '',
    'bg' => '',
])

<div class="widget bb--3 border--{{ $color }} b-radius--10 bg--white box--shadow2 has--link p-4">
    <a class="item--link" href="{{ $link }}"></a>
    <div class="widget__icon b-radius--rounded bg--{{ $bg }}"><i class="{{ $icon }}"></i></div>
    <div class="widget__content">
        <p class="text-uppercase text-muted">{{ __($title) }}</p>
        <h2 class="text--{{ $color }} font-weight-bold">{{ $value }}</h2>
    </div>
    <div class="widget__arrow">
        <i class="fas fa-chevron-right"></i>
    </div>
</div>
