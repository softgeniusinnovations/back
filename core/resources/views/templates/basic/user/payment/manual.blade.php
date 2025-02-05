@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="card custom--card">
        <div class="card-body">
            <p class="text-center">@lang('You have requested') <b class="text--success">{{ showAmount($data['amount']) }} {{ __($general->cur_text) }}</b>, @lang('Please pay')
                <b class="text--success">{{ showAmount($data['final_amo']) . ' ' . $data['method_currency'] }} </b> @lang('for successful payment')
            </p>

            <form action="{{ route('user.deposit.manual.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <h4 class="text-center">@lang('Please follow the instruction below')</h4>
                <p class="text-center">@php echo  $data->gateway->description @endphp</p>

                <x-viser-form identifier="id" identifierValue="{{ $gateway->form_id }}" />

                <div class="text-end">
                    <button class="btn btn--xl btn--base " type="submit">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
