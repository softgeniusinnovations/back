@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('role-management')
            <div class="col-lg-4 m-auto">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.admin.by.update', $admin->id) }}" method="post">
                            @method('PUT')
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Name')</label>
                                        <input class="form-control form--control mb-3" name="name" type="text"
                                            value="{{ $admin->name }}" required placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Username')</label>
                                        <input class="form-control form--control mb-3" name="username" type="text"
                                            value="{{ $admin->username }}" required placeholder="Username">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Change password (Optional)')</label>
                                        <input class="form-control form--control mb-3" name="password" type="password"
                                            value="{{ old('password') }}" placeholder="*******">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Role')</label>
                                        <select class="form-control form--control mb-3" name="role" required>
                                            <option value="">---Select role---</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected($admin->type == $role->id)>
                                                    {{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Status')</label>
                                        <select class="form-control form--control mb-3" name="status" required>
                                            <option value="">---Select stauts---</option>
                                            <option value="1" @selected($admin->status == 1)>
                                                Active</option>
                                            <option value="0" @selected($admin->status == 0)>
                                                Blocked</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

    </div>
@endsection
