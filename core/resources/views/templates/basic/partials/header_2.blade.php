<div class="header-fluid-custom-parent">
    <nav class="primary-menu-container">

        <ul class="list list--row primary-menu-lg_1 justify-content-end justify-content-lg-start desktop_display_login">
            {{-- @if (Route::is('home') || Route::is('game.markets') || Route::is('league.games') || Route::is('category.games') || Route::is('home.sports')  || Route::is('home.demo')) --}}
            <li>
            <a class="text-light" href="{{ route('home') }}"><i class="fas fa-house"></i> @lang('Home') </a>
            </li>
            {{-- <li>
                <a class="text-light @if (session('game_type') != 'upcoming') active @endif" href="{{ route('switch.type', 'live') }}"><i class="fas fa-spinner fa-pulse"></i>  @lang('Live') </a>
            </li> 
            <li>
                <a class="text-light @if (session('game_type') == 'upcoming') active @endif" href="{{ route('switch.type', 'upcoming') }}"><i class="fas fa-dice-d20"></i>  @lang('Upcoming') </a>
            </li> --}}
            <li>
                <a class="text-light" href="{{ route('live.game') }}"><i class="fas fa-spinner fa-pulse"></i>  @lang('Live') </a>
            </li>
            <li>
                <a class="text-light" href="{{ route('upcomming.game') }}"><i class="fas fa-dice-d20"></i>  @lang('Upcoming') </a>
            </li>
            <li>
                <a class="text-light {{ request()->routeIs('home.sports') ? 'active' : '' }}" href="{{route('home.sports')}}" ><i class="fas fa-futbol"></i>  @lang('Sports') </a>
            </li>
            <li>
                <a class="text-light" href="{{ route('events') }}"><i class="fas fa-gift"></i>  @lang('Promotions') </a>
            </li>
            <li>
                <a class="text-light" href="{{route('home.demo')}}"><i class="fas fa-dice"></i>  @lang('Casino') </a>
            </li>
            <li>
                <a class="text-light" href="{{route('casino.live')}}"><i class="fas fa-satellite-dish"></i>  @lang('Live Casino') </a>
            </li>
            <li>
                <a class="text-light" href="#"><i class="fas fa-dice-five"></i>  @lang('Games') </a>
            </li>
            <li>
                <a class="text-light" href="#"><i class="fas fa-chart-bar"></i>  @lang('Aviator') </a>
            </li>
            <li>
                <a class="text-light" href="#"><i class="fas fa-tv"></i>  @lang('Live TV Game') </a>
            </li>
            {{-- @endif --}}
        </ul>

        {{-- <ul class="list list--row primary-menu-lg justify-content-end justify-content-lg-start mobile_display_login">
            @auth
            <li>
                <button type="button" class="btn px-1 py-0  btn-sm">
                    <i class="fas fa-search text-light"></i>
                </button>
            </li>
            <li>
                <h6 class="p-0 mt-1 m-0 text-light">
                <a class="text-light" href="{{ route('user.deposit.index') }}">+{{ showAmount(auth()->user()->balance) }} </a>   
                </h6>
            </li>
            @else
            @if (Route::is('home') || Route::is('game.markets') || Route::is('league.games') || Route::is('category.games'))
            <li>
                <button class="btn btn--login" data-bs-toggle="modal" data-bs-target="#loginModal" type="button"> @lang('Login') </button>
            </li>
            <li>
                <button type="button" class="btn btn--signup" data-bs-toggle="modal" data-bs-target="#registerModal">
                    @lang('Registration')
                </button>
            </li>
            @endif
            @endauth
        </ul> --}}

        <ul class="list list--row primary-menu justify-content-end align-items-center right-side-nav gap-4">

           {{-- @if (!Route::is('home'))
            <li>
                <a class="primary-menu-lg__link" href="{{ route('home') }}">
                    <span class="primary-menu-lg__link-text"> @lang('Home') </span>
                </a>
            </li>
            @endif

            <li class="d-none d-lg-block">
                <div class="select-lang--container">
                    <div class="select-lang">
                        <select class="form-select oddsType">
                            <option value="" disabled>@lang('Odds Type')</option>
                            <option value="decimal" @selected(session('odds_type')=='decimal' )>@lang('Decimal Odds')</option>
                            <option value="fraction" @selected(session('odds_type')=='fraction' )>@lang('Fraction Odds')</option>
                            <option value="american" @selected(session('odds_type')=='american' )>@lang('American Odds')</option>
                        </select>
                    </div>
                </div>
            </li> --}}
            <li>
            <a class="text-light" href="#"><i class="fas fa-bars"></i>  @lang('MORE') </a>
            </li>

        </ul>
    </nav>
</div>

