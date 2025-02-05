@extends('admin.layouts.app')

@section('panel')
   {{-- @if (@json_decode($general->system_info)->version > systemDetails()['version'])
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-warning mb-3 text-white">
                    <div class="card-header">
                        <h3 class="card-title"> @lang('New Version Available') <button class="btn btn--dark float-end">@lang('Version')
                                {{ json_decode($general->system_info)->version }}</button> </h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title text-dark">@lang('What is the Update?')</h5>
                        <p>
                            <pre class="f-size--24">{{ json_decode($general->system_info)->details }}</pre>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}
    @if (@json_decode($general->system_info)->message)
        <div class="row">
            @foreach (json_decode($general->system_info)->message as $msg)
                <div class="col-md-12">
                    <div class="alert border--primary border" role="alert">
                        <div class="alert__icon bg--primary">
                            <i class="far fa-bell"></i>
                        </div>
                        <p class="alert__message">@php echo $msg; @endphp</p>
                        <button class="close" data-bs-dismiss="alert" type="button" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    
    
    

    <div class="row gy-4">
        
        @if(auth()->user()->type == 1 && auth()->user()->balance < $threshold->amount)
        <div class="col-md-12">
            <div class="alert alert-danger p-4 d-flex align-items-center justify-content-between">
                <p>“Attention! Your account inactive automatically by balance. Please top up your prepaid balance ”</p>
                <a href="{{route('admin.agent.deposit.create')}}" class="btn btn-sm btn-primary">Top up</a>
            </div>
        </div>
        @endif
        
        @can('total-bettor-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $widget['totalUsers'] }}" title="Total Bettors" style="2" color="info" bg="white"
                    icon="la la-users"
                    link="{{ auth()->guard('admin')->user()->can('total-bettor-link')? route('admin.users.all'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('active-bettor-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $widget['verifiedUsers'] }}" title="Active Bettors" style="2" color="success"
                    bg="white" icon="la la-user-check"
                    link="{{ auth()->guard('admin')->user()->can('active-bettor-link')? route('admin.users.active'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('email-unverified-bettor-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $widget['emailUnverifiedUsers'] }}" title="Email Unverified Bettors" style="2"
                    color="danger" bg="white" icon="lar la-envelope"
                    link="{{ auth()->guard('admin')->user()->can('email-unverified-bettor-link')? route('admin.users.email.unverified'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('mobile-unverified-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $widget['mobileUnverifiedUsers'] }}" title="Mobile Unverified Bettors" style="2"
                    color="red" bg="white" icon="las la-comment-slash"
                    link="{{ auth()->guard('admin')->user()->can('mobile-unverified-link')? route('admin.users.mobile.unverified'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
    </div>

    <div class="row gy-4 mt-2">
        @can('pending-bet-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $widget['pendingBet'] }}" title="Pending Bet" style="2" color="warning" bg="white"
                    icon="las la-pause-circle"
                    link="{{ auth()->guard('admin')->user()->can('pending-bet-link')? route('admin.bet.pending'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('pending-deposit-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $deposit['total_deposit_pending'] }}" title="Pending Deposits" style="2"
                    bg="white" color="amber" icon="las la-wallet"
                    link="{{ auth()->guard('admin')->user()->can('pending-deposit-link')? route('admin.deposit.pending'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('pending-withdraw-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $withdrawals['total_withdraw_pending'] }}" title="Pending Withdrawals" style="2"
                    bg="white" color="orange" icon="las la-credit-card"
                    link="{{ auth()->guard('admin')->user()->can('pending-withdraw-link')? route('admin.withdraw.pending'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('pending-ticket-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ $withdrawals['total_withdraw_pending'] }}" title="Pending Tickets" style="2"
                    bg="white" color="warning" icon="la la-ticket"
                    link="{{ auth()->guard('admin')->user()->can('pending-ticket-link')? route('admin.withdraw.pending'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
    </div>

    <div class="row gy-4 mt-2">
        @can('total-deposit-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ auth()->user()->currency }}{{ showAmount($deposit['total_deposit_amount']) }}"
                    title="Total Deposited" style="2" bg="white" color="success" icon="las la-wallet"
                    link="{{ auth()->guard('admin')->user()->can('total-deposit-link')? route('admin.deposit.list'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('deposit-charge-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ auth()->user()->currency }}{{ showAmount($deposit['total_deposit_charge']) }}"
                    title="Deposited Charge" style="2" bg="white" color="primary" icon="las la-percent"
                    link="{{ auth()->guard('admin')->user()->can('deposit-charge-link')? route('admin.deposit.list'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('total-withdraw-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ auth()->user()->currency }}{{ showAmount($withdrawals['total_withdraw_amount']) }}"
                    title="Total Withdrawn" style="2" bg="white" color="danger" icon="lar la-credit-card"
                    link="{{ auth()->guard('admin')->user()->can('total-withdraw-link')? route('admin.withdraw.log'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
        @can('withdraw-charge-card')
            <div class="col-xxl-3 col-sm-6">
                <x-widget value="{{ auth()->user()->currency }}{{ showAmount($withdrawals['total_withdraw_charge']) }}"
                    title="Withdrawal Charge" style="2" bg="white" color="amber" icon="las la-percent"
                    link="{{ auth()->guard('admin')->user()->can('withdraw-charge-link')? route('admin.withdraw.log'): '' }}"
                    overlay_icon=0 icon_style="solid" />
            </div>
        @endcan
    </div>

    <div class="row mb-none-30 mt-30">
        @can('monthly-deposit-withdraw-report')
            <div class="col-xl-6 mb-30">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Monthly Deposit & Withdraw Report') (@lang('Last 12 Month'))</h5>
                        <div id="apex-bar-chart"> </div>
                    </div>
                </div>
            </div>
        @endcan
        @can('transaction-report')
            <div class="col-xl-6 mb-30">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Transactions Report') (@lang('Last 30 Days'))</h5>
                        <div id="apex-line"></div>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    <div class="row mb-none-30 mt-5">
        @can('login-by-browser')
            <div class="col-xl-4 col-lg-6 mb-30">
                <div class="card overflow-hidden">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Login By Browser') (@lang('Last 30 days'))</h5>
                        <canvas id="userBrowserChart"></canvas>
                    </div>
                </div>
            </div>
        @endcan
        @can('login-by-os')
            <div class="col-xl-4 col-lg-6 mb-30">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Login By OS') (@lang('Last 30 days'))</h5>
                        <canvas id="userOsChart"></canvas>
                    </div>
                </div>
            </div>
        @endcan
        @can('login-by-country')
            <div class="col-xl-4 col-lg-6 mb-30">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">@lang('Login By Country') (@lang('Last 30 days'))</h5>
                        <canvas id="userCountryChart"></canvas>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    @include('admin.partials.cron_modal')
@endsection

@push('style')
    <style>
        .widget:hover {
            transform: unset;
        }
    </style>
@endpush

@push('script')
    <script src="{{ asset('assets/global/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>

    <script>
        "use strict";


        var options = {
            series: [{
                name: 'Total Deposit',
                data: [
                    @foreach ($months as $month)
                        {{ getAmount(@$depositsMonth->where('months', $month)->first()->depositAmount) }},
                    @endforeach
                ]
            }, {
                name: 'Total Withdraw',
                data: [
                    @foreach ($months as $month)
                        {{ getAmount(@$withdrawalMonth->where('months', $month)->first()->withdrawAmount) }},
                    @endforeach
                ]
            }],
            chart: {
                type: 'bar',
                height: 450,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '50%',
                    endingShape: 'rounded'
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: @json($months),
            },
            yaxis: {
                title: {
                    text: "{{ __(auth()->user()->currency) }}",
                    style: {
                        color: '#7c97bb'
                    }
                }
            },
            grid: {
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: false
                    }
                },
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return "{{ __(auth()->user()->currency) }}" + val + " "
                    }
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#apex-bar-chart"), options);
        chart.render();

        var ctx = document.getElementById('userBrowserChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_browser_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_browser_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                maintainAspectRatio: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });



        var ctx = document.getElementById('userOsChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_os_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_os_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(0, 0, 0, 0.05)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            },
        });


        // Donut chart
        var ctx = document.getElementById('userCountryChart');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($chart['user_country_counter']->keys()),
                datasets: [{
                    data: {{ $chart['user_country_counter']->flatten() }},
                    backgroundColor: [
                        '#ff7675',
                        '#6c5ce7',
                        '#ffa62b',
                        '#ffeaa7',
                        '#D980FA',
                        '#fccbcb',
                        '#45aaf2',
                        '#05dfd7',
                        '#FF00F6',
                        '#1e90ff',
                        '#2ed573',
                        '#eccc68',
                        '#ff5200',
                        '#cd84f1',
                        '#7efff5',
                        '#7158e2',
                        '#fff200',
                        '#ff9ff3',
                        '#08ffc8',
                        '#3742fa',
                        '#1089ff',
                        '#70FF61',
                        '#bf9fee',
                        '#574b90'
                    ],
                    borderColor: [
                        'rgba(231, 80, 90, 0.75)'
                    ],
                    borderWidth: 0,

                }]
            },
            options: {
                aspectRatio: 1,
                responsive: true,
                elements: {
                    line: {
                        tension: 0 // disables bezier curves
                    }
                },
                scales: {
                    xAxes: [{
                        display: false
                    }],
                    yAxes: [{
                        display: false
                    }]
                },
                legend: {
                    display: false,
                }
            }
        });

        // apex-line chart
        var options = {
            chart: {
                height: 450,
                type: "area",
                toolbar: {
                    show: false
                },
                dropShadow: {
                    enabled: true,
                    enabledSeries: [0],
                    top: -2,
                    left: 0,
                    blur: 10,
                    opacity: 0.08
                },
                animations: {
                    enabled: true,
                    easing: 'linear',
                    dynamicAnimation: {
                        speed: 1000
                    }
                },
            },
            dataLabels: {
                enabled: false
            },
            series: [{
                    name: "Plus Transactions",
                    data: [
                        @foreach ($trxReport['date'] as $trxDate)
                            {{ @$plusTrx->where('date', $trxDate)->first()->amount ?? 0 }},
                        @endforeach
                    ]
                },
                {
                    name: "Minus Transactions",
                    data: [
                        @foreach ($trxReport['date'] as $trxDate)
                            {{ @$minusTrx->where('date', $trxDate)->first()->amount ?? 0 }},
                        @endforeach
                    ]
                }
            ],
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: [
                    @foreach ($trxReport['date'] as $trxDate)
                        "{{ $trxDate }}",
                    @endforeach
                ]
            },
            grid: {
                padding: {
                    left: 5,
                    right: 5
                },
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: false
                    }
                },
            },
        };

        var chart = new ApexCharts(document.querySelector("#apex-line"), options);

        chart.render();
    </script>
@endpush
