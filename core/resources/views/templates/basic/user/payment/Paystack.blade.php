@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title">@lang('Deposit Money via Paystack')</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3" action="{{ route('ipn.' . $deposit->gateway->alias) }}" method="POST">
                        @csrf
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between flex-wrap px-0">
                                <span>@lang('You have to pay')</span>
                                <span>{{ showAmount($deposit->final_amo) }} {{ __($deposit->method_currency) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between flex-wrap px-0">
                                <span>@lang('You will get')</span>
                                <span>{{ showAmount($deposit->amount) }} {{ __($general->cur_text) }}</span>
                            </li>
                        </ul>
                        <div class="text-end">
                            <button class="btn btn--xl btn--base" id="btn-confirm" type="button">@lang('Pay Now')</button>
                        </div>
                        <script src="//js.paystack.co/v1/inline.js" data-key="{{ $data->key }}" data-email="{{ $data->email }}" data-amount="{{ round($data->amount) }}" data-currency="{{ $data->currency }}" data-ref="{{ $data->ref }}" data-custom-button="btn-confirm"></script>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
