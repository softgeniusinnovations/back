@extends($activeTemplate . 'layouts.app')
@section('content')
@if(Auth::user()->is_affiliate != 1 || Auth::user()->profile_mode == 'better')
<div class="dark--600">
    <div class="container-fluid">
        @include($activeTemplate . 'partials.header')
    </div>
</div>
<div class="dark--400">
    <div class="container-fluid">
        @include($activeTemplate . 'partials.header_2')
    </div>
</div>

@elseif(Auth::user()->is_affiliate == 1)
@include($activeTemplate . 'partials.user_header')
{{-- <div class="dark--400">
    <div class="container-fluid">
        @include($activeTemplate . 'partials.header_2')
    </div>
</div> --}}
@endif

@if(request()->routeIs('casino.game.open'))
<div class="dark--400">
    <div class="container-fluid">
        @include($activeTemplate . 'partials.header_2')
    </div>
</div>
@endif


<div class="user-dashboard">
    <div class="container">
        <div class="row">
                @if(auth()->user()->is_affiliate == 1 && auth()->user()->profile_mode == 'affiliate' && !request()->routeIs('casino.game.open'))
                @include($activeTemplate . 'partials.affiliate.dashboard_sidebar')
                    <div class="col-lg-8 col-xl-9 ps-lg-5">
                        @yield('master')
                    </div>
                {{-- @elseif(auth()->user()->is_affiliate == 1 && auth()->user()->profile_mode == 'better')
                        @include($activeTemplate . 'partials.dashboard_sidebar')
                        @else
                        @include($activeTemplate . 'partials.dashboard_sidebar') --}}
                @else
                    <div class="col-lg-12 col-xl-12 ps-lg-15">
                        @yield('master')
                    </div>
                @endif

        </div>
    </div>
</div>
@include($activeTemplate . 'partials.footer')
@if(auth()->user())
@include($activeTemplate . 'partials.dashboard_mobile_menu')
@endif
@endsection
@push('script')
<script>
    (function($) {
        "use strict";
        $('.showFilterBtn').on('click', function() {
            $('.responsive-filter-card').slideToggle();
        });
    })(jQuery)

</script>
@endpush
