@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="card custom--card">
    <div class="card-header d-flex justify-content-between">
        <div>
            <h5>
                <i class="las la-ticket-alt"></i>
                @lang('Websites')
            </h5>
        </div>
        <div>
            @if($websiteList->count() == 1)
                <button type="button" class="btn btn--primary d-none" data-bs-toggle="modal" data-bs-target="#applicationModal">
                    <i class="las la-plus"></i>
                    @lang('Apply')
                </button>
            @else
            <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#websiteModal">
                <i class="las la-plus"></i>
                @lang('Add Website')
            </button>
            @endif
            
        </div>
    </div>
</div>


<div class="mt-5 table-responsive">
    <table class="table-responsive-sm table-sm custom--table table table-striped table-bordered">
        <thead>
            <tr>
                <th>@lang('Website Id')</th>
                <th>@lang('Website')</th>
                <th>@lang('Status')</th>
                <th>@lang('Created At')</th>
                <th>@lang('Action')</th>
            </tr>
        </thead>

        <tbody>
            @php
            $i=0;
            @endphp
            @forelse ($websiteList as $item)
            <tr>
                <td>{{ $item->websiteId }}</td>
                <td>{{ $item->website }}</td>

                <td>
                    @if ($item->status == 1)
                    <span class="badge badge--success">@lang('Active')</span>
                    @else
                    <span class="badge badge--warning">@lang('Inactive')</span>
                    @endif
                </td>
                <td>{{ $item->created_at->format('d M, Y') }}</td>
                <td>
                    <a href="javascript:void(0);" data-id="{{ $item->id }}" class="btn-sm btn-outline-primary edit-btn"><i class="las la-edit"></i></a>
                    <a href="javascript:void(0);" class="btn-sm btn-outline-danger delete_btn" data-id="{{ $item->id }}"><i class="las la-trash"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="websiteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Affiliate Applicaiton Form</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="websiteForm">
                    <div class="form-group">
                        <label for="landingpage">Website</label>
                        <input type="text" class="form-control" id="website" name="website" >
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput">Website type</label>
                        <select id="type" class="form-control" name="type">
                            <option value="website">Website</option>
                            <option value="youtube">Youtube</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="websiteEditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Affiliate Applicaiton Form</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="websiteEditForm">
                    <div class="form-group">
                        <input type="hidden" id="id" name="id">
                        <label for="landingpage">Website</label>
                        <input type="text" class="form-control" id="website_edit" name="website_edit" >
                    </div>
                    <div class="form-group">
                        <label for="formGroupExampleInput">Website type</label>
                        <select id="type_edit" class="form-control" name="type_edit">
                            <option value="website">Website</option>
                            <option value="youtube">Youtube</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection



@push('script')
<script>
    $('#websiteForm').on('submit', function(e) {
        e.preventDefault();
        $.ajaxSetup({
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                var formData = new FormData($('#websiteForm')[0]);
        $.ajax({
            url: "{{ route('affiliate.website.create') }}",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    notify('success', data.success);
                    $('#websiteModal').modal('hide');
                    $('#websiteForm').trigger('reset');
                    window.location.reload();
                } else {
                    notify('error', data.error);
                }
            }
        });
    });

    $(document).ready(function() {
        $('.delete_btn').on('click', function(e) {
            e.preventDefault();

            var id = $(this).data('id');
            var url = "{{ route('affiliate.website.delete', ':id') }}";
            url = url.replace(':id', id);
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            Swal.fire({
                title: "@lang('Are you sure to delete this promotions?')"
                , icon: "warning"
                , showCancelButton: true
                , confirmButtonColor: "#276678"
                , confirmButtonText: "@lang('Yes')"
                , cancelButtonText: "@lang('No')"
                , reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url
                        , type: "DELETE"
                        , data: {
                            "id": id
                            , "_token": csrf_token
                        }
                        , success: function(response) {
                            Swal.fire({
                                icon: 'success'
                                , title: 'Promotion Successfully Deleted!'
                                , showConfirmButton: false
                                , timer: 1500
                            });
                            window.location.reload();
                        }
                    });
                }
            });
        });
    });

    $('.edit-btn').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{ route('affiliate.website.edit', ':id') }}";
        url = url.replace(':id', id);
        $.ajax({
            url: url
            , success: function(data) {
                $('#websiteEditModal').modal('show');
                $('#id').val(data.id);
                $('#website_edit').val(data.website);
                $('#type_edit').val(data.webtype);
            }
        });
    });

    $('#websiteEditForm').on('submit', function(e) {
        e.preventDefault();
        $.ajaxSetup({
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                var formData = new FormData($('#websiteEditForm')[0]);
        $.ajax({
            url: "{{ route('affiliate.website.update') }}",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
                if (data.success) {
                    notify('success', data.success);
                    $('#websiteEditModal').modal('hide');
                    $('#websiteEditForm').trigger('reset');
                    window.location.reload();
                } else {
                    notify('error', data.error);
                }
            }
        });
    });
</script>
@endpush
