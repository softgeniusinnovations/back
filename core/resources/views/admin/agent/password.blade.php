@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card b-radius--10">
                <div class="card-body p-4">
                    <form action="{{route('admin.agent.password.change', $agent->id)}}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <p class="alert alert-danger p-2">
                                   Be careful! Now you will change the password for {{ $agent->username }} ({{ $agent->identity }})
                                </p>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Password')</label>
                                    <div class="input-group input--group">
                                        <input class="form-control form--control" name="password" type="password" required>
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
                            <button class="btn btn--primary" type="submit">@lang('Change Password')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

