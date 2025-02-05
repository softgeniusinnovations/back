@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="row justify-content-end mb-3">
        <div class="col-xl-5 col-md-8">
            <form action="">
                <div class="d-flex flex-wrap gap-4">
                    
                </div>
            </form>
        </div>
    </div>

    <div class="bet-table">
        <div class="col-12">
            <h5 class="mb-3 mt-0">
                @lang('Referral Link')
            </h5>
            <div class="qr-code text--base mb-1">
                <div class="qr-code-copy-form" data-copy=true>
                    <input id="qr-code-text" type="text" value="{{ route('home') }}?reference={{ $user->referral_code }}"
                        readonly>
                    <button class="text-copy-btn copy-btn lh-1 text-white" data-bs-toggle="tooltip"
                        data-bs-original-title="@lang('Copy to clipboard')" type="button">@lang('Copy')</button>
                </div>
            </div>
        </div>
    </div>


@endsection
