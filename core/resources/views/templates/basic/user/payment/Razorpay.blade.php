@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title">@lang('Deposit Money via Razorpay')</h5>
                </div>
                <div class="card-body">
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

                    <form action="{{ $data->url }}" class="text-end" method="{{ $data->method }}">
                        <input name="hidden" type="hidden" custom="{{ $data->custom }}">
                        <script src="{{ $data->checkout_js }}" @foreach ($data->val as $key => $value)
                                data-{{ $key }}="{{ $value }}" @endforeach></script>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('input[type="submit"]').addClass("btn btn--xl btn--base");
        })(jQuery);
    </script>
@endpush
