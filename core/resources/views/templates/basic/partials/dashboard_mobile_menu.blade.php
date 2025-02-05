<div class="app-nav">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                @if(auth()->user()->is_affiliate == 1 && auth()->user()->profile_mode == 'affiliate')
                    <ul class="app-nav__menu list list--row justify-content-between align-items-center">
                        <li>
                            <a class="app-nav__menu-link active" href="{{ route('user.promotions.index') }}">
                                <span class="app-nav__menu-icon">
                                    <i class="fas fa-trophy text-light"></i>
                                </span>
                                <span class="app-nav__menu-text"> @lang('Promo') </span>
                            </a>
                        </li>
                        <li>
                            <a class="app-nav__menu-link" href="{{ route('affiliate.report.summery') }}">
                                <span class="app-nav__menu-icon">
                                    <i class="las la-dice"></i>
                                </span>
                                <span class="app-nav__menu-text"> @lang('Summery') </span>
                            </a>
                        </li>

                        <li>
                            <a class="app-nav__menu-link" href="{{ route('affiliate.report.fullreport') }}">
                                <span class="app-nav__menu-icon">
                                    <i class="las la-sticky-note"></i>
                                </span>
                                <span class="app-nav__menu-text"> @lang('Full Report') </span>
                            </a>
                        </li>

                        <li>
                            <a class="app-nav__menu-link" href="{{ route('user.withdraw') }}">
                                <span class="app-nav__menu-icon">
                                    <i class="las la-coins"></i>
                                </span>
                                <span class="app-nav__menu-text"> @lang('Withdraw') </span>
                            </a>
                        </li>

                        <li>
                            <a class="app-nav__menu-link app-nav__menu-link-important" href="javascript:void(0)">
                                <span class="app-nav__menu-icon">
                                    <i class="fas fa-bars"></i>
                                </span>
                                <span class="app-nav__menu-text">@lang('Menu')</span>
                            </a>
                        </li>

                    </ul>
                @elseif(auth()->user()->is_affiliate != 1 || auth()->user()->profile_mode == 'better')
                    <ul class="app-nav__menu list list--row justify-content-between align-items-center">
                            <li>
                                <a class="app-nav__menu-link active" href="{{ route('home') }}">
                                    <span class="app-nav__menu-icon">
                                        <i class="fas fa-trophy text-light"></i>
                                    </span>
                                    <span class="app-nav__menu-text"> @lang('Sports') </span>
                                </a>
                            </li>
                            <li>
                                <a class="app-nav__menu-link" href="{{ route('user.deposit.index') }}">
                                    <span class="app-nav__menu-icon">
                                        <i class="las la-dice"></i>
                                    </span>
                                    <span class="app-nav__menu-text"> @lang('Casino') </span>
                                </a>
                            </li>

                            <li>
                                <a class="app-nav__menu-link open-betslip header-button" href="javascript:void(0)">
                                    <span class="bet-count">{{ collect(session('bets'))->count() }}</span>
                                    <span class="app-nav__menu-icon">
                                        <i class="las la-sticky-note"></i>
                                    </span>
                                    <span class="app-nav__menu-text"> @lang('Bet Slip') </span>
                                </a>
                            </li>

                            <li>
                                <a class="app-nav__menu-link" href="{{ route('home') }}">
                                    <span class="app-nav__menu-icon">
                                        <i class="las la-coins"></i>
                                    </span>
                                    <span class="app-nav__menu-text"> @lang('Bet Now') </span>
                                </a>
                            </li>

                            <li>
                                <a class="app-nav__menu-link app-nav__menu-link-important" href="javascript:void(0)">
                                    <span class="app-nav__menu-icon">
                                        <i class="fas fa-bars"></i>
                                    </span>
                                    <span class="app-nav__menu-text">@lang('Menu')</span>
                                </a>
                            </li>

                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="app-nav__drawer dashboard-menu__body" data-simplebar>

        <section class="d-flex justify-content-between pt-4 mb-2">
            <div>
                <li class="text-white d-lg-none d-block"><a class="text-light" href="{{ route('user.profile.setting') }}"><i class="far fa-user-circle fa-xl"></i> {{ optional(auth()->user())->user_id }}</a></li>
            </div>
            <div class="d-flex">
                <div class="dropdown">
                    <button class="btn p-0 m-0 btn-sm dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell text-light fa-xl"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        {{-- <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li> --}}
                    </ul>
                </div>


                <div class="dropdown">
                    <button class="btn p-0 m-0 btn-sm dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog text-light fa-xl"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                        <li><a class="dropdown-item {{ menuActive('user.change.password', 4) }}" href="{{ route('user.change.password') }}">Change Password</a></li>
                        @if ($general->multi_language)
                        @php
                        $language = App\Models\Language::all();
                        @endphp
                        <li class="d-none d-lg-block">
                            <div>
                                <select class="border-0 p-0 m-0 langSel">
                                    @foreach ($language as $item)
                                    <option value="{{ $item->code }}" @if (session('lang')==$item->code) selected @endif>
                                        {{ __($item->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </li>
                        @endif
                        <li><a class="dropdown-item {{ menuActive('user.twofactor', 4) }}" href="{{ route('user.twofactor') }}">Authenticator (2FA)</a></li>
                        <li>
                            <div class="dropdown-item">
                                <select class="oddsType  border-0 p-0 m-0">
                                    <option value="" disabled>@lang('Odds Type')</option>
                                    <option value="decimal" @selected(session('odds_type')=='decimal' )>@lang('Decimal Odds')</option>
                                    <option value="fraction" @selected(session('odds_type')=='fraction' )>@lang('Fraction Odds')</option>
                                    <option value="american" @selected(session('odds_type')=='american' )>@lang('American Odds')</option>
                                </select>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('policy.pages', [slug(__(@$policy->data_values->title)), 3]) }}">Security & Privacy</a></li>
                        <li><a class="dropdown-item" href="{{ route('user.logout') }}">Logout</a></li>
                    </ul>
                </div>
            </div>
        </section>

        <section>
            <div class="widget-card widget-card--primary">
                <div class="widget-card__body">
                    <div class="d-flex justify-content-between">
                        
                        @if(auth()->user()->is_affiliate != 1 || auth()->user()->profile_mode == "better")
                        <div class="">
                            <h5 class="widget-card__balance">
                                {{ __(userCurrency()) }} {{ showAmount(auth()->user()->balance) }}</h5>
                            <span class="widget-card__balance-text">@lang('Current Balance')</span>
                        </div>
                        <div class="">
                            <h5 class="widget-card__balance">
                                {{ __(userCurrency()) }} {{ showAmount(auth()->user()->bonus_account) }}</h5>
                            <span class="widget-card__balance-text">@lang('Bonus Balance')</span>
                        </div>
                        @elseif(auth()->user()->is_affiliate == 1 || auth()->user()->profile_mode == "affiliate")
                        <div class="">
                            <h5 class="widget-card__balance">
                                {{ __(userCurrency()) }} {{ showAmount(auth()->user()->affiliate_balance) }}</h5>
                            <span class="widget-card__balance-text">@lang('Current Balance')</span>
                        </div>
                        @endif
                    </div>
                    @if(auth()->user()->is_affiliate != 1)
                    <a class="btn widget-card__deposit" href="{{ route('user.deposit.index') }}">@lang('Deposit Now')</a>
                    @endif
                </div>
            </div>
        </section>

        <ul class="list app-nav__drawer-list">
            @if(auth()->user()->is_affiliate == 1 && auth()->user()->profile_mode == 'affiliate' )
                <li>
                    <a class="dashboard-menu__link {{ menuActive('user.home') }}" href="{{ route('user.home') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-home"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Dashboard') </span>
                    </a>
                </li>
                
                    <form method="POST" action="{{ route('user.profile.mode') }}">
                        @csrf
                        <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                        <input type="hidden" name="mode" value="better">
                        <button class="dashboard-menu__link btn" type="submit">
                            <span class="dashboard-menu__icon">
                                <i class="las la-list"></i>
                            </span>
                            <span class="dashboard-menu__text"> @lang('Better Profile') </span>
                        </button>
                    </form>
                </li>

                <li class="has-submenu_mobile {{ menuActive('user.promotions*', 4) }}">
                    <button class="accordion-button {{ menuActive('user.promotions*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-wallet"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Promo')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.promotions.index', 4) }}" href="{{ route('user.promotions.index') }}">
                                @lang('All Promo')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.promotions.create', 4) }}" href="{{ route('user.promotions.create') }}">
                                @lang('Create Promo')
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a class="dashboard-menu__link {{ menuActive('affiliate.promos.register.user') }}" href="{{ route('affiliate.promos.register.user') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-list"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Register Users') </span>
                    </a>
                </li>
                <li>
                    <a class="dashboard-menu__link {{ menuActive('affiliate.website.list') }}" href="{{ route('affiliate.website.list') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-list"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Website') </span>
                    </a>
                </li>

                <li class="has-submenu_mobile {{ menuActive('user.withdraw*', 4) }}">
                    <button class="accordion-button {{ menuActive('user.withdraw*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-money-bill-wave-alt"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Withdraw')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive(['user.withdraw', 'user.withdraw.preview'], 4) }}" href="{{ route('user.withdraw') }}">
                                @lang('Withdraw Now')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.withdraw.history', 4) }}" href="{{ route('user.withdraw.history') }}">
                                @lang('Withdraw History')
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="has-submenu_mobile {{ menuActive('affiliate.report*', 4) }}">
                    <button class="accordion-button {{ menuActive('affiliate.report*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-sitemap"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Reports')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('affiliate.report.affiliatelink', 4) }}" href="{{ route('affiliate.report.affiliatelink') }}">
                                @lang('Affiliate Link')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('affiliate.report.fullreport', 4) }}" href="{{ route('affiliate.report.fullreport') }}">
                                @lang('Full Reports')
                            </a>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('affiliate.report.playerreport', 4) }}" href="{{ route('affiliate.report.playerreport') }}">
                                @lang('Player Reports')
                            </a>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('affiliate.report.summery', 4) }}" href="{{ route('affiliate.report.summery') }}">
                                @lang('Summery')
                            </a>
                        </li>
                    </ul>
                </li>


                <li class="has-submenu_mobile {{ menuActive('ticket*', 4) }}">
                    <button class="accordion-button {{ menuActive('user.ticket*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-question-circle"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Support Ticket')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('ticket.open', 4) }}" href="{{ route('ticket.open') }}">
                                @lang('Open New Ticket')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive(['ticket.index', 'ticket.view'], 4) }}" href="{{ route('ticket.index') }}">
                                @lang('My Ticket')
                            </a>
                        </li>
                    </ul>
                </li>
            @else
                @if(auth()->user()->is_affiliate == 1 )
                <li>
                    <form method="POST" action="{{ route('user.profile.mode') }}">
                        @CSRF
                        <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                        <input type="hidden" name="mode" value="affiliate">
                                    <button type="submit" class="dashboard-menu__link btn">
                                    <span class="dashboard-menu__icon">
                                <i class="las la-list"></i>
                            </span>
                            <span class="dashboard-menu__text"> Affiliate Profile </span>
                                    </button>
                                </form>
                </li>
                @endif
                <li>
                    <a class="dashboard-menu__link {{ menuActive('live.game') }}" href="{{ route('live.game') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-home"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Live') </span>
                    </a>
                </li>

                <li>
                    <a class="dashboard-menu__link {{ menuActive('upcomming.game') }}" href="{{ route('upcomming.game') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-home"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('UpComming') </span>
                    </a>
                </li>

                <li class="has-submenu_mobile {{ menuActive('casino*', 4) }}">
                    <button class="accordion-button {{ menuActive('casino*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-wallet"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Casino')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('casino.live', 4) }}" href="#">
                                @lang('Slot')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('casino.live', 4) }}" href="{{ route('casino.live') }}">
                                @lang('Live Casino')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link " href="#">
                                @lang('Games')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link " href="#">
                                @lang('Aviator')
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a class="dashboard-menu__link {{ menuActive('user.bets') }}" href="{{ route('user.bets') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-list"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('My Bets') </span>
                    </a>
                </li>


                <li class="has-submenu_mobile {{ menuActive('user.deposit*', 4) }}">
                    <button class="accordion-button {{ menuActive('user.deposit*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-wallet"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Deposit')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.deposit.index', 4) }}" href="{{ route('user.deposit.index') }}">
                                @lang('Deposit Now')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.deposit.history', 4) }}" href="{{ route('user.deposit.history') }}">
                                @lang('Deposit History')
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="has-submenu_mobile {{ menuActive('user.withdraw*', 4) }}">
                    <button class="accordion-button {{ menuActive('user.withdraw*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-money-bill-wave-alt"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Withdraw')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive(['user.withdraw', 'user.withdraw.preview'], 4) }}" href="{{ route('user.withdraw') }}">
                                @lang('Withdraw Now')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.withdraw.history', 4) }}" href="{{ route('user.withdraw.history') }}">
                                @lang('Withdraw History')
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="has-submenu_mobile {{ menuActive('user.referral*', 4) }}">
                    <button class="accordion-button {{ menuActive('user.referral*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-sitemap"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Referral')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.referral.users') }}" href="{{ route('user.referral.users') }}">
                                @lang('Referred Users')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('user.referral.commissions', 4) }}" href="{{ route('user.referral.commissions') }}">
                                @lang('Referral Commissions')
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a class="dashboard-menu__link {{ menuActive('user.transactions', 4) }}" href="{{ route('user.transactions') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-exchange-alt"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Transactions') </span>
                    </a>
                </li>

                <li class="has-submenu_mobile {{ menuActive('ticket*', 4) }}">
                    <button class="accordion-button {{ menuActive('user.ticket*', 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                        <span class="accordion-button__icon">
                            <i class="las la-question-circle"></i>
                        </span>
                        <span class="accordion-button__text">@lang('Support Ticket')</span>
                    </button>

                    <ul class="list dashboard-menu__inner">
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive('ticket.open', 4) }}" href="{{ route('ticket.open') }}">
                                @lang('Open New Ticket')
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__inner-link {{ menuActive(['ticket.index', 'ticket.view'], 4) }}" href="{{ route('ticket.index') }}">
                                @lang('My Ticket')
                            </a>
                        </li>
                    </ul>
                </li>
            @endif


            {{-- <li class="has-submenu has-submenu_mobile {{ menuActive(['user.profile.setting', 'user.change.password', 'user.twofactor'], 4) }}">
            <button class="accordion-button {{ menuActive(['user.profile.setting', 'user.change.password', 'user.twofactor'], 5) }}" data-bs-toggle="collapse" type="button" aria-expanded="false">
                <span class="accordion-button__icon">
                    <i class="las la-user-circle"></i>
                </span>
                <span class="accordion-button__text">@lang('Account Setting')</span>
            </button>

            <ul class="list dashboard-menu__inner">
                <li>
                    <a class="dashboard-menu__inner-link {{ menuActive('user.profile.setting', 4) }}" href="{{ route('user.profile.setting') }}">
                        @lang('Profile Setting')
                    </a>
                </li>
                <li>
                    <a class="dashboard-menu__inner-link {{ menuActive('user.change.password', 4) }}" href="{{ route('user.change.password') }}">
                        @lang('Change Password')
                    </a>
                </li>
                <li>
                    <a class="dashboard-menu__inner-link {{ menuActive('user.twofactor', 4) }}" href="{{ route('user.twofactor') }}">
                        @lang('2FA Security')
                    </a>
                </li>
            </ul>
            </li> --}}

            <li>
                <a class="dashboard-menu__link" href="{{ route('user.logout') }}">
                    <span class="dashboard-menu__icon">
                        <i class="las la-sign-out-alt"></i>
                    </span>
                    <span class="dashboard-menu__text"> @lang('Logout') </span>
                </a>
            </li>
        </ul>
    </div>
</div>
@push('script')
<script>
    (function($) {
        "use strict";

        $('#reload').on('click', function() {
            location.reload();
        });

        //  Submenu Show hide Js Start
        $('.has-submenu_mobile button').on('click', function() {
            $(this).parent(".has-submenu_mobile").children(".dashboard-menu__inner").slideToggle("100");
            $(this).toggleClass('rotate');
        });


        $(this).parent(".has-submenu_mobile.active").children('.dashboard-menu__inner').removeClass('d-block');

        $('.dashboard-sidebar__nav-toggle-btn').on('click', function() {
            $('.body-overlay').toggleClass('active')
        });

        $('.dashboard-menu__head-close').on('click', function() {
            $('body').removeClass('.dashboard-menu-open')
            $('.body-overlay').removeClass('active')
        });

        $('.body-overlay').on('click', function() {
            $('.dashboard-menu__head-close').trigger('click')
            $('.body-overlay').removeClass('active')
        });
    })(jQuery);

</script>
@endpush
