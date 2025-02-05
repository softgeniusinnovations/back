<div class="app-nav">
    <div class="container-fluid">
        <div class="row g-0">
            <div class="col-12">
                <ul class="app-nav__menu list list--row justify-content-between align-items-center">
                    <li>
                        <a class="app-nav__menu-link active" href="{{ route('home') }}">
                            <span class="app-nav__menu-icon">
                                <img src="{{ asset($activeTemplateTrue . 'images/bet-now.png') }}" alt="@lang('image')">
                            </span>
                            <span class="app-nav__menu-text"> @lang('Bet Now') </span>
                        </a>
                    </li>
                    <li>
                        <a class="app-nav__menu-link" href="{{ route('user.bets') }}">
                            <span class="app-nav__menu-icon">
                                <img src="{{ asset($activeTemplateTrue . 'images/my_bets.png') }}" alt="@lang('image')">
                            </span>
                            <span class="app-nav__menu-text"> @lang('My Bets') </span>
                        </a>
                    </li>
                    <li class="app-nav__menu-link-important-container">
                        <a class="app-nav__menu-link-important" href="javascript:void(0)">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                    <li>
                        <a class="app-nav__menu-link open-betslip header-button" href="javascript:void(0)">
                            <span class="bet-count">{{ collect(session('bets'))->count() }}</span>
                            <span class="app-nav__menu-icon">
                                <i class="fa-thin fa-clipboard-list-check"></i>
                            </span>
                            <span class="app-nav__menu-text">@lang('Bet Slip')</span>
                        </a>
                    </li>
                    <li>
                        @auth
                            <a class="app-nav__menu-link" href="{{ route('user.home') }}">
                                <span class="app-nav__menu-icon">
                                    <img src="{{ asset($activeTemplateTrue . 'images/user.png') }}" alt="@lang('image')">
                                </span>
                                <span class="app-nav__menu-text"> @lang('Dashboard') </span>
                            </a>
                        @else
                            <a class="app-nav__menu-link" @if (request()->routeIs('user.login')) href="{{ route('user.login') }}" @else  href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginModal" @endif>
                                <span class="app-nav__menu-icon">
                                    <img src="{{ asset($activeTemplateTrue . 'images/user.png') }}" alt="@lang('image')">
                                </span>
                                <span class="app-nav__menu-text"> @lang('Login') </span>
                            </a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="app-nav__drawer" data-simplebar>
        <ul class="list app-nav__drawer-list">
            <li>
                <a class="app-nav__drawer-link" href="{{ route('home') }}">
                    <span class="app-nav__drawer-icon">
                        <i class="las la-home"></i>
                    </span>
                    <span class="app-nav__drawer-text"> @lang('Home') </span>
                </a>
            </li>
            <li>
                <a class="app-nav__drawer-link" href="{{ route('blog') }}">
                    <span class="app-nav__drawer-icon">
                        <i class="las la-newspaper"></i>
                    </span>
                    <span class="app-nav__drawer-text"> @lang('News & Updates') </span>
                </a>
            </li>
            <li>
                <a class="app-nav__drawer-link" href="{{ route('contact') }}">
                    <span class="app-nav__drawer-icon">
                        <i class="las la-headset"></i>
                    </span>
                    <span class="app-nav__drawer-text"> @lang('Contact') </span>
                </a>
            </li>
            <li>
                <div class="select-lang--container">
                    <div class="select-lang">
                        <span class="select-lang__icon">
                            <i class="las la-percent"></i>
                        </span>
                        <select class="form-select oddsType">
                            <option value="" disabled>@lang('Select Odds Type')</option>
                            <option value="decimal" @selected(session('odds_type') == 'decimal')>@lang('Decimal')</option>
                            <option value="fraction" @selected(session('odds_type') == 'fraction')>@lang('Fraction')</option>
                            <option value="american" @selected(session('odds_type') == 'american')>@lang('American Odds')</option>
                        </select>
                    </div>
                </div>
            </li>
            @php
                $language = App\Models\Language::all();
            @endphp
            @if (!blank($language))
                <li>
                    <div class="select-lang--container">
                        <div class="select-lang">
                            <span class="select-lang__icon">
                                <i class="fas fa-globe"></i>
                            </span>
                            <select class="form-select langSel">
                                @foreach ($language as $item)
                                    <option value="{{ $item->code }}" @if (session('lang') == $item->code) selected @endif>{{ __($item->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </li>
            @endif
        </ul>
    </div>
</div>
