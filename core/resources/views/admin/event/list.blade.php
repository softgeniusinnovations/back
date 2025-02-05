@extends('admin.layouts.app')
@php
$types = ['super-admin', 'agent', 'cash-agent', 'mob-agent', 'affiliator', 'support', 'report'];
@endphp
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>@lang('#')</th>
                                <th>@lang('Title')</th>
                                <th>@lang('Bonus Percentage')</th>
                                <th>@lang('Start Date')</th>
                                <th>@lang('End Date')</th>
                                {{-- <th>@lang('Description')</th> --}}
                                <th>@lang('Status')</th>
                                <th>@lang('Created Time')</th>
                                <th>@lang('Update Time')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $i = 0;
                            @endphp
                            @forelse ($events as $item)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $item->title }}</td>
                                <td>{{ $item->bonus_percentage ?? 0}}%</td>
                                <td>{{ \Carbon\Carbon::parse($item->start_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->end_date)->format('d M Y') }}</td>
                                {{-- <td>{{ $item->description }}</td> --}}
                                <td>
                                    @if ($item->status == 1)
                                    <span class="badge badge--success">@lang('Active')</span>
                                    @elseif($item->status == 0)
                                    <span class="badge badge--warning">@lang('Inactive')</span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }} <br>
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('h:i:s A') }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->updated_at)->format('d M Y') }} <br>
                                    {{ \Carbon\Carbon::parse($item->updated_at)->format('h:i:s A') }}
                                </td>
                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.event.edit', $item->id) }}" class="btn btn-sm btn-outline-info mr-1 mb-1">
                                            <i class="fa fa-pencil"></i> @lang('Edit')
                                        </a>
                                        {{-- <a href="{{ route('admin.event.edit', $item->id) }}"
                                        class="btn btn-sm btn-outline-primary mr-1 mb-1">
                                        <i class="fa fa-eye"></i> @lang('Details')
                                        </a> --}}
                                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger deleteBtn" data-id="{{ $item->id }}">
                                            <i class="fa fa-trash"></i> @lang('Delete')
                                        </a>
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
                @if ($events->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($events) }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')
<script>
    $(document).ready(function() {
        $('.deleteBtn').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = "{{ route('admin.event.delete', ':id') }}";
            url = url.replace(':id', id);
            var csrf_token = $('meta[name="csrf-token"]').attr('content');

            Swal.fire({
                title: "@lang('Are you sure to delete this event?')"
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
