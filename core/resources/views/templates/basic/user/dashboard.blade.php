@extends($activeTemplate . 'layouts.master')
@section('master')
    @php
        $kycContent = getContent('kyc_instructions.content', true);
    @endphp
    <div class="row gy-4">
        {{-- <div class="col-12">
            <h5 class="mb-3 mt-0">
                @lang('Referral Link')
            </h5>
            <div class="qr-code text--base mb-1">
                <div class="qr-code-copy-form" data-copy=true>
                    <input id="qr-code-text" type="text" value="{{ route('home') }}?reference={{ $user->referral_code }}"
                        readonly>
                    <button class="text-copy-btn copy-btn lh-1 text-white" data-bs-toggle="tooltip"
                        data-bs-original-title="@lang('Copy to clipboard')" type="button">@lang('Copy</')</button>
                </div>
            </div>
        </div> --}}
        


        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Total Deposited" link="{{ route('user.deposit.history') }}" icon="las la-wallet"
                amount="{{ showAmount($widget['totalDeposit']) }} {{ userCurrency() }}" />
        </div>
        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Total Withdrawan" link="{{ route('user.withdraw.history') }}"
                icon="las la-money-bill-wave" amount="{{ showAmount($widget['totalWithdraw']) }} {{ userCurrency() }}" />
        </div>
        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Total Bet" link="{{ route('user.bets') }}" icon="las la-gamepad"
                amount="{{ getAmount($widget['totalBet']) }}" />
        </div>
        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Pending Bet" link="{{ route('user.bets') }}" icon="las la-spinner"
                amount="{{ getAmount($widget['pendingBet']) }}" />
        </div>
        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Won Bet" link="{{ route('user.bets', 'won') }}" icon="las la-trophy"
                amount="{{ getAmount($widget['wonBet']) }}" />
        </div>
        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Lose Bet" link="{{ route('user.bets', 'lose') }}" icon="las la-frown"
                amount="{{ getAmount($widget['loseBet']) }}" />
        </div>
        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Refunded Bet" link="{{ route('user.bets', 'refunded') }}"
                icon="las la-undo-alt" amount="{{ getAmount($widget['refundedBet']) }}" />
        </div>
        <div class="col-sm-6 col-lg-6 col-xl-4">
            <x-user-dashboard-widget title="Transaction" link="{{ route('user.transactions') }}" icon="las la-exchange-alt"
                amount="{{ getAmount($widget['totalTransaction']) }}" />
        </div>
        <div class="col-12 col-xl-4">
            <x-user-dashboard-widget title="Support Tickets" link="{{ route('ticket.index') }}" icon="las la-ticket-alt"
                amount="{{ getAmount($widget['totalTicket']) }}" />
        </div>
        <div class="col-12">
            <div class="bet-chart-heading-area d-flex justify-content-between align-items-center">
                <h5>@lang('Bet Chart')</h5>
                <input class="form-control w-auto bg-white" name="date" type="text" value="{{ request()->date }}"
                    autocomplete="off" placeholder="@lang('Start Date - End Date')">
            </div>
            <div class="card custom--card">
                <div class="card-body">
                    <div id="betChart"></div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <h5 class="mb-2 mt-2">
                @lang('Latest Transaction History')
            </h5>
            @include($activeTemplate . 'partials.transaction_table')
        </div>
    </div>

    {{-- Show modal if one_time_pass is not null --}}
    {{-- <input class="d-none one_time_pass" id="one_time_pass" data-id="{{ auth()->user()->id }}"
        value="{{ auth()->user()->one_time_pass }}">
    <div class="modal fade" id="exampleModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">User Information</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>UserId: <input type="text" class="border-0" id="userName" readonly
                            value="{{ auth()->user()->username }}"></h6>
                    <h6>Password:
                        <input type="text" class="border-0" readonly id="passwords"
                            value="{{ auth()->user()->one_time_pass }}">
                    </h6>

                    <small class="text-danger">Please Save UserId and Password</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary copyBtn">Copy</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> --}}


@endsection

@push('style-lib')
    <link href="{{ asset('assets/global/css/daterangepicker.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/daterangepicker.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            // $('.copyBtn').on('click', function() {
            //     var copyText = document.getElementById("textToCopy");
            //     copyText.select();
            //     copyText.setSelectionRange(0, 99999);
            //     document.execCommand("copy");
            //     iziToast.success({
            //         message: "Copied: " + copyText.value,
            //         position: "topRight"
            //     });
            // });

            var startsOne;
            var endOne;
            let startDate;
            let endDate;

            @if (@$request->starts_from_start)
                startsOne = moment(`{{ @$request->startDate }}`);
            @endif

            @if (@$request->starts_from_end)
                endOne = moment(`{{ @$request->endDate }}`);
            @endif


            function intDateRangePicker(element, start, end) {
                $(element).daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Clear': ['', ''],
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')],
                    }
                });

                $(element).on('apply.daterangepicker', function(ev, picker) {
                    if (!(picker.startDate.isValid() && picker.endDate.isValid())) {
                        $(element).val('');
                    }
                    window.location = appendQueryParameter('date', this.value);
                });
            }

            intDateRangePicker('[name=date]', startsOne, endOne);

            var betOptions = {
                series: [{
                    name: 'Total Stake',
                    data: [
                        @foreach ($report['bet_stake_amount'] as $stakeAmount)
                            "{{ $stakeAmount }}",
                        @endforeach
                    ]
                }, {
                    name: 'Total Return',
                    data: [
                        @foreach ($report['bet_return_amount'] as $returnAmount)
                            "{{ $returnAmount }}",
                        @endforeach
                    ]
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true,
                        tools: {
                            download: false
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
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
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
                    categories: [
                        @foreach ($report['bet_dates'] as $date)
                            "{{ $date }}",
                        @endforeach
                    ],
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return `${val} {{ userCurrency() }}`
                        }
                    }
                },
            };
            var chart = new ApexCharts(document.querySelector("#betChart"), betOptions);
            chart.render();
        })(jQuery);
    </script>

  <!-- <script>
        $(document).ready(function() {
            var one_time_pass = $('#one_time_pass').val();
            if (one_time_pass != null && one_time_pass != '') {
                $('.modal').modal('show');
                var id = $('.one_time_pass').data('id');
                $.ajax({
                    url: "{{ route('user.one.time.pass') }}",
                    method: "POST",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        console.log(response);
                    }
                });
            } else {
                $('.modal').modal('hide');
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.copyBtn').on('click', function() {
                var username = $("#userName").val();
                var password = $("#passwords").val();
                var copyText = "UserId : " + username + "\n" + "Password : " + password;
                navigator.clipboard.writeText(copyText);
                iziToast.success({
                    message: "Copied: " + copyText,
                    position: "topRight"
                });
            });
        });
    </script> -->
@endpush
