@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <a href="{{route('admin.event.tramcard.create')}}" class="btn btn-sm btn-primary my-2">Create tramcard</a>
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>@lang('#')</th>
                                <th>@lang('Title')</th>
                                <th>@lang('Image')</th>
                                <th>@lang('Tracking Number')</th>
                                <th>@lang('Currency')</th>
                                <th>@lang('Value')</th>
                                <th>@lang('Created Time')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            
                            @forelse ($tramcards as $key=>$item)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $item->title }}</td>
                                <td><img src="{{ asset('/core/public/storage/event/tramcard/' . $item->image) }}" alt="Photo" style="width: 60px;
                                    height: 60px;
                                    padding: 5px;
                                    border: 1px solid #ddd;
                                    margin-bottom: 5px;
                                    object-fit: cover;" /></td>
                                <td>{{ $item->trx}}</td>
                                <td>{{ $item->currency }}</td>
                                <td>{{ $item->value }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }} <br>
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('h:i:s A') }}
                                </td>
                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.event.tramcard.show', $item->id) }}" class="btn btn-sm btn-outline-primary mr-1 mb-1">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.event.tramcard.edit', $item->id) }}" class="btn btn-sm btn-outline-info mr-1 mb-1">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <a href="{{ route('admin.event.tramcard.send', $item->id) }}" class="btn btn-sm btn-outline-primary mr-1 mb-1">
                                            <i class="fa fa-rocket"></i>
                                        </a>
                                        <!--<a href="javascript:void(0)" class="btn btn-sm btn-outline-danger deleteBtn" data-id="{{ $item->id }}">-->
                                        <!--    <i class="fa fa-trash"></i> @lang('Delete')-->
                                        <!--</a>-->
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="100%" class="text-center">@lang('No Data Available')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($tramcards->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($tramcards) }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@push('breadcrumb-plugins')
    <x-search-form placeholder="Title/ Trx" />
@endpush
@push('script')
<script>
    $(document).ready(function() {
        $('.deleteBtn').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = "{{ route('admin.event.tramcard.delete', ':id') }}";
            url = url.replace(':id', id);
            var csrf_token = $('meta[name="csrf-token"]').attr('content');

            Swal.fire({
                title: "@lang('Are you sure to delete this tramcard?')"
                , text: "@lang('You won\'t be able to revert this!')"
                , icon: "warning"
                , showCancelButton: true
                , confirmButtonColor: "#335eea"
                , cancelButtonColor: "#d33"
                , confirmButtonText: "@lang('Yes, delete it!')"
                , cancelButtonText: "@lang('Cancel')"
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: "DELETE",
                        data: {
                            "_token": csrf_token
                            , "id": id
                        }
                        , success: function(response) {
                            Swal.fire({
                                icon: "success"
                                , title: "@lang('Deleted')!"
                                , text: 'News Deleted Successfully!'
                                , showConfirmButton: false
                                , timer: 1500
                            });
                            window.location.reload();
                        }
                    });
                }
            });
        });
    })

</script>
@endpush
