<form class="verify-gcaptcha" action="{{ route('user.register') }}" method="POST">
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

        <div class="col-md-12 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Email')</label>
                <input class="form-control form--control checkUser" name="email" type="email" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Country')</label>
                <div class="form--select">
                    <select class="form-select" name="country" required>
                        @foreach ($countries as $key => $country)
                            <option data-mobile_code="{{ $country->dial_code }}" data-code="{{ $key }}" value="{{ $country->country }}" {{ old('country') == $country->country? 'selected' : '' }}>
                                {{ __($country->country) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Mobile')</label>
                <div class="input-group">
                    <span class="input-group-text mobile-code"></span>
                    <input name="mobile_code" type="hidden">
                    <input name="country_code" type="hidden">
                    <input class="form-control phone-number form--control checkUser border-start-0 px-1" name="mobile" type="number" value="{{ old('mobile') }}" required>
                </div>
                <small class="text--danger mobileExist"></small>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Currency')</label>
                <div class="form--select">
                    <select class="form-select" name="currencies" required>
                        <option value="">@lang('Select Currency')</option>
                        @foreach ($currencies as $key => $currencies)
                                <option data-mobile_code="{{ $currencies->currency_code }}" data-code="{{ $key }}" value="{{ $currencies->currency_code }}" {{ old('currencies') == $currencies->currency_code ? 'selected' : '' }}>
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
        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Password')</label>
                <div class="input-group input--group">
                    <input class="form-control form--control @if ($general->secure_password) secure-password @endif" name="password" type="password" required>
                    <span class="input-group-text pass-toggle">
                        <i class="las la-eye"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12">
            <div class="form-group">
                <label class="form-label">@lang('Confirm Password')</label>
                <div class="input-group input--group">
                    <input class="form-control form--control" name="password_confirmation" type="password" required>
                    <span class="input-group-text pass-toggle">
                        <i class="las la-eye"></i>
                    </span>
                </div>
            </div>
        </div>

        <x-captcha />

        @if ($general->agree)
            <div class="col-12">
                <div class="form-group form-check d-flex flex-wrap align-items-center gap-2">
                    <input class="form-check-input custom--check" id="agree" name="agree" type="checkbox" @checked(old('agree')) required>
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

        <div class="col-6">
            <button class="btn btn--xl btn--base w-100" type="submit">
                @lang('Register')
            </button>
        </div>
        <div class="col-6">
            <a href="{{ route('user.oneclick.register') }}" class="btn btn--xl btn--base w-100" type="button">
                @lang('One Click')
            </a>
        </div>
    </div>

    <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
        <span class="d-inline-block sm-text"> @lang('Already have an account?') </span>
        <a class="t-link d-inline-block t-link--base base-clr sm-text lh-1 text-center text-end" href="{{ route('user.login') }}">
            @lang('Login')
        </a>
    </div>
</form>


<div class="modal fade login-modal" id="oneClickModal" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body p-3 p-sm-5">
                <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                    <i class="las la-times"></i>
                </span>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="mt-0">{{ __(@$loginContent->data_values->heading) }}</h4>
                </div>
                {{-- @include($activeTemplate . 'partials.login') --}}
            </div>
        </div>
    </div>
</div>
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


            $('.mobile-code').on('click', function(e) {
                $('[name=mobile]').focus();
            });

            $('[name=country]').on('change', function() {
                $('[name=mobile_code]').val($(this).find('option:selected').data('mobile_code'));
                $('[name=country_code]').val($(this).find('option:selected').data('code'));
                $('.mobile-code').text('+' + $(this).find('option:selected').data('mobile_code'));
            }).change();

            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif

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
