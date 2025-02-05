{{-- @php
    $bonusClaimCount = session('bonus_claim_count', 0);
@endphp

@if ($bonusClaimCount <=1 && auth()->check() && app('userBalance')['is_welcome_message'])
    <div class="p-2 alert alert-success d-flex align-items-center justify-content-between gap-2 px-4">
        <p class="m-0">@lang('Congratulations on claiming your welcome bonus! Enjoy your rewards'). This message will be displayed {{$bonusClaimCount + 1}} out of 2 times.</p>
        <a href="{{route('user.bonus.log')}}" class="btn-sm" style="background:#0f5132; color:#fff" >@lang('Claim Now')</a>
    </div>
    @php
        session(['bonus_claim_count' => $bonusClaimCount + 1]);
    @endphp
@endif --}}

<div class="header-fluid-custom-parent">

    <div class="logo">
        <a href="{{ route('home') }}">
            <img class="img-fluid logo-image" src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="@lang('logo')">
        </a>
    </div>

    <nav class="primary-menu-container">

        <ul class="list list--row primary-menu-lg justify-content-end justify-content-lg-start desktop_display_login">
            {{-- @if (Route::is('home') || Route::is('game.markets') || Route::is('league.games') || Route::is('category.games'))
            <li>
                <a class="bet-type__live @if (session('game_type') != 'upcoming') active @endif" href="{{ route('switch.type', 'live') }}"> @lang('Live') </a>
            </li>
            <li>
                <a class="bet-type__upcoming @if (session('game_type') == 'upcoming') active @endif" href="{{ route('switch.type', 'upcoming') }}"> @lang('Upcoming') </a>
            </li>
            @endif --}}
        </ul>

        <ul class="list list--row primary-menu-lg justify-content-end justify-content-lg-start mobile_display_login">
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
        </ul>

        <ul class="list list--row primary-menu justify-content-end align-items-center right-side-nav">

            {{-- @if (!Route::is('home'))
            <li>
                <a class="primary-menu-lg__link" href="{{ route('home') }}">
            <span class="primary-menu-lg__link-text"> @lang('Home') </span>
            </a>
            </li>
            @endif --}}

            @guest
            <li><button class="btn btn--signup btn-sm" data-bs-toggle="modal" data-bs-target="#loginModal" type="button"> @lang('Login') </button></li>
            <li>
                <button type="button" class="btn btn--signup btn-sm" data-bs-toggle="modal" data-bs-target="#registerModal">
                    @lang('Sign up')
                </button>
            </li>
            @endguest
            @auth
            <li class="p-0 m-0 amount-list">
                <span class="text-white" style="margin-right: 30px">ID: {{auth()->user()->user_id}}</span>
                <a class="text-light ml-4" href="{{ route('user.deposit.index') }}">+{{ showAmount(auth()->user()->balance) }} {{auth()->user()->currency}} </a>
                <ul class="all-ammount-area">
                    <li><span>Deposit: +{{ showAmount(app('userBalance')['deposit']) }}</span> </li>
                    <li><span>Withdrawal: +{{ showAmount(app('userBalance')['withdrawal']) }}</span> </li>
                    <li><span>Bonus: +{{ showAmount(app('userBalance')['bonus']) }}</span> </li>
                    <li><span>Tramcard: +{{ showAmount(app('userBalance')['tramcard']) }}</span> </li>
                </ul>
            </li>
            
            <li class="d-none d-lg-block">
                <div class="dropdown">
                    <a class="btn dropdown-toggle text-light" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle user_icon" style="color: #59B4C3"></i>
                    </a>
                    <ul class="dropdown-menu dark--400" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.profile.setting') }}">My Profile</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item text-light header_dropdown dropdown-toggle" href="#">Deposit</a>
                            <ul class="dropdown-menu dropdown_submenu_1 dark--400">
                                <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.deposit.index') }}">Deposit</a></li>
                                <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.deposit.history') }}">@lang('Deposit History')</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item text-light header_dropdown dropdown-toggle" href="#">Withdraw</a>
                            <ul class="dropdown-menu dropdown_submenu_1 dark--400">
                                <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.withdraw') }}">Withdraw Now</a></li>
                                <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.withdraw.history') }}">@lang('Withdraw History')</a></li>
                            </ul>
                        </li>
                        <li><a class="dropdown-item text-light header_dropdown open-betslip" href="javascript:void(0)">Bet Slip</a></li>
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.bets') }}">Bet History</a></li>
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('casino.history') }}">Casino History</a></li>

                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.bonus.log') }}">Bonuses</a></li>
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.tram.card') }}">Tramcard</a></li>
                        @if(auth()->user() && auth()->user()->is_affiliate != 1)
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.affiliate.application.form') }}">Apply For Affiliate</a></li>
                        @endif
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.logout') }}">Log Out</a></li>
                    </ul>
                </div>
            </li>
            @endauth
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
            </li>
            <li class="d-none d-lg-block">
                <div class="select-lang--container">
                    <div class="select-lang">
                        <div class="select-lang__icon text-white">
                            <i class="fal fa-globe "></i>
                        </div>
                        <select class="form-select langSel">
                            @foreach (App\Models\Language::all() as $item)
                            <option value="{{ $item->code }}" @if (session('lang')==$item->code) selected @endif>
                                {{ __($item->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </li>

            <li class="p-0 m-0">
                <span id="clock" class="text-light badge bg-secondary"></span>

                <script>
                    function updateClock() {
                        var now = new Date();
                        var hours = now.getHours();
                        var minutes = now.getMinutes();
                        var seconds = now.getSeconds();
                        hours = hours < 10 ? '0' + hours : hours;
                        minutes = minutes < 10 ? '0' + minutes : minutes;
                        seconds = seconds < 10 ? '0' + seconds : seconds;
                        var time = hours + ':' + minutes + ':' + seconds;
                        document.getElementById('clock').innerHTML = time;
                    }
                    setInterval(updateClock, 1000);
                    updateClock();
                </script>
            </li>
            @auth
            <li class="p-0 m-0">
                <a class="text-light" href="#"><i class="fas fa-envelope" style="color: #F28585"></i></a>
            </li>
            <li>
                
                <div class="icon position-relative" id="bell"> <img src="https://i.imgur.com/AC7dgLA.png" alt=""> <b class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{app('userNotificationData')['unreadNotificationCountData']}}</b></div>
                <div class="notifications" id="box">
                    <h2>Notifications - <span>{{app('userNotificationData')['unreadNotificationCountData']}}</span></h2>
                    
                    @forelse(app('userNotificationData')['userNotificationsData'] as $data)
                        <div class="notifications-item">
                            <a href="{{route('user.notify.url', ['id' => $data->id])}}">
                                <div class="text">
                                    <h4 class="{{!$data->is_read ? 'text-primary' : ''}}" >{{$data->title}}</h4>
                                </div>
                            </a>
                        </div>
                    @empty
                    <div class="notifications-item">
                        <div class="text">
                            <h4></h4>
                        </div>
                    </div>
                    @endforelse
                    
                </div>
            </li>
            <li class="d-none d-lg-block">
                <div class="dropdown">
                    <a class="btn dropdown-toggle text-light" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog" style="color: #BBE2EC"></i>
                    </a>
                    <ul class="dropdown-menu dark--400" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.change.password') }}">Change Password</a></li>
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.twofactor') }}">2FA</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item text-light header_dropdown dropdown-toggle" href="#">Referral</a>
                            <ul class="dropdown-menu dropdown_submenu_1 dark--400">
                                <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.referral.myRefLink') }}">Referred Link</a></li>
                                <li><a class="dropdown-item text-light header_dropdown" href="{{ route('user.referral.users') }}">@lang('Referred Users')</a></li>
                            </ul>
                        </li>
                        <li><a class="dropdown-item text-light header_dropdown" href="#">Security and Privacy</a></li>
                        <li><a class="dropdown-item text-light header_dropdown" href="{{ route('ticket.open') }}">Support</a></li>
                        @if(auth()->user()->is_affiliate == 1)
                        <li>
                            <form method="POST" action="{{ route('user.profile.mode') }}">
                                @CSRF
                                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                                <input type="hidden" name="mode" value="affiliate">
                                <button type="submit" class="dropdown-item text-light header_dropdown">Affiliate Profile</button>
                            </form>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endauth
        </ul>
    </nav>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
    $('.dropdown-submenu a.dropdown-toggle').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
    });
});
</script>
@php
$loginContent = getContent('login.content', true);
@endphp


@push('style')
<style>
@import url("https://fonts.googleapis.com/css2?family=Manrope:wght@200&display=swap");

.amount-list {
    position: relative;
}

ul.all-ammount-area {
    position: absolute;
    background: #fff;
    z-index: 999;
    width: 285px;
    border: 0px solid #ddd;
    top: 42px;
    height: 0;
    overflow: hidden;
}
.amount-list:hover ul.all-ammount-area{
    border: 1px solid #ddd;
    height:auto;
    overflow: auto;
}
ul.all-ammount-area li {
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    padding: 5px 0;
}

ul.all-ammount-area li:last-child {
    border: 0;
}

nav {
    display: flex;
    align-items: center;
    position: relative;
}
.icon {
    cursor: pointer;
}
.icon span {
    background: #f00;
    padding: 7px;
    border-radius: 50%;
    color: #fff;
    vertical-align: top;
    margin-left: -25px;
}
.icon img {
    display: inline-block;
    width: 18px;
    margin-top: 4px;
}
.icon:hover {
    opacity: 0.7;
}
.notifications {
    width: 300px;
    height: 0px;
    opacity: 0;
    position: absolute;
    top: 63px;
    right: 62px;
    border-radius: 5px 0px 5px 5px;
    background-color: #fff;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    z-index: 9;
    overflow: hidden;
}
.notifications h2 {
    font-size: 14px;
    padding: 10px;
    border-bottom: 1px solid #eee;
    color: #999;
    margin: 0;
    background: #ddd;
}
.notifications h2 span {
    color: #f00;
}
.notifications-item {
    border-bottom: 1px solid #eee;
    padding: 6px 9px;
    margin-bottom: 0px;
    cursor: pointer;
}
.notifications-item:hover {
    background-color: #eee;
}
.notifications-item .text h4 {
    color: #777;
    font-size: 13px;
    margin-top: 3px;
    margin-bottom: 0;
}
.notifications-item .text p {
    color: #aaa;
    font-size: 12px;
    margin: 0;
}

</style>
@endpush

@push('script')
<script>
$(document).ready(function () {
    var down = false;

    $("#bell").click(function (e) {
        var color = $(this).text();
        if (down) {
            $("#box").css("height", "0px");
            $("#box").css("opacity", "0");
            $("#box").css("overflow", "hidden");
            down = false;
        } else {
            $("#box").css("height", "auto");
            $("#box").css("opacity", "1");
            $("#box").css("overflow", "auto");
            down = true;
        }
    });
});
</script>
@endpush

<div class="modal fade login-modal" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body p-3 p-sm-5">
                <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                    <i class="las la-times"></i>
                </span>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="mt-0">{{ __(@$loginContent->data_values->heading) }}</h4>
                </div>
                @include($activeTemplate . 'partials.login')
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Registration Process</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body class text-center">
                <a class="btn btn--signup" href="{{ route('user.register') }}"> @lang('Full Registration') </a>
                <a class="btn btn--signup" href="{{ route('user.oneclick.register') }}"> @lang('One Click Registration') </a>
            </div>
        </div>
    </div>
</div>
