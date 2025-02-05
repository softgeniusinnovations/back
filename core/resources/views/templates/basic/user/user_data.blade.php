@extends($activeTemplate . 'layouts.frontend')
@section('frontend')
    <div class="login-page section">
        <div class="container">
            <div class="row g-3 justify-content-center">
                <div class="col-lg-6">
                    <div class="login-form">
                        <form action="{{ route('user.data.submit') }}" method="POST">
                            @csrf
                            <h4 class="login-form__title">{{ __($pageTitle) }}</h4>
                            <p class="text--base">@lang('Please complete this step to get full access.')</p>
                            <div class="row">
                                <div class="col-sm-6 col-xsm-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('First Name')</label>
                                        <input type="text" class="form-control form--control mb-3" name="firstname" value="{{ old('firstname') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xsm-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Last Name')</label>
                                        <input type="text" class="form-control form--control mb-3" name="lastname" value="{{ old('lastname') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xsm-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Address')</label>
                                        <input type="text" class="form-control form--control mb-3" name="address" value="{{ old('address') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xsm-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('State')</label>
                                        <input type="text" class="form-control form--control mb-3" name="state" value="{{ old('state') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xsm-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Zip Code')</label>
                                        <input type="text" class="form-control form--control mb-3" name="zip" value="{{ old('zip') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xsm-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('City')</label>
                                        <input type="text" class="form-control form--control mb-3" name="city" value="{{ old('city') }}" required>
                                    </div>
                                </div>
                                @if(auth()->user()->is_affiliate == 1)
                                <div class="col-sm-12 col-xsm-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Website / Youtube Link')</label>
                                        <input type="url" class="form-control form--control mb-3" name="website" value="{{ old('website') }}">
                                    </div>
                                </div>

                                {{-- <div class="col-sm-12 col-xsm-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Youtube channel link')</label>
                                        <input type="url" class="form-control form--control mb-3" name="youtube_link" value="{{ old('youtube_link') }}">
                                    </div>
                                </div> --}}
                                @endif

                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn--xl btn--base">
                                    @lang('Submit')
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .login-form {
            background: hsl(var(--white));
            border-radius: 5px;
            margin-top: 0px;
            box-shadow: 0px 3px 18px #ddddddab;
            border: 1px solid #ddddddad;
        }
    </style>
@endpush
