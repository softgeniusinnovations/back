@extends('admin.layouts.master')
@section('content')
    <div class="login-main" style="background-image: url('{{ asset('assets/admin/images/login.jpg') }}')">
        <div class="custom-container container">
            <div class="row justify-content-center">
                <div class="col-xxl-5 col-xl-5 col-lg-6 col-md-8 col-sm-11">
                    <div class="login-area">
                        <div class="login-wrapper">
                            <div class="login-wrapper__top">
                                <h3 class="title text-white">@lang('Recover Account')</h3>
                            </div>
                            <div class="login-wrapper__body">
                                <form class="login-form" action="{{ route('admin.password.change') }}" method="POST">
                                    @csrf
                                    <input name="email" type="hidden" value="{{ $email }}">
                                    <input name="token" type="hidden" value="{{ $token }}">
                                    <div class="form-group">
                                        <label>@lang('New Password')</label>
                                        <input class="form-control" name="password" type="password" required>
                                    </div>
                                    <div class="form-group">
                                        <label>@lang('Re-type New Password')</label>
                                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                                    </div>
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <a class="forget-text" href="{{ route('admin.login') }}">@lang('Login Here')</a>
                                    </div>
                                    <button class="btn cmn-btn w-100" type="submit">@lang('Submit')</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
