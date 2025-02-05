<form class="verify-gcaptcha" action="{{ route('user.oneclick.register') }}" method="POST">
    @csrf
    <div class="row">
        @if (session()->get('reference') != null)
            <div class="form-group">
                <label class="form-label">@lang('Reference Id')</label>
                <input class="form-control form--control" type="text" value="{{ session()->get('reference') }}" readonly>
            </div>
        @endif
        {{-- <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Username')</label>
                <input class="form-control form--control checkUser" name="username" type="text" value="{{ old('username') }}" required>
                <small class="text--danger usernameExist"></small>
            </div>
        </div> --}}

        {{-- <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Email')</label>
                <input class="form-control form--control checkUser" name="email" type="email" value="{{ old('email') }}">
            </div>
        </div> --}}

        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Country')</label>
                <div class="form--select">
                    <select class="form-select" name="country" id="country" required>
                        @if ($country != null)
                        <option data-code="{{ $country }}" value="{{ $country}}" >
                            {{ __($country) }}</option>
                        @else
                        <option value="">@lang('Select Country')</option>
                        @endif
                        @foreach ($countries as $key => $country)
                        <option data-code="{{ $key }}" value="{{ $country->country }}" {{ old('country') == $country->country || $country->country == 'Bangladesh' ? 'selected' : '' }}>
                            {{ __($country->country) }}</option>
                        @endforeach
                    </select>
                        <input class="form-control" name="country_code" type="hidden" id="country_code" value="BD">
                </div>
            </div>
        </div>


        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Currency')</label>
                <div class="form--select">
                    <select class="form-select" name="currencies" id="currencies" required>
                        @foreach ($currencies as $key => $currencies)
                        <option value="{{ $currencies->currency_code }}" {{ old('currencies') == $currencies->currency_code || $currencies->currency_code == 'BDT' ? 'selected' : '' }}>
                            {{ __($currencies->currency_code) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>


        <div class="col-md-12 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Promo (Optional)')</label>
                <input class="form-control form--control" name="promo" type="text" value="{{ session()->get('promo', old('promo')) }}" {{ session()->has('promo') ? 'readonly' : '' }}>
            </div>
        </div>

        <x-captcha />

        @if ($general->agree)
            <div class="col-12">
                <div class="form-group form-check d-flex align-items-start gap-2">
                    <input class="form-check-input custom--check mt-2 border" id="agree" name="agree" type="checkbox" @checked(old('agree')) required >
                    <div>
                        <label class="form-check-label sm-text t-heading-font heading-clr fw-md" for="agree">
                            @lang('I agree with')
                        </label>
                        <span class="sm-text">
                            @foreach ($policyElements as $policy)
                                <a href="{{ route('policy.pages', [slug(__(@$policy->data_values->title)), @$policy->id]) }}" target="_blank">{{ __($policy->data_values->title) }}</a>
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <button class="btn btn--xl btn--base w-100" type="submit">
                @lang('Register')
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
        <span class="d-inline-block sm-text"> @lang('Already have an account?') </span>
        <a class="t-link d-inline-block t-link--base base-clr sm-text lh-1 text-center text-end" href="{{ route('user.login') }}">
            @lang('Login')
        </a>
    </div>
</form>

@push('style')
    <style>
        .form-group {
            margin: 8px 0;
        }
    </style>
@endpush

@if ($general->secure_password)
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif

@push('script')
    <script>
        "use strict";
        (function($) {
            //if select country then country code will update
            $('[name=country]').on('change', function() {
                $('[name=country_code]').val($(this).find('option:selected').data('code'));
                // $('[name=mobile_code]').val(mobile_code);
            });

            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                if ($(this).attr('name') == 'mobile') {
                    var mobile = `${$('[name=mobile_code]').val()}${value}`;
                    var data = {
                        mobile: mobile,
                        _token: token
                    };
                }

                if ($(this).attr('name') == 'email') {
                    var data = {
                        email: value,
                        _token: token
                    }
                }

                if ($(this).attr('name') == 'username') {
                    var data = {
                        username: value,
                        _token: token
                    }
                }
                $.post(url, data, function(response) {
                    if (response.data != false && response.type == 'email') {
                        $('#existModalCenter').modal('show');
                    } else if (response.data != false) {
                        $(`.${response.type}Exist`).text(`This ${response.type} is already exist`);
                    } else {
                        $(`.${response.type}Exist`).empty();
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
