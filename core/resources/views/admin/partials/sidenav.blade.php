<div class="sidebar bg--dark">
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            @php
                $domain = app("domainCheckList");
            @endphp
            @if($domain)
                <a class="sidebar__main-logo" href="{{ route('admin.dashboard') }}"><img
                    src="{{ asset('/core/public/storage/' . $domain->logo) }}" alt="@lang('image')"></a>
            @else
                <a class="sidebar__main-logo" href="{{ route('admin.dashboard') }}"><img
                    src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="@lang('image')"></a>
            @endif
            
        </div>

        <div class="sidebar__menu-wrapper" id="sidebar__menuWrapper">
            <ul class="sidebar__menu">

                @can('dashboard')
                    <li class="sidebar-menu-item {{ menuActive('admin.dashboard') }}">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <i class="menu-icon las la-home"></i>
                            <span class="menu-title">@lang('Dashboard')</span>
                        </a>
                    </li>
                @endcan
                    <li class="sidebar-menu-item {{ menuActive('admin.activity') }}">
                        <a class="nav-link" href="{{ route('admin.activity') }}">
                            <i class="menu-icon las la-home"></i>
                            <span class="menu-title">@lang('Activity')</span>
                        </a>
                    </li>
                
                @can('manage-better')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.users*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon las la-users"></i>
                            <span class="menu-title">@lang('Manage Bettors')</span>

                            @if (
                                $bannedUsersCount > 0 ||
                                    $emailUnverifiedUsersCount > 0 ||
                                    $mobileUnverifiedUsersCount > 0 ||
                                    $kycUnverifiedUsersCount > 0 ||
                                    $kycPendingUsersCount > 0)
                                <span class="menu-badge pill bg--danger ms-auto">
                                    <i class="fa fa-exclamation"></i>
                                </span>
                            @endif
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.users*', 2) }}">
                            <ul>
                                 @can('active-better')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.week.active') }}">
                                        <a class="nav-link" href="{{ route('admin.users.week.active') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Current Week Bettors')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('active-better')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.active') }}">
                                        <a class="nav-link" href="{{ route('admin.users.active') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Active Bettors')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('banned-better')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.banned') }}">
                                        <a class="nav-link" href="{{ route('admin.users.banned') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Banned Bettors')</span>
                                            @if ($bannedUsersCount)
                                                <span class="menu-badge pill bg--danger ms-auto">{{ $bannedUsersCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('email-unverify')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.email.unverified') }}">
                                        <a class="nav-link" href="{{ route('admin.users.email.unverified') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Email Unverified')</span>

                                            @if ($emailUnverifiedUsersCount)
                                                <span
                                                    class="menu-badge pill bg--danger ms-auto">{{ $emailUnverifiedUsersCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('mobile-unverify')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.mobile.unverified') }}">
                                        <a class="nav-link" href="{{ route('admin.users.mobile.unverified') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Mobile Unverified')</span>
                                            @if ($mobileUnverifiedUsersCount)
                                                <span
                                                    class="menu-badge pill bg--danger ms-auto">{{ $mobileUnverifiedUsersCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('kyc-unverify')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.kyc.unverified') }}">
                                        <a class="nav-link" href="{{ route('admin.users.kyc.unverified') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('KYC Unverified')</span>
                                            @if ($kycUnverifiedUsersCount)
                                                <span
                                                    class="menu-badge pill bg--danger ms-auto">{{ $kycUnverifiedUsersCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('kyc-pending')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.kyc.pending') }}">
                                        <a class="nav-link" href="{{ route('admin.users.kyc.pending') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('KYC Pending')</span>
                                            @if ($kycPendingUsersCount)
                                                <span
                                                    class="menu-badge pill bg--danger ms-auto">{{ $kycPendingUsersCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('with-balance')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.with.balance') }}">
                                        <a class="nav-link" href="{{ route('admin.users.with.balance') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('With Balance')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('all-bettors')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.all') }}">
                                        <a class="nav-link" href="{{ route('admin.users.all') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('All Bettors')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('notification-to-all')
                                    <li class="sidebar-menu-item {{ menuActive('admin.users.notification.all') }}">
                                        <a class="nav-link" href="{{ route('admin.users.notification.all') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Notification to All')</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcan
                
                
                 @can('super-admin')
                    <li class="sidebar__menu-header">@lang('Goal Serve API Config')</li>
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.goal*', 5) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-spotify"></i>
                            <span class="menu-title">@lang('Goal Serve')</span>
                        </a>

                        <div class="sidebar-submenu {{ menuActive('admin.goal*', 2) }}">
                            <ul>
                                <li class="sidebar-menu-item {{ menuActive('admin.goal.category') }}">
                                    <a class="nav-link" href="{{ route('admin.goal.category') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Categories')</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                @endcan

                @can('role-management')
                    <li class="sidebar__menu-header">@lang('Role Management')</li>
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.role*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-spotify"></i>
                            <span class="menu-title">@lang('Permission Config')</span>

                        </a>

                        <div class="sidebar-submenu {{ menuActive('admin.role*', 2) }}">
                            <ul>
                                @can('role-view')
                                    <li class="sidebar-menu-item {{ menuActive('admin.role.list') }}">
                                        <a class="nav-link" href="{{ route('admin.role.list') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Roles List')</span>
                                        </a>
                                    </li>
                                @endcan


                            </ul>
                        </div>



                    </li>

                @endcan

                @can('bonus-page')
                    <li class="sidebar__menu-header">@lang('Bonus Management')</li>
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.event*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-spotify"></i>
                            <span class="menu-title">@lang('Bonus')</span>
                        </a>

                        <div class="sidebar-submenu {{ menuActive('admin.event*', 2) }}">
                            <ul>
                                <li class="sidebar-menu-item {{ menuActive('admin.event.list') }}">
                                    <a class="nav-link" href="{{ route('admin.event.list') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Bonus List')</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item {{ menuActive('admin.event.create') }}">
                                    <a class="nav-link" href="{{ route('admin.event.create') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Bonus Create')</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item {{ menuActive('admin.event.send.user') }}">
                                    <a class="nav-link" href="{{ route('admin.event.send.user') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Bonus Users')</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item {{ menuActive('admin.event.cashback.settings') }}">
                                    <a class="nav-link" href="{{ route('admin.event.cashback.settings') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Cashback Settings')</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item {{ menuActive('admin.event.deposit.settings') }}">
                                    <a class="nav-link" href="{{ route('admin.event.deposit.settings') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Deposit Settings')</span>
                                    </a>
                                </li>
                                @can('tram-card')
                                <li class="sidebar-menu-item {{ menuActive('admin.event.tramcard.*') }}">
                                    <a class="nav-link" href="{{ route('admin.event.tramcard.list') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Tram cards')</span>
                                    </a>
                                </li>
                                @endcan
                                <li class="sidebar-menu-item {{ menuActive('admin.event.promo.*') }}">
                                    <a class="nav-link" href="{{ route('admin.event.promo.banner.list') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Promo Banners')</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan
                @canany('role-management')
                    <li class="sidebar__menu-header">@lang('Admin Management')</li>
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.admin*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-spotify"></i>
                            <span class="menu-title">@lang('Admin Config')</span>

                        </a>

                        <div class="sidebar-submenu {{ menuActive('admin.admin*', 2) }}">
                            <ul>
                                <li class="sidebar-menu-item {{ menuActive('admin.admin.list') }}">
                                    <a class="nav-link" href="{{ route('admin.admin.list') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Admin List')</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcanany
                @can('affiliate-page')
                    <li class="sidebar__menu-header">@lang('Affiliate Management')</li>
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.affiliate*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-spotify"></i>
                            <span class="menu-title">@lang('Affiliate Config')</span>

                        </a>

                        <div class="sidebar-submenu {{ menuActive('admin.affiliate*', 2) }}">
                            <ul>
                                <li class="sidebar-menu-item {{ menuActive('admin.affiliate.list') }}">
                                    <a class="nav-link" href="{{ route('admin.affiliate.list') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Affiliate List')</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item {{ menuActive('admin.affiliate.company_expenses') }}">
                                    <a class="nav-link" href="{{ route('admin.affiliate.company_expenses') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Company Expenses')</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item {{ menuActive('admin.affiliate.promocode.list') }}">
                                    <a class="nav-link" href="{{ route('admin.affiliate.promocode.list') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Promo Codes')</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item {{ menuActive('admin.affiliate.better.application') }}">
                                    <a class="nav-link" href="{{ route('admin.affiliate.better.application') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Better Application')</span>
                                    </a>
                                </li>

                                <li class="sidebar-menu-item {{ menuActive('admin.affiliate.withdraw.setting') }}">
                                    <a class="nav-link" href="{{ route('admin.affiliate.withdraw.setting') }}">
                                        <i class="menu-icon las la-dot-circle"></i>
                                        <span class="menu-title">@lang('Affiliate Withdraw Setting')</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan

                @canany(['agent-area', 'agent-create', 'agent-update', 'agent-delete', 'agent-view',
                    'create-wallet-number', 'make-deposit'])
                    <li class="sidebar__menu-header">@lang('Agent Configure')</li>
                @endcanany
                @can('agent-area')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.agent*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-spotify"></i>
                            <span class="menu-title">@lang('Agent Config')</span>

                        </a>

                        <div class="sidebar-submenu {{ menuActive('admin.agent*', 2) }}">
                            <ul>
                                @can('agent-view')
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.list') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.list') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Agents List')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('agent-create')
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.create') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.create') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Agent Create')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('transaction-providers-view')
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.transaction.providers*') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.transaction.providers') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Transaction Providers')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('create-wallet-number')
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.diposit.wallet*') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.diposit.wallet.list') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Wallets')</span>
                                        </a>
                                    </li>
                                @endcan 
                                @can('make-bettor-deposit')
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.make.bettor.deposit.page') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.make.bettor.deposit.page') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Make Bettor Deposit')</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can('make-bettor-withdraw')
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.make.bettor.withdraw.page') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.make.bettor.withdraw.page') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Make Bettor Withdraw')</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                
                                @can('make-deposit')
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.deposit.list') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.deposit.list') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('All Deposits')</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.deposit.status.pending') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.deposit.status.pending', 'Pending') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Pending Deposits')</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.deposit.status.approve') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.deposit.status.approve', 'Approved') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Approved Deposits')</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.deposit.status.reject') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.deposit.status.reject', 'Rejected') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Rejected Deposits')</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-menu-item {{ menuActive('admin.agent.deposit.status.back') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.deposit.status.back', 'Back') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Back Deposits')</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                @can('super-admin')
                                     <li class="sidebar-menu-item {{ menuActive('admin.agent.threshold.index') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.threshold.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Threshold value')</span>
                                        </a>
                                     </li>
                                     <li class="sidebar-menu-item {{ menuActive('admin.agent.password.request*') }}">
                                        <a class="nav-link" href="{{ route('admin.agent.password.request.list') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Password Request')</span>
                                        </a>
                                     </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcan



                <li class="sidebar__menu-header">@lang('Bet Setup')</li>

                @can('sporting-config')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive(['admin.category*', 'admin.league*', 'admin.team*'], 3) }}"
                            href="javascript:void(0)">
                            <i class="menu-icon la la-spotify"></i>
                            <span class="menu-title">@lang('Sports Config')</span>
                            @if (
                                $bannedUsersCount > 0 ||
                                    $emailUnverifiedUsersCount > 0 ||
                                    $mobileUnverifiedUsersCount > 0 ||
                                    $kycUnverifiedUsersCount > 0 ||
                                    $kycPendingUsersCount > 0)
                                <span class="menu-badge pill bg--danger ms-auto">
                                    <i class="fa fa-exclamation"></i>
                                </span>
                            @endif
                        </a>

                        <div
                            class="sidebar-submenu {{ menuActive(['admin.category*', 'admin.league*', 'admin.team*'], 2) }}">
                            <ul>
                                @can('manage-categories')
                                    <li class="sidebar-menu-item {{ menuActive('admin.category*') }}">
                                        <a class="nav-link" href="{{ route('admin.category.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Manage Categories')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('manage-league')
                                    <li class="sidebar-menu-item {{ menuActive('admin.league*') }}">
                                        <a class="nav-link" href="{{ route('admin.league.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Manage Leagues')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('manage-teams')
                                    <li class="sidebar-menu-item {{ menuActive('admin.team*') }}">
                                        <a class="nav-link" href="{{ route('admin.team.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Manage Teams')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('manage-games')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive(['admin.game*', 'admin.question*', 'admin.option*'], 3) }}"
                            href="javascript:void(0)">
                            <i class="menu-icon las la-gamepad"></i>
                            <span class="menu-title">@lang('Manage Games') </span>
                        </a>
                        <div
                            class="sidebar-submenu {{ menuActive(['admin.game*', 'admin.question*', 'admin.option*'], 2) }}">
                            <ul>

                                @can('running-games')
                                    <li class="sidebar-menu-item {{ menuActive('admin.game.running') }}">
                                        <a class="nav-link" href="{{ route('admin.game.running') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Running')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('upcoming-games')
                                    <li class="sidebar-menu-item {{ menuActive('admin.game.upcoming') }}">
                                        <a class="nav-link" href="{{ route('admin.game.upcoming') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Upcoming')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('ended-games')
                                    <li class="sidebar-menu-item {{ menuActive('admin.game.ended') }}">
                                        <a class="nav-link" href="{{ route('admin.game.ended') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Ended')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('all-games')
                                    <li class="sidebar-menu-item {{ menuActive('admin.game.index') }}">
                                        <a class="nav-link" href="{{ route('admin.game.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('All')</span>
                                        </a>
                                    </li>
                                @endcan
                                    <li class="sidebar-menu-item {{ menuActive('admin.managehomegame') }}">
                                        <a class="nav-link" href="{{ route('admin.managehomegame') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Homepage Game(live)')</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-menu-item {{ menuActive('admin.managehomegameup') }}">
                                        <a class="nav-link" href="{{ route('admin.managehomegameup') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Homepage Game(Up)')</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-menu-item {{ menuActive('admin.managehomegamefeatured') }}">
                                        <a class="nav-link" href="{{ route('admin.managehomegamefeatured') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Homepage Game(Featured)')</span>
                                        </a>
                                    </li>
                            </ul>
                        </div>
                    </li>
                @endcan

                @canany(['bet-placed', 'declare-outcomes'])
                    <li class="sidebar__menu-header">@lang('Manage Bets')</li>
                @endcanany
                @can('bet-placed')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.bet*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon las la-clipboard-list"></i>
                            <span class="menu-title">@lang('Bet Placed') </span>
                            @if ($pendingBetCount > 0)
                                <span class="menu-badge pill bg--danger ms-auto">
                                    <i class="fa fa-exclamation"></i>
                                </span>
                            @endif
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.bet*', 2) }}">
                            <ul>
                                @can('pending-bet')
                                    <li class="sidebar-menu-item {{ menuActive('admin.bet.pending') }}">
                                        <a class="nav-link" href="{{ route('admin.bet.pending') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Pending')</span>
                                            @if ($pendingBetCount)
                                                <span class="menu-badge pill bg--danger ms-auto">{{ $pendingBetCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('won-bet')
                                    <li class="sidebar-menu-item {{ menuActive('admin.bet.won') }}">
                                        <a class="nav-link" href="{{ route('admin.bet.won') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Won')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('loss-bet')
                                    <li class="sidebar-menu-item {{ menuActive('admin.bet.lose') }}">
                                        <a class="nav-link" href="{{ route('admin.bet.lose') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Lose')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('refund-bet')
                                    <li class="sidebar-menu-item {{ menuActive('admin.bet.refunded') }}">
                                        <a class="nav-link" href="{{ route('admin.bet.refunded') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Refunded')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('all-bet')
                                    <li class="sidebar-menu-item {{ menuActive('admin.bet.index') }}">
                                        <a class="nav-link" href="{{ route('admin.bet.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('All')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('declare-outcomes')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.outcomes.*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon las la-clipboard-list"></i>
                            <span class="menu-title">@lang('Declare Outcomes') </span>
                            @if ($pendingGameCount > 0 || $pendingUpcomingGameCount > 0)
                                <span class="menu-badge pill bg--danger ms-auto">
                                    <i class="fa fa-exclamation"></i>
                                </span>
                            @endif
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.outcomes*', 2) }}">
                            <ul>
                                @can('pending-outcomes')
                                    <li class="sidebar-menu-item {{ menuActive('admin.outcomes.declare.game') }}">
                                        <a class="nav-link" href="{{ route('admin.outcomes.declare.game') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Pending Live Games')</span>
                                            @if ($pendingGameCount)
                                                <span class="menu-badge pill bg--danger ms-auto">{{ $pendingGameCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan
                                @can('pending-outcomes')
                                    <li class="sidebar-menu-item {{ menuActive('admin.outcomes.declare.game.upcoming') }}">
                                        <a class="nav-link" href="{{ route('admin.outcomes.declare.game.upcoming') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Pending Games (UP)')</span>
                                            @if ($pendingUpcomingGameCount)
                                                <span class="menu-badge pill bg--danger ms-auto">{{ $pendingUpcomingGameCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('declare-outcomes')
                                    <li class="sidebar-menu-item {{ menuActive('admin.outcomes.declare.declared') }}">
                                        <a class="nav-link" href="{{ route('admin.outcomes.declare.declared') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Declared Outcomes')</span>
                                        </a>
                                    </li>
                                @endcan
                                
                                 @can('declare-outcomes')
                                    <li class="sidebar-menu-item {{ menuActive('admin.outcomes.declare.upcoming.category') }}">
                                        <a class="nav-link" href="{{ route('admin.outcomes.declare.upcoming.category') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Upcoming Result')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @canany(['payment-gateways', 'diposits', 'withdrawals'])
                    <li class="sidebar__menu-header">@lang('Manage Finance')</li>
                @endcanany

                @can('payment-gateways')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.gateway*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon las la-credit-card"></i>
                            <span class="menu-title">@lang('Payment Gateways')</span>
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.gateway*', 2) }}">
                            <ul>

                                @can('autometic-payment')
                                    <li class="sidebar-menu-item {{ menuActive('admin.gateway.automatic.*') }}">
                                        <a class="nav-link" href="{{ route('admin.gateway.automatic.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Automatic Gateways')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('manual-payment')
                                    <li class="sidebar-menu-item {{ menuActive('admin.gateway.manual.*') }}">
                                        <a class="nav-link" href="{{ route('admin.gateway.manual.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Manual Gateways')</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcan

                @can('deposits')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.deposit*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon las la-file-invoice-dollar"></i>
                            <span class="menu-title">@lang('Deposits')</span>
                            @if (0 < $pendingDepositsCount)
                                <span class="menu-badge pill bg--danger ms-auto">
                                    <i class="fa fa-exclamation"></i>
                                </span>
                            @endif
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.deposit*', 2) }}">
                            <ul>

                                @can('commission')
                                    <li class="sidebar-menu-item {{ menuActive('admin.deposit.commission') }}">
                                        <a class="nav-link" href="{{ route('admin.deposit.commission') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Commissions')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('pending-deposits')
                                    <li class="sidebar-menu-item {{ menuActive('admin.deposit.pending') }}">
                                        <a class="nav-link" href="{{ route('admin.deposit.pending') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Pending Deposits')</span>
                                            @if ($pendingDepositsCount)
                                                <span
                                                    class="menu-badge pill bg--danger ms-auto">{{ $pendingDepositsCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('approved-diposits')
                                    <li class="sidebar-menu-item {{ menuActive('admin.deposit.approved') }}">
                                        <a class="nav-link" href="{{ route('admin.deposit.approved') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Approved Deposits')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('successfull-deposits')
                                    <li class="sidebar-menu-item {{ menuActive('admin.deposit.successful') }}">
                                        <a class="nav-link" href="{{ route('admin.deposit.successful') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Successful Deposits')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('rejected-deposits')
                                    <li class="sidebar-menu-item {{ menuActive('admin.deposit.rejected') }}">
                                        <a class="nav-link" href="{{ route('admin.deposit.rejected') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Rejected Deposits')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('initiated-deposits')
                                    <li class="sidebar-menu-item {{ menuActive('admin.deposit.initiated') }}">

                                        <a class="nav-link" href="{{ route('admin.deposit.initiated') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Initiated Deposits')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('all-deposits')
                                    <li class="sidebar-menu-item {{ menuActive('admin.deposit.list') }}">
                                        <a class="nav-link" href="{{ route('admin.deposit.list') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('All Deposits')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('withdrawals')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.withdraw*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-bank"></i>
                            <span class="menu-title">@lang('Withdrawals') </span>
                            @if (0 < $pendingWithdrawCount)
                                <span class="menu-badge pill bg--danger ms-auto">
                                    <i class="fa fa-exclamation"></i>
                                </span>
                            @endif
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.withdraw*', 2) }}">
                            <ul>
                                @can('commission')
                                    <li class="sidebar-menu-item {{ menuActive('admin.withdraw.commission') }}">
                                        <a class="nav-link" href="{{ route('admin.withdraw.commission') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Commissions')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('withdrawals-methods')
                                    <li class="sidebar-menu-item {{ menuActive('admin.withdraw.method.*') }}">
                                        <a class="nav-link" href="{{ route('admin.withdraw.method.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Withdrawal Methods')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('pending-withdrawals')
                                    <li class="sidebar-menu-item {{ menuActive('admin.withdraw.pending') }}">
                                        <a class="nav-link" href="{{ route('admin.withdraw.pending') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Pending Withdrawals')</span>

                                            @if ($pendingWithdrawCount)
                                                <span
                                                    class="menu-badge pill bg--danger ms-auto">{{ $pendingWithdrawCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan

                                @can('approved-withdrawals')
                                    <li class="sidebar-menu-item {{ menuActive('admin.withdraw.approved') }}">
                                        <a class="nav-link" href="{{ route('admin.withdraw.approved') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Approved Withdrawals')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('rejected-withdrawals')
                                    <li class="sidebar-menu-item {{ menuActive('admin.withdraw.rejected') }}">
                                        <a class="nav-link" href="{{ route('admin.withdraw.rejected') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Rejected Withdrawals')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('all-withdrawals')
                                    <li class="sidebar-menu-item {{ menuActive('admin.withdraw.log') }}">
                                        <a class="nav-link" href="{{ route('admin.withdraw.log') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('All Withdrawals')</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcan

                @canany(['support-ticket', 'report'])
                    <li class="sidebar__menu-header">@lang('Support & Report')</li>
                @endcanany

                @can('support-ticket')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.ticket*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-ticket"></i>
                            <span class="menu-title">@lang('Support Ticket') </span>
                            @if (0 < $pendingTicketCount)
                                <span class="menu-badge pill bg--danger ms-auto">
                                    <i class="fa fa-exclamation"></i>
                                </span>
                            @endif
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.ticket*', 2) }}">
                            <ul>
                                @can('pending-ticket')
                                    <li class="sidebar-menu-item {{ menuActive('admin.ticket.pending') }}">
                                        <a class="nav-link" href="{{ route('admin.ticket.pending') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Pending Ticket')</span>
                                            @if ($pendingTicketCount)
                                                <span
                                                    class="menu-badge pill bg--danger ms-auto">{{ $pendingTicketCount }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endcan
                                @can('closed-ticket')
                                    <li class="sidebar-menu-item {{ menuActive('admin.ticket.closed') }}">
                                        <a class="nav-link" href="{{ route('admin.ticket.closed') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Closed Ticket')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('answered-ticket')
                                    <li class="sidebar-menu-item {{ menuActive('admin.ticket.answered') }}">
                                        <a class="nav-link" href="{{ route('admin.ticket.answered') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Answered Ticket')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('all-ticket')
                                    <li class="sidebar-menu-item {{ menuActive('admin.ticket.index') }}">
                                        <a class="nav-link" href="{{ route('admin.ticket.index') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('All Ticket')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('report')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.report*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-list"></i>
                            <span class="menu-title">@lang('Report') </span>
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.report*', 2) }}">
                            <ul>
                                @can('transection-log')
                                    <li
                                        class="sidebar-menu-item {{ menuActive(['admin.report.transaction', 'admin.report.transaction.search']) }}">
                                        <a class="nav-link" href="{{ route('admin.report.transaction') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Transaction Log')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('login-history')
                                    <li
                                        class="sidebar-menu-item {{ menuActive(['admin.report.login.history', 'admin.report.login.ipHistory']) }}">
                                        <a class="nav-link" href="{{ route('admin.report.login.history') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Login History')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('notification-history')
                                    <li class="sidebar-menu-item {{ menuActive('admin.report.notification.history') }}">
                                        <a class="nav-link" href="{{ route('admin.report.notification.history') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Notification History')</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('referral-commissions')
                                    <li class="sidebar-menu-item {{ menuActive('admin.report.referral.commissions') }}">
                                        <a class="nav-link" href="{{ route('admin.report.referral.commissions') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Referral Commissions')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @canany(['referral-setting', 'general-setting', 'system-configuration', 'logo-favicon', 'extensions',
                    'languages', 'seo-manager', 'kyc-setting', 'notification-setting'])
                    <li class="sidebar__menu-header">@lang('Settings')</li>
                @endcanany

                @can('referral-setting')
                    <li class="sidebar-menu-item {{ menuActive('admin.referral*') }}">
                        <a class="nav-link" href="{{ route('admin.referral.index') }}">
                            <i class="menu-icon las la-sitemap"></i>
                            <span class="menu-title">@lang('Referral Setting')</span>
                        </a>
                    </li>
                @endcan

                @can('general-setting')
                    <li class="sidebar-menu-item {{ menuActive('admin.setting.index') }}">
                        <a class="nav-link" href="{{ route('admin.setting.index') }}">
                            <i class="menu-icon las la-life-ring"></i>
                            <span class="menu-title">@lang('General Setting')</span>
                        </a>
                    </li>
                @endcan

                @can('system-configuration')
                    <li class="sidebar-menu-item {{ menuActive('admin.setting.system.configuration') }}">
                        <a class="nav-link" href="{{ route('admin.setting.system.configuration') }}">
                            <i class="menu-icon las la-cog"></i>
                            <span class="menu-title">@lang('System Configuration')</span>
                        </a>
                    </li>
                @endcan

                @can('logo-favicon')
                    <li class="sidebar-menu-item {{ menuActive('admin.setting.logo.icon') }}">
                        <a class="nav-link" href="{{ route('admin.setting.logo.icon') }}">
                            <i class="menu-icon las la-images"></i>
                            <span class="menu-title">@lang('Logo & Favicon')</span>
                        </a>
                    </li>
                @endcan

                @can('extensions')
                    <li class="sidebar-menu-item {{ menuActive('admin.extensions.index') }}">
                        <a class="nav-link" href="{{ route('admin.extensions.index') }}">
                            <i class="menu-icon las la-cogs"></i>
                            <span class="menu-title">@lang('Extensions')</span>
                        </a>
                    </li>
                @endcan

                @can('languages')
                    <li class="sidebar-menu-item {{ menuActive(['admin.language.manage', 'admin.language.key']) }}">
                        <a class="nav-link" data-default-url="{{ route('admin.language.manage') }}"
                            href="{{ route('admin.language.manage') }}">
                            <i class="menu-icon las la-language"></i>
                            <span class="menu-title">@lang('Language') </span>
                        </a>
                    </li>
                @endcan

                @can('seo-manager')
                    <li class="sidebar-menu-item {{ menuActive('admin.seo') }}">
                        <a class="nav-link" href="{{ route('admin.seo') }}">
                            <i class="menu-icon las la-globe"></i>
                            <span class="menu-title">@lang('SEO Manager')</span>
                        </a>
                    </li>
                @endcan

                @can('kyc-setting')
                    <li class="sidebar-menu-item {{ menuActive('admin.kyc.setting') }}">
                        <a class="nav-link" href="{{ route('admin.kyc.setting') }}">
                            <i class="menu-icon las la-user-check"></i>
                            <span class="menu-title">@lang('KYC Setting')</span>
                        </a>
                    </li>
                @endcan

                @can('notification-setting')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.setting.notification*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon las la-bell"></i>
                            <span class="menu-title">@lang('Notification Setting')</span>
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.setting.notification*', 2) }}">
                            <ul>
                                @can('global-template')
                                    <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.global') }}">
                                        <a class="nav-link" href="{{ route('admin.setting.notification.global') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Global Template')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('email-setting')
                                    <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.email') }}">
                                        <a class="nav-link" href="{{ route('admin.setting.notification.email') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Email Setting')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('sms-setting')
                                    <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.sms') }}">
                                        <a class="nav-link" href="{{ route('admin.setting.notification.sms') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('SMS Setting')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('notification-template')
                                    <li class="sidebar-menu-item {{ menuActive('admin.setting.notification.templates') }}">
                                        <a class="nav-link" href="{{ route('admin.setting.notification.templates') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Notification Templates')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @canany(['manage-tempalte', 'manage-section'])
                    <li class="sidebar__menu-header">@lang('Frontend Manager')</li>
                @endcanany

                @can('manage-tempalte')
                    <li class="sidebar-menu-item {{ menuActive('admin.frontend.templates') }}">
                        <a class="nav-link" href="{{ route('admin.frontend.templates') }}">
                            <i class="menu-icon la la-html5"></i>
                            <span class="menu-title">@lang('Manage Templates')</span>
                        </a>
                    </li>
                @endcan

                @can('manage-section')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.frontend.sections*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-puzzle-piece"></i>
                            <span class="menu-title">@lang('Manage Section')</span>
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.frontend.sections*', 2) }}">
                            <ul>
                                @php
                                    $lastSegment = collect(request()->segments())->last();
                                @endphp
                                @foreach (getPageSections(true) as $k => $secs)
                                    @if ($secs['builder'])
                                        <li class="sidebar-menu-item @if ($lastSegment == $k) active @endif">
                                            <a class="nav-link" href="{{ route('admin.frontend.sections', $k) }}">
                                                <i class="menu-icon las la-dot-circle"></i>
                                                <span class="menu-title">{{ __($secs['name']) }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endcan

                @canany(['maintenance-mode', 'gdrp-cookie', 'system', 'custom-css', 'report-and-request','domain-setup'])
                    <li class="sidebar__menu-header">@lang('Extra')</li>
                @endcanany

                @can('maintenance-mode')
                    <li class="sidebar-menu-item {{ menuActive('admin.maintenance.mode') }}">
                        <a class="nav-link" href="{{ route('admin.maintenance.mode') }}">
                            <i class="menu-icon las la-robot"></i>
                            <span class="menu-title">@lang('Maintenance Mode')</span>
                        </a>
                    </li>
                @endcan

                @can('gdrp-cookie')
                    <li class="sidebar-menu-item {{ menuActive('admin.setting.cookie') }}">
                        <a class="nav-link" href="{{ route('admin.setting.cookie') }}">
                            <i class="menu-icon las la-cookie-bite"></i>
                            <span class="menu-title">@lang('GDPR Cookie')</span>
                        </a>
                    </li>
                @endcan

                @can('system')
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a class="{{ menuActive('admin.system*', 3) }}" href="javascript:void(0)">
                            <i class="menu-icon la la-server"></i>
                            <span class="menu-title">@lang('System')</span>
                        </a>
                        <div class="sidebar-submenu {{ menuActive('admin.system*', 2) }}">
                            <ul>
                                @can('application')
                                    <li class="sidebar-menu-item {{ menuActive('admin.system.info') }}">
                                        <a class="nav-link" href="{{ route('admin.system.info') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Application')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('server')
                                    <li class="sidebar-menu-item {{ menuActive('admin.system.server.info') }}">
                                        <a class="nav-link" href="{{ route('admin.system.server.info') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Server')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('cache')
                                    <li class="sidebar-menu-item {{ menuActive('admin.system.optimize') }}">
                                        <a class="nav-link" href="{{ route('admin.system.optimize') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Cache')</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('update')
                                    <li class="sidebar-menu-item {{ menuActive('admin.system.update') }}">
                                        <a class="nav-link" href="{{ route('admin.system.update') }}">
                                            <i class="menu-icon las la-dot-circle"></i>
                                            <span class="menu-title">@lang('Update')</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                @can('custom-css')
                    <li class="sidebar-menu-item {{ menuActive('admin.setting.custom.css') }}">
                        <a class="nav-link" href="{{ route('admin.setting.custom.css') }}">
                            <i class="menu-icon lab la-css3-alt"></i>
                            <span class="menu-title">@lang('Custom CSS')</span>
                        </a>
                    </li>
                @endcan

                @can('report-and-request')
                    <li class="sidebar-menu-item {{ menuActive('admin.request.report') }}">
                        <a class="nav-link" data-default-url="{{ route('admin.request.report') }}"
                            href="{{ route('admin.request.report') }}">
                            <i class="menu-icon las la-bug"></i>
                            <span class="menu-title">@lang('Report & Request') </span>
                        </a>
                    </li>
                @endcan

                @can('domain-setup')
                    <li class="sidebar-menu-item {{ menuActive('admin.domain.list') }}">
                        <a class="nav-link" data-default-url="{{ route('admin.domain.list') }}"
                            href="{{ route('admin.domain.list') }}">
                            <i class="menu-icon las la-feather-alt"></i>
                            <span class="menu-title">@lang('Domain Setup') </span>
                        </a>
                    </li>
                @endcan
                
            </ul>
            <div class="text-uppercase mb-3 text-center">
                <span class="text--primary">Tram Bet</span>
                <span class="text--success">@lang('V'){{ systemDetails()['version'] }} </span>
            </div>
        </div>
    </div>
</div>
<!-- sidebar end -->

@push('script')
    <script>
        if ($('li').hasClass('active')) {
            $('#sidebar__menuWrapper').animate({
                scrollTop: eval($(".active").offset().top - 320)
            }, 500);
        }
    </script>
@endpush
