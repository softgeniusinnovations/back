@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('role-create')
            <div class="col-lg-3">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.role.create') }}" method="post">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Role')</label>
                                        <input class="form-control form--control mb-3" name="name" type="text"
                                            value="{{ old('name') }}" required placeholder="Role name">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button class="btn btn-primary">Create role</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('role-view')
            <div class="col-lg-9">
                <div class="card b-radius--10">
                    <div class="card-body p-0">
                        <div class="table-responsive--md table-responsive">
                            <table class="table--light style--two table">
                                <thead>
                                    <tr>
                                        <th>@lang('Role Name')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($roles as $role)
                                        <tr>
                                            <td>{{ $role->name }}</td>
                                            <td>
                                                @if ($role->name != 'super-admin')
                                                    @can('role-edit')
                                                        <a href="{{ route('admin.role.has.permissions', $role->id) }}"
                                                            class="btn btn-sm btn-outline--primary bet-detail ">Permissions
                                                            ({{ $role->name == 'super-admin' ? 'All' : $role->permissions->count() }})
                                                        </a>
                                                    @endcan
                                                @endif
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
                    @if ($roles->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($roles) }}
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
