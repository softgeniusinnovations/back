@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="col-12">
        <div class="card custom--card">
            <h5 class="card-header">
                <span class="card-header__icon">
                    <i class="las la-user-check"></i>
                </span>
                @lang('KYC Form')
            </h5>
            <div class="card-body">
                <form action="{{ route('user.kyc.submit') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <x-viser-form identifier="act" identifierValue="kyc" />
                    <div class="text-end">
                        <button class="btn btn--base" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
