<div class="col-lg-4 col-xl-3">
    <div class="dashboard-sidebar d-lg-block d-none">
        <div class="widget-card widget-card--primary d-none d-lg-block">
            <div class="widget-card__head">
                <span class="widget-card__id d-block"> <i class="la la-user"></i> ID: {{ auth()->user()->username }}</span>
                <button class="btn widget-card__reload" id="reload" type="button">
                    <i class="las la-sync"></i>
                </button>
            </div>
            <div class="widget-card__body">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <h5 class="widget-card__balance">
                            {{ __(userCurrency()) }}{{ showAmount(auth()->user()->balance) }}</h5>
                        <span class="widget-card__balance-text">@lang('Current Balance')</span>
                    </div>
                    <div class="">
                        <h5 class="widget-card__balance">
                            {{ __(userCurrency()) }}{{ showAmount(auth()->user()->bonus_account) }}</h5>
                        <span class="widget-card__balance-text">@lang('Bonus Balance')</span>
                    </div>
                </div>
                <a class="btn widget-card__deposit" href="{{ route('user.deposit.index') }}">@lang('Deposit Now')</a>
            </div>
        </div>

        @if (auth()->user()->is_affiliate == 1)
            <div class="mt-2">
                <div class="d-flex justify-content-between">
                    <div class="form-check-label" for="profile_mode">Better</div>
                    <div class="form-check form-switch pl-2">
                        <input class="form-check-input userprofileMode" type="checkbox" data-id={{ auth()->user()->id }} role="switch"
                            id="profile_mode" {{ auth()->user()->profile_mode == 'affiliate' ? 'checked' : '' }}>
                    </div>
                    <div class="form-check-label" for="profile_mode">Affiliate</div>
                </div>
            </div>
        @endif

        <div class="dashboard-menu">
            <div class="dashboard-menu__head">
                <span class="dashboard-menu__head-text">
                    <i class="la la-user"></i> {{ auth()->user()->username }}
                </span>
                <button class="btn dashboard-menu__head-close" type="button">
                    <i class="las la-times"></i>
                </button>
            </div>
            <div class="dashboard-menu__body">

                <ul class="list dashboard-menu__list">
                    <li>
                        <div class="widget-card menu-blance widget-card--primary d-block d-lg-none">
                            <div class="widget-card__body">
                                <h5 class="widget-card__balance">
                                    {{ __($general->cur_sym) }}{{ showAmount(auth()->user()->balance) }}</h5>
                                <span class="widget-card__balance-text">@lang('Current Balance')</span>
                                <a class="btn widget-card__deposit"
                                    href="{{ route('user.deposit.index') }}">@lang('Deposit Now')</a>
                            </div>
                        </div>
                    </li>

                    <li>
                        <a class="dashboard-menu__link {{ menuActive('home', 4) }}"
                            href="{{ route('home') }}">
                            <span class="dashboard-menu__icon">
                                <i class="las la-home"></i>
                            </span>
                            <span class="dashboard-menu__text"> @lang('Home') </span>
                        </a>
                    </li>


                    <li>
                        <a class="dashboard-menu__link {{ menuActive('user.bets') }}" href="{{ route('user.bets') }}">
                            <span class="dashboard-menu__icon">
                                <i class="las la-list"></i>
                            </span>
                            <span class="dashboard-menu__text"> @lang('My Bets') </span>
                        </a>
                    </li>

                    {{-- @if (auth()->user()->is_affiliate == 1 && auth()->user()->profile_mode == 'affiliate')
                        <li>
                            <a class="dashboard-menu__link {{ menuActive('user.news.index') }}"
                                href="{{ route('user.news.index') }}">
                                <span class="dashboard-menu__icon">
                                    <i class="la la-newspaper-o"></i>
                                </span>
                                <span class="dashboard-menu__text"> @lang('News') </span>
                            </a>
                        </li>

                        <li class="has-submenu {{ menuActive('user.promotions*', 4) }}">
                            <button class="accordion-button {{ menuActive('user.promotions*', 5) }}"
                                data-bs-toggle="collapse" type="button" aria-expanded="false">
                                <span class="accordion-button__icon">
                                    <i class="la la-newspaper-o"></i>
                                </span>
                                <span class="accordion-button__text">@lang('Promo')</span>
                            </button>

                            <ul class="list dashboard-menu__inner">
                                <li>
                                    <a class="dashboard-menu__inner-link {{ menuActive('user.promotions.index', 4) }}"
                                        href="{{ route('user.promotions.index') }}">
                                        @lang('All Promo')
                                    </a>
                                </li>
                                <li>
                                    <a class="dashboard-menu__inner-link {{ menuActive('user.promotions.create', 4) }}"
                                        href="{{ route('user.promotions.create') }}">
                                        @lang('Promo Create')
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a class="dashboard-menu__link {{ menuActive('user.balance') }}"
                                href="{{ route('user.balance') }}">
                                <span class="dashboard-menu__icon">
                                    <i class="las la-list"></i>
                                </span>
                                <span class="dashboard-menu__text"> @lang('Affiliate Report') </span>
                            </a>
                        </li>
                        <li>
                            <a class="dashboard-menu__link {{ menuActive('user.balance') }}"
                                href="{{ route('user.balance') }}">
                                <span class="dashboard-menu__icon">
                                    <i class="las la-list"></i>
                                </span>
                                <span class="dashboard-menu__text"> @lang('Balance') </span>
                            </a>
                        </li>
                    @endif --}}


                    <li class="has-submenu {{ menuActive('user.deposit*', 4) }}">
                        <button class="accordion-button {{ menuActive('user.deposit*', 5) }}" data-bs-toggle="collapse"
                            type="button" aria-expanded="false">
                            <span class="accordion-button__icon">
                                <i class="las la-wallet"></i>
                            </span>
                            <span class="accordion-button__text">@lang('Deposit')</span>
                        </button>

                        <ul class="list dashboard-menu__inner">
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.deposit.index', 4) }}"
                                    href="{{ route('user.deposit.index') }}">
                                    @lang('Deposit Now')
                                </a>
                            </li>
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.deposit.history', 4) }}"
                                    href="{{ route('user.deposit.history') }}">
                                    @lang('Deposit History')
                                </a>
                            </li>
                        </ul>
                    </li>
                     @if(auth()->user()->is_affiliate == 1)
                         @if(\Carbon\Carbon::now()->format('l') == 'Monday')
                            <li class="has-submenu {{ menuActive('user.withdraw*', 4) }}">
                                <button class="accordion-button {{ menuActive('user.withdraw*', 5) }}"
                                    data-bs-toggle="collapse" type="button" aria-expanded="false">
                                    <span class="accordion-button__icon">
                                        <i class="las la-money-bill-wave-alt"></i>
                                    </span>
                                    <span class="accordion-button__text">@lang('Withdraw')</span>
                                </button>
        
                                <ul class="list dashboard-menu__inner">
                                    <li>
                                        <a class="dashboard-menu__inner-link {{ menuActive(['user.withdraw', 'user.withdraw.preview'], 4) }}"
                                            href="{{ route('user.withdraw') }}">
                                            @lang('Withdraw Now')
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dashboard-menu__inner-link {{ menuActive('user.withdraw.history', 4) }}"
                                            href="{{ route('user.withdraw.history') }}">
                                            @lang('Withdraw History')
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @else
                    <li class="has-submenu {{ menuActive('user.withdraw*', 4) }}">
                        <button class="accordion-button {{ menuActive('user.withdraw*', 5) }}"
                            data-bs-toggle="collapse" type="button" aria-expanded="false">
                            <span class="accordion-button__icon">
                                <i class="las la-money-bill-wave-alt"></i>
                            </span>
                            <span class="accordion-button__text">@lang('Withdraw')</span>
                        </button>

                        <ul class="list dashboard-menu__inner">
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive(['user.withdraw', 'user.withdraw.preview'], 4) }}"
                                    href="{{ route('user.withdraw') }}">
                                    @lang('Withdraw Now')
                                </a>
                            </li>
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.withdraw.history', 4) }}"
                                    href="{{ route('user.withdraw.history') }}">
                                    @lang('Withdraw History')
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    

                    <li class="has-submenu {{ menuActive('user.referral*', 4) }}">
                        <button class="accordion-button {{ menuActive('user.referral*', 5) }}"
                            data-bs-toggle="collapse" type="button" aria-expanded="false">
                            <span class="accordion-button__icon">
                                <i class="las la-sitemap"></i>
                            </span>
                            <span class="accordion-button__text">@lang('Referral')</span>
                        </button>

                        <ul class="list dashboard-menu__inner">
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.referral.myRefLink') }}" href="{{ route('user.referral.myRefLink') }}">
                                    @lang('Referred Link')
                                </a>
                            </li>
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.referral.users') }}"
                                    href="{{ route('user.referral.users') }}">
                                    @lang('Referred Users')
                                </a>
                            </li>
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.referral.commissions', 4) }}"
                                    href="{{ route('user.referral.commissions') }}">
                                    @lang('Referral Commissions')
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a class="dashboard-menu__link {{ menuActive('user.events') }}"
                            href="{{ route('user.events') }}">
                            <span class="dashboard-menu__icon">
                                <i class="las la-exchange-alt"></i>
                            </span>
                            <span class="dashboard-menu__text"> @lang('Events') </span>
                        </a>
                    </li>
                    <li>
                        <a class="dashboard-menu__link {{ menuActive('user.bonus.log') }}"
                            href="{{ route('user.bonus.log') }}">
                            <span class="dashboard-menu__icon">
                                <i class="las la-exchange-alt"></i>
                            </span>
                            <span class="dashboard-menu__text"> @lang('Bonus Transaction') </span>
                        </a>
                    </li>
                    <li>
                        <a class="dashboard-menu__link {{ menuActive('user.transactions', 4) }}"
                            href="{{ route('user.transactions') }}">
                            <span class="dashboard-menu__icon">
                                <i class="las la-exchange-alt"></i>
                            </span>
                            <span class="dashboard-menu__text"> @lang('Transactions') </span>
                        </a>
                    </li>


                    <li class="has-submenu {{ menuActive('ticket*', 4) }}">
                        <button class="accordion-button {{ menuActive('user.ticket*', 5) }}"
                            data-bs-toggle="collapse" type="button" aria-expanded="false">
                            <span class="accordion-button__icon">
                                <i class="las la-question-circle"></i>
                            </span>
                            <span class="accordion-button__text">@lang('Support Ticket')</span>
                        </button>

                        <ul class="list dashboard-menu__inner">
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('ticket.open', 4) }}"
                                    href="{{ route('ticket.open') }}">
                                    @lang('Open New Ticket')
                                </a>
                            </li>
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive(['ticket.index', 'ticket.view'], 4) }}"
                                    href="{{ route('ticket.index') }}">
                                    @lang('My Ticket')
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li
                        class="has-submenu {{ menuActive(['user.profile.setting', 'user.change.password', 'user.twofactor'], 4) }}">
                        <button
                            class="accordion-button {{ menuActive(['user.profile.setting', 'user.change.password', 'user.twofactor'], 5) }}"
                            data-bs-toggle="collapse" type="button" aria-expanded="false">
                            <span class="accordion-button__icon">
                                <i class="las la-user-circle"></i>
                            </span>
                            <span class="accordion-button__text">@lang('Account Setting')</span>
                        </button>

                        <ul class="list dashboard-menu__inner">
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.profile.setting', 4) }}"
                                    href="{{ route('user.profile.setting') }}">
                                    @lang('Profile Setting')
                                </a>
                            </li>
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.change.password', 4) }}"
                                    href="{{ route('user.change.password') }}">
                                    @lang('Change Password')
                                </a>
                            </li>
                            <li>
                                <a class="dashboard-menu__inner-link {{ menuActive('user.twofactor', 4) }}"
                                    href="{{ route('user.twofactor') }}">
                                    @lang('2FA Security')
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a class="dashboard-menu__link {{ menuActive('user.affiliate.application.form', 4) }}"
                            href="{{ route('user.affiliate.application.form') }}">
                            <span class="dashboard-menu__icon">
                                <i class="las la-exchange-alt"></i>
                            </span>
                            <span class="dashboard-menu__text"> @lang('Apply for Affliate') </span>
                        </a>
                    </li>
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
            $('.has-submenu button').on('click', function() {
                $(this).parent(".has-submenu").children(".dashboard-menu__inner").slideToggle("100");
                $(this).toggleClass('rotate');
            });


            $(this).parent(".has-submenu.active").children('.dashboard-menu__inner').removeClass('d-block');

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

    <script>
            $('#profile_mode').on('change', function() {
                var mode = $(this).is(':checked') ? "affiliate" : "better";
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ route('user.profile.mode') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        mode: mode,
                        id: id
                    },
                    success: function(response) {
                        location.reload();
                    }
                });
                // location.reload();
            });
    </script>
@endpush
