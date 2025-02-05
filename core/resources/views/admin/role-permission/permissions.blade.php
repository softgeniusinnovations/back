@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-3">
                    <form action="{{ route('admin.role.has.permission.update', $role->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-lg-10">
                                <div class="form-group">
                                    <input class="form-control form--control mb-3" name="name" type="text"
                                        value="{{ $role->name }}" required placeholder="Role name">
                                </div>

                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <button class="btn btn-primary btn-lg">Update</button>
                                </div>
                            </div>
                            <hr>

                            @forelse ($permissions as $key=>$permission)
                                @if ($key === 0)
                                    <div class="col-lg-3">
                                        <label for="checkAll"><input id="checkAll" type="checkbox"
                                                {{ $permissions->count() === $role->permissions->count() ? 'checked' : '' }}
                                                value="1">
                                            All</label>
                                    </div>
                                @endif
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label for="{{ $permission->id }}"><input type="checkbox" id={{ $permission->id }}
                                                name="permissions[]" value="{{ $permission->id }}"
                                                {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                            {{ $permission->name }}</label>

                                    </div>
                                </div>
                            @empty
                                No permission found right now
                            @endforelse
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        "use strict";
        (function($) {
            $("#checkAll").click(function() {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });
        })(jQuery);
    </script>
@endpush
