@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="d-flex justify-content-between align-items-center mt-0 flex-wrap gap-3 pb-3">
    <div class="action-area d-flex flex-wrap gap-2">
        {{-- <a class="btn btn-outline--base btn-sm @if (!request()->type) active @endif" href="{{ route('user.bets') }}">@lang('All')</a>
        <a class="btn btn-outline--base btn-sm @if (request()->type == 'pending') active @endif" href="{{ route('user.bets', 'pending') }}">@lang("Yesterday's Earning")</a>
        <a class="btn btn-outline--base btn-sm @if (request()->type == 'won') active @endif" href="{{ route('user.bets', 'won') }}">@lang("Today Earning")</a> --}}
    </div>
</div>


<div class="table-responsive">
    <table class="table-responsive table-sm custom--table table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('Title')</th>
                <th>@lang('Promo Code')</th>
                <th>@lang('Percentage')</th>
                <th>@lang('Create Date')</th>
                <th>@lang('Approval')</th>
                <th>@lang('Admin Comment')</th>
                <th>@lang('Status')</th>
                <th>@lang('Action')</th>
            </tr>
        </thead>

        <tbody>
            @php
            $i=0;
            @endphp
            @forelse ($promotions as $item)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ $item->title }}</td>
                <td id="promo{{ $item->id }}">{{ $item->promo_code }}</td>
                <td>{{ $item->promo_percentage }}%</td>
                <td>{{ $item->created_at->format('d M, Y') }}</td>
                <td>
                    @if ($item->is_admin_approved == 0)
                        <span class="badge badge--info">@lang('Pending')</span>
                    @elseif ($item->is_admin_approved == 1)
                        <span class="badge badge--success">@lang('Approved')</span>
                    @elseif($item->is_admin_approved == 2)
                        <span class="badge badge--danger">@lang('Rejected')</span>
                    @endif
                </td>
                <td>
                    @if ($item->admin_comment)
                        <small>{{ $item->admin_comment }}</small>
                    @else
                        <span class="badge badge--warning">@lang('No Comment')</span>
                    @endif
                </td>
                <td>
                    @if ($item->status == 1)
                        <span class="badge badge--success">@lang('Active')</span>
                    @else
                        <span class="badge badge--warning">@lang('Inactive')</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex">
                        @if($item->id !== $promo->id)
                            @if($item->is_admin_approved != 1)
                                <a href="{{ route('user.promotions.edit', $item->id) }}" class="btn-sm btn btn-outline-primary edit-btn pr-1"><i class="las la-edit"></i></a>
                            @else
                                <button onclick="copyLink({{ $item->id }})" class="btn btn-sm btn-outline-info"><i class="fas fa-copy"></i></button>
                                @endif
                                <a href="javascript:void(0);" class="btn-sm btn-outline-danger btn delete_btn" data-id="{{ $item->id }}"><i class="las la-trash"></i></a>
                        @else
                            <button onclick="copyLink({{ $item->id }})" class="btn btn-sm btn-outline-info"><i class="fas fa-copy"></i></button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="float-end mt-2">
        {{ $promotions->links() }}
    </div>
</div>
@endsection
@push('script')
<script>
    $(document).ready(function() {
        $('.delete_btn').on('click', function(e) {
            e.preventDefault();

            var id = $(this).data('id');
            var url = "{{ route('user.promotions.destroy', ':id') }}";
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

    $(document).ready(function() {
        window.copyLink = function(id) {
            var copyText = $("#promo" + id).text();
            var tempElement = $("<textarea></textarea>");
            tempElement.val(copyText);
            $("body").append(tempElement);
            tempElement.select();
            document.execCommand("copy");
            tempElement.remove();
            Swal.fire({
                icon: "success",
                title: "Promo Code Copied",
                text: "Promo Code copied to clipboard",
                timer: 1500,
                showConfirmButton: false,
            });
        }
    });
</script>
@endpush
