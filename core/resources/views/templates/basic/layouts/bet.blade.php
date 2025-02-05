@extends($activeTemplate . 'layouts.app')
@section('content')
    <header class="header-primary">
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
    </header>
    <main class="home-page">
       @if(!request()->routeIs('home') && !request()->routeIs('events') ) 
        @include($activeTemplate . 'partials.category')
       @endif
        <div class="sports-body">
            <div class="container-fluid">
                <div class="row g-3">
                    @yield('bet')

                    {{-- Footer Start --}}
                    <div class="col-12">
                        <div class="footer footer--light">
                            @include($activeTemplate . 'partials.footer_top')
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="footer-bottom">
                            <div class="container-fluid">
                                @include($activeTemplate . 'partials.footer_bottom')
                            </div>
                        </div>
                    </div>
                    {{-- Footer End --}}

                </div>
            </div>
        </div>
        @include($activeTemplate . 'partials.bet_slip')
        @if(auth()->user())
            @include($activeTemplate . 'partials.dashboard_mobile_menu')
        @endif
    </main>
@endsection
