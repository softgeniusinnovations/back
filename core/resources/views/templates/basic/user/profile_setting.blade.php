@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="card custom--card">
        <h5 class="card-header">
            <i class="las la-user-circle"></i>
            {{ __($pageTitle) }}
        </h5>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('First Name')</label>
                            <input class="form-control form--control" name="firstname" type="text" value="{{ $user->firstname }}" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Last Name')</label>
                            <input class="form-control form--control" name="lastname" type="text" value="{{ $user->lastname }}" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Email')</label>
                            @if($user->email != null)
                                <input class="form-control form--control" type="text" value="{{ $user->email }}" disabled>
                            @elseif($user->email == null)
                                <input class="form-control form--control" type="text" name="email" value="{{ $user->email }}">
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Mobile')</label>
                            @if($user->mobile != null)
                                <input class="form-control form--control" type="text" value="{{ $user->mobile }}" disabled>
                            @elseif($user->mobile == null)
                                <input class="form-control form--control" type="text" name="mobile" value="{{ $user->mobile }}">
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Date of Birth')</label>
                            <input class="form-control form--control" type="date" value="{{ $user->dob }}" name="dob" {{ $user->dob ? 'disabled' : '' }}>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Profile Photo')</label>
                            <input class="form-control form--control" type="file" accept="image/*" name="image">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Country')</label>
                            <input class="form-control form--control" type="text" value="{{ @$user->address->country }}" disabled>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('State')</label>
                            <input class="form-control form--control" name="state" type="text" value="{{ $user->address->state }}" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('City')</label>
                            <input class="form-control form--control" name="city" type="text" value="{{ $user->address->city }}" required>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Zip')</label>
                            <input class="form-control form--control" name="zip" type="text" value="{{ $user->address->zip }}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-label">@lang('Address')</label>
                            <input class="form-control form--control" name="address" type="text" value="{{ $user->address->address }}" required>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button class="btn btn--base" type="submit">@lang('Update Profile')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
