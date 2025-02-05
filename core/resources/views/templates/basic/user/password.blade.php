@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="card custom--card">
        <h5 class="card-header">
            <i class="las la-key"></i>
            @lang('Change Password')
        </h5>
        <div class="card-body">
            <form action="" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">@lang('Current Password')</label>
                            <div class="input-group input--group">
                                <input class="form-control form--control" name="current_password" type="password" required>
                                <span class="input-group-text pass-toggle">
                                    <i class="las la-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">@lang('Password')</label>
                            <div class="input-group input--group">
                                <input class="form-control form--control @if ($general->secure_password) secure-password @endif" name="password" type="password" required autocomplete="current-password">
                                <span class="input-group-text pass-toggle">
                                    <i class="las la-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">@lang('Confirm Password')</label>
                            <div class="input-group input--group">
                                <input class="form-control form--control" name="password_confirmation" type="password" required>
                                <span class="input-group-text pass-toggle">
                                    <i class="las la-eye"></i>
                                </span>
                            </div>
                            <small class="text--danger passNotMatch"></small>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button class="btn btn--base" type="submit">@lang('Change Password')</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@if ($general->secure_password)
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
