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
                <input id="qr-code-text" type="text" value="{{ route('home') }}?reference={{ $user->referral_code }}" readonly>
    <button class="text-copy-btn copy-btn lh-1 text-white" data-bs-toggle="tooltip" data-bs-original-title="@lang('Copy to clipboard')" type="button">@lang('Copy</')< /button>
</div>
</div>
</div> --}}

{{-- @if (!$user->kv)
    <div class="col-12">
        @if ($user->kv == Status::KYC_UNVERIFIED)
        <div class="alert alert-warning mt-0" role="alert">
            <h5 class="m-0">@lang('KYC Verification Required')</h5>
            <hr>
            <p class="mb-0">
                {{ __(@$kycContent->data_values->for_verification) }}
                <a class="text--base" href="{{ route('user.kyc.form') }}">@lang('Click here to verify')</a>
            </p>
        </div>
        @elseif($user->kv == Status::KYC_PENDING)
        <div class="alert alert-info" role="alert">
            <h5 class="m-0">@lang('KYC Verification Pending')</h5>
            <hr>
            <p class="mb-0">
                {{ __(@$kycContent->data_values->for_pending) }}
                <a class="text--base" href="{{ route('user.kyc.data') }}">@lang('See KYC data')</a>
            </p>
        </div>
        @endif
    </div>
    @endif --}}

    @if(auth()->user()->is_affiliate == 1)
    <div class="col-sm-4 col-lg-4 col-xl-4">
        @php
            $affiliate_temp_balance = auth()->user()->affiliate_temp_balance;
        $amount = 0;
        if ($affiliate_temp_balance >= 30 && now()->dayOfWeek == Carbon\Carbon::MONDAY) {
            $amount = $affiliate_temp_balance;
        }
        @endphp
        <x-affiliate-dashboard-widget title="Minimum withdrawal amount 30$" link="#" icon="las la-wallet"  amount="{{ $amount }} USD | {{ ceil(convertCurrency($amount, 'USD', auth()->user()->currency )) }} {{ __(getSymbol(userCurrency())) }}" />
    </div>
    @endif
{{-- <div class="col-sm-6 col-lg-6 col-xl-3">
    <x-affiliate-dashboard-widget title="{{auth()->user()->currency}} to USD" link="#" icon="las la-wallet" amount="{{ convertCurrency(auth()->user()->balance, auth()->user()->currency, 'USD') }} USD" />
</div> --}}
{{--<div class="col-sm-6 col-lg-6 col-xl-3">
    <x-affiliate-dashboard-widget title="1 {{auth()->user()->currency}} = USD Rate" link="#" icon="las la-wallet" amount="{{ convertCurrency(1, auth()->user()->currency, 'USD') }} USD" />
</div>--}}
{{--<div class="col-sm-6 col-lg-6 col-xl-3">
    <x-affiliate-dashboard-widget title="Total User" link="#" icon="las la-wallet" amount="{{ $promocodeUser->count() }}" />
</div>--}}

{{--<div class="col-sm-6 col-lg-6 col-xl-3">
    <x-affiliate-dashboard-widget title="Total Promo Code" link="#" icon="las la-wallet" amount="{{ $promoCode->count() }}" />
</div>--}}
<div class="col-sm-6 col-lg-6 col-xl-4">
    <x-affiliate-dashboard-widget title="Yesterday Earn" link="#" icon="las la-wallet" amount="{{ $yestrdayEarn }} {{ __(getSymbol(userCurrency())) }}" />
</div>
<div class="col-sm-6 col-lg-6 col-xl-4">
    <x-affiliate-dashboard-widget title="Current Month" link="#" icon="las la-wallet" amount="{{ $currentMonthEarn }} {{ __(getSymbol(userCurrency())) }}" />
</div>
<div class="col-sm-6 col-lg-6 col-xl-4">
    <x-affiliate-dashboard-widget title="30 Days" link="#" icon="las la-wallet" amount="{{ $thirtyDaysEarn }} {{ __(getSymbol(userCurrency())) }}" />
</div>
<div class="col-sm-6 col-lg-6 col-xl-4">
    <x-affiliate-dashboard-widget title="Total" link="#" icon="las la-wallet" amount="{{ showAmount(auth()->user()->affiliate_balance) }} {{ __(getSymbol(userCurrency())) }}" />
</div>


<hr>

<div class="col-12">
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <h6>Registration Statistic</h6>
            </div>

            <div>
                <canvas id="register-statistic"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <h6>Earning Statistic</h6>
            </div>

            <div>
                <canvas id="Earning-Statistic"></canvas>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
@push('style-lib')
<link href="{{ asset('assets/global/css/daterangepicker.css') }}" rel="stylesheet">
@endpush
@push('script-lib')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/global/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/global/js/daterangepicker.min.js') }}"></script>
@endpush
@push('script')
<script>
    const ctx = document.getElementById('register-statistic');
    let monthwiseData = JSON.parse('{!! json_encode($promoCodeUserData) !!}');
    // console.log(Object.values(monthwiseData));
    new Chart(ctx, {
        type: 'line'
        , data: {
            labels: (Object.keys(monthwiseData))
            , datasets: [{
                label: 'Registration Statistic'
                , data: (Object.values(monthwiseData))
                , borderWidth: 1
            }]
        }
        , options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

</script>
<script>
    const ctx1 = document.getElementById('Earning-Statistic');
    let earningData = JSON.parse('{!! json_encode($affiliateCommisionData) !!}');
    new Chart(ctx1, {
        type: 'line'
        , data: {
            labels: (Object.keys(earningData))
            , datasets: [{
                label: 'Earning Statistic'
                , data: (Object.values(earningData))
                , borderWidth: 1
            }]
        }
        , options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

</script>
@endpush
