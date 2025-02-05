@extends($activeTemplate . 'layouts.app')
@section('content')
<header class="header-primary dark--600 user-header-primary">
    <div class="container">
        @include($activeTemplate . 'partials.header')
    </div>
    @if(request()->routeIs('casino*'))
        <div class="dark--400">
            <div class="container-fluid">
                @include($activeTemplate . 'partials.header_2')
            </div>
        </div>
    @endif
</header>
@yield('frontend')
@include($activeTemplate . 'partials.footer')
@if(auth()->user())
@include($activeTemplate . 'partials.dashboard_mobile_menu')
@endif
@endsection
