@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('role-management')
            <div class="col-lg-3">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.admin.create') }}" method="post">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Name')</label>
                                        <input class="form-control form--control mb-3" name="name" type="text"
                                            value="{{ old('name') }}" required placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Username')</label>
                                        <input class="form-control form--control mb-3" name="username" type="text"
                                            value="{{ old('username') }}" required placeholder="Username">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Password')</label>
                                        <input class="form-control form--control mb-3" name="password" type="password"
                                            value="{{ old('password') }}" required placeholder="*******">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Role')</label>
                                        <select class="form-control form--control mb-3" name="role" required>
                                            <option value="">---Select role---</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected(old('role') == $role->id)>
                                                    {{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button class="btn btn-primary">Create admin</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('role-management')
            <div class="col-lg-9">
                <div class="card b-radius--10">
                    <div class="card-body p-0">
                        <div class="table-responsive--md table-responsive">
                            <table class="table--light style--two table">
                                <thead>
                                    <tr>
                                        <th>@lang('Name')</th>
                                        <th>@lang('Username')</th>
                                        <th>@lang('Role')</th>
                                        <th>@lang('Created at')</th>
                                        <th>@lang('Status')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($admins as $admin)
                                        <tr style="background: {{ $admin->status == 0 ? '#ffecec' : '' }}">
                                            <td>{{ $admin->name }}</td>
                                            <td>{{ $admin->username }}</td>
                                            <td>
                                                {{ @$admin->roles[0]->name }}
                                            </td>
                                            <td>{{ $admin->created_at->diffForHumans() }}</td>
                                            <td>{{ $admin->status == 1 ? 'Active' : 'Blocked' }}</td>
                                            <td>
                                                <a href="{{ route('admin.admin.edit', $admin->id) }}"
                                                    class="btn btn-sm btn-outline--primary">Edit</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center" colspan="100%">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($admins->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($admins) }}
                        </div>
                    @endif
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {

        })(jQuery);
    </script>
@endpush
