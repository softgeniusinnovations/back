@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="two-factor">
        <div class="row justify-content-center gy-4">
            @if (!auth()->user()->ts)
                <div class="col-xl-6">
                    <div class="card custom--card">
                        <h5 class="card-header">
                            <i class="las la-user-shield"></i>
                            @lang('Add Your Account')
                        </h5>
                        <div class="card-body">
                            <div class="qr-code-wrapper rounded-2 text-center">
                                <img src="{{ $qrCodeUrl }}" alt="@lang('image')">
                            </div>
                            <label class="form-label">@lang('First Name')</label>
                            <div class="qr-code text--base mb-1">
                                <div class="qr-code-copy-form" data-copy=true>
                                    <input id="qr-code-text" type="text" value="{{ $secret }}" readonly>
                                    <button class="text-copy-btn copy-btn lh-1 text-white" data-bs-toggle="tooltip" data-bs-original-title="@lang('Copy to clipboard')" type="button">@lang('Copy</')</button>
                                </div>
                            </div>
                            <code class="d-flex xsm-text text-muted gap-2">
                                <span class="d-inline-block">
                                    <i class="fas fa-info-circle"></i>
                                </span>
                                <span class="d-inline-block">
                                    @lang('If you have any problem with scanning the QR code enter the code manually'). <a
                                        class="text--base" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank">@lang('App link')</a>
                                </span>
                            </code>
                        </div>
                    </div>

                </div>

                <div class="col-xl-6">
                    <div class="card custom--card">
                        <h5 class="card-header">
                            <i class="las la-user-shield"></i>
                            @lang('Enable 2FA Authenticator')
                        </h5>

                        <div class="card-body">
                            <div class="qr-code-content">
                                <p class="text--dark mb-1">@lang('Google Authenticatior OTP') <span class="text--danger">*</span></p>
                                <div class="qr-code">
                                    <form class="qr-code-copy-form" action="{{ route('user.twofactor.enable') }}" method="POST">
                                        @csrf
                                        <input name="key" type="hidden" value="{{ $secret }}">
                                        <input name="code" type="text" required>
                                        <button class="text-copy-btn lh-1 text-white" type="submit">@lang('Apply')</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

            @if (auth()->user()->ts)
                <div class="col-lg-6">
                    <div class="card custom--card">
                        <h5 class="card-header">
                            <span class="card-header__icon">
                                <i class="las la-user-shield"></i>
                            </span>
                            @lang('Disable 2FA Authenticator')
                        </h5>
                        <div class="card-body">
                            <div class="qr-code-content">
                                <p class="text--dark mb-1">@lang('Google Authenticatior OTP') <span class="text--danger">*</span></p>
                                <div class="qr-code">
                                    <form class="qr-code-copy-form" action="{{ route('user.twofactor.disable') }}" method="POST">
                                        @csrf
                                        <input name="key" type="hidden" value="{{ $secret }}">
                                        <input name="code" type="text" required>
                                        <button class="text-copy-btn lh-1 text-white" type="submit">@lang('Apply')</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
