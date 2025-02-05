@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="card custom--card">
    <div class="card-header d-flex justify-content-between">
        <div>
            <h5>
                <i class="las la-ticket-alt"></i>
                @lang('Applications list for affiliate')
            </h5>
        </div>
        <div>
            @if(auth()->user()->is_affiliate == 1)
                <button type="button" class="btn btn--primary d-none" data-bs-toggle="modal" data-bs-target="#applicationModal">
                    <i class="las la-plus"></i>
                    @lang('Apply')
                </button>
            @else
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applicationModal">
                @lang('Apply')
            </button>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table-responsive--md custom--table custom--table-separate table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>@lang('Applied Date')</th>
                    <th>@lang('Description')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Action')</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $i=0;
                @endphp
                @forelse ($applicationForm as $item)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td style="width: 50%">{{ $item->description }}</td>
                        <td>
                            @if ($item->is_approved == 0)
                                <span class="badge badge--info">@lang('Panding')</span>
                            @elseif($item->is_approved == 1)
                                <span class="badge badge--success">@lang('Accept')</span>
                            @elseif($item->is_approved == 2)
                                <span class="badge badge--danger">@lang('Reject')</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('user.news.edit', $item->id) }}" class="btn-sm btn-outline-primary edit-btn"><i class="las la-edit"></i></a>
                            <a href="javascript:void(0);" class="btn-sm btn-outline-danger delete_btn" data-id="{{ $item->id }}"><i class="las la-trash"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                    </tr>
                @endforelse
        </table>
    </div>
</div>

<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Affiliate Applicaiton Form</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Applicaiton Form  --}}
                <form action="" id="applicaitonForm">
                    <div class="form-group">
                        <label class="form-label" for="application">@lang('Application')</label>
                        <textarea class="form-control" name="description" id="application" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="application">@lang('Website')</label>
                        <input class="form-control" name="website" id="website" required />
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    //Add data using ajax and jquery
    // user.affiliate.application.submit
    $('#applicaitonForm').on('submit', function(e) {
        e.preventDefault();
        $.ajaxSetup({
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                var formData = new FormData($('#applicaitonForm')[0]);
        $.ajax({
            url: "{{ route('user.affiliate.application.submit') }}",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(data) {
                // console.log(data);
                if (data.success) {
                    notify('success', data.success);
                    $('#applicationModal').modal('hide');
                    $('#applicaitonForm').trigger('reset');
                    // location.reload();
                } else {
                    notify('error', data.error);
                }
            }
        });
    });
</script>
@endpush
