@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="card custom--card">
        <h5 class="card-header">
            <span class="card-header__icon">
                <i class="las la-file-invoice-dollar"></i>
            </span>
            @lang('Payment Preview')
        </h5>

        <div class="card-body text-center">
            <h5> @lang('PLEASE SEND EXACTLY') <span class="text--base"> {{ $data->amount }}</span> {{ __($data->currency) }}</h5>
            <h5 class="mb-4">@lang('TO') <span class="text--base"> {{ $data->sendto }}</span></h5>
            <img src="{{ $data->img }}" alt="@lang('Image')">
            <h5 class="text--base">@lang('SCAN TO SEND')</h5>
        </div>
    </div>
@endsection
