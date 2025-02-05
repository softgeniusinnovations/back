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
                                <th>@lang('UserId')</th>
                                <th>@lang('Description')</th>
                                <th>@lang('Applied At')</th>
                                <th>@lang('Is Approved')</th>
                                <th>@lang('Kyc Verified')</th>
                                <th>@lang('Last Update')</th>
                                <th>@lang('Company Expenses')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($application as $item)
                            <tr>
                                <td>
                                    <span>{{ optional($item->user)->firstname }} {{ optional($item->user)->lastname }}</span>
                                    <br>
                                    {{-- <a href="{{ route('admin.agent.details', $agent->id) }}"> --}}
                                    <a href="{{ route('admin.users.detail', $item->id) }}">
                                        <span>@</span>{{ optional($item->user)->username }}
                                    </a>
                                </td>
                                <td>{{ $item->description }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                                <td>
                                    @if ($item->is_approved == 1)
                                    <span class="text--small badge font-weight-normal badge--success">@lang('Approved')</span>
                                    @elseif ($item->is_approved == 0)
                                    <span class="text--small badge font-weight-normal badge--primary">@lang('Pending')</span>
                                    @elseif ($item->is_approved == 3)
                                    <span class="text--small badge font-weight-normal badge--danger">@lang('Rejected')</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $item->user->kv == 1 ? 'Yes' : 'No' }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}
                                </td>
                                <td>
                                    {{$item->company_expenses?? 'N/A'}}
                                </td>
                                <td>
                                @if ($item->is_approved != 1)
                                    <a href="javascript:void(0);" data-id="{{ $item->id }}" class="btn btn-sm btn-outline--success approvedBtn">
                                        <i class="la la-check-circle-o"></i>@lang('Approved')
                                    </a>
                                    @endif
                                    <a href="javascript:void(0);" data-id="{{ $item->id }}" class="btn btn-sm btn-outline--danger rejectBtn">
                                        <i class="la la-times-circle-o"></i>@lang('Rejected')
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($application->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($application) }}
            </div>
            @endif
        </div>
    </div>
</div>

<!--Accept Modal -->
<div class="modal fade" id="AcceptModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <form class="forms-sample" id="AcceptForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="display: none">
                        <input type="text" class="form-control" id="edit_id" name="edit_id">
                    </div>

                    <div class="form-group">
                        <label for="percentage">Promo Code</label>
                        <input type="text" class="form-control" id="promo_code" name="promo_code" readonly/>
                    </div>
                    <div class="form-group">
                        <label for="percentage">Set Percentage</label>
                        <input type="text" class="form-control" id="percentage" name="percentage" placeholder="Percentage" value="20" />
                        <span class="invalid feedback text-danger list-percentage" role="alert" style="display:none"></span>
                    </div>
                    <div class="form-group">
                        <label for="company_expenses">Set Company Expenses</label>
                        <input type="text" class="form-control" id="company_expenses" name="company_expenses" placeholder="Company expenses" value="20" />
                        <span class="invalid feedback text-danger list-percentage" role="alert" style="display:none"></span>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="is_admin_approved" name="is_admin_approved">
                            <option value="0">Pending</option>
                            <option value="1">Approved</option>
                        </select>
                        <span class="invalid feedback text-danger list-is_admin_approved" role="alert" style="display:none"></span>
                    </div>
                    <div class="text-center pb-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-success" type="submit">@lang('Approve')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')
<script>
    $(document).on('click', '.approvedBtn', function() {
        var id = $(this).data('id');
        var url = `{{ URL::route('admin.affiliate.applicationForm', ':id') }}`;
        url = url.replace(':id', id);
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for (var i = 0; i < 6; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        $.ajax({
            type: "GET"
            , url: url
            , success: function(data) {
                $('#AcceptModal').modal('show');
                if (data.is_approved == 1) {
                    $('#promo_code').val(data.promo_code);
                } else {
                        $('#promo_code').val(text);
                    }
                $('#edit_id').val(data.id);
                $('#percentage').val(data.promo_percentage);
                $('#is_admin_approved').val(data.is_approved);
            }
        , });
    });

    $('#AcceptForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();
        var promo_code = $('#promo_code').val();
        var percentage = $('#percentage').val();
        var company_expenses = $('#company_expenses').val();
        console.log("company_expenses",company_expenses)
        var is_admin_approved = $('#is_admin_approved').val();
        var token = $("meta[name='csrf-token']").attr("content");
        $.ajax({
            type: "Post"
            , url: "{{ URL::route('admin.affiliate.applicationapprove') }}"
            , data: {
                id: id
                , promo_code: promo_code
                , percentage: percentage
                , company_expenses: company_expenses
                , is_approved: is_admin_approved
                , _token: token
            , }
            , success: function(data) {
                Swal.fire({
                    title: 'Approved!'
                    , text: 'Application has been approved.'
                    , icon: 'success'
                    , showConfirmButton: false
                , });
                location.reload();
            }
            , error: function(data) {
                Swal.fire({
                    icon: 'error'
                    , title: 'Oops...'
                    , text: 'Something went wrong!'
                    , showConfirmButton: false
                , });
            }
        });
    });


    $('body').on('click', '.rejectBtn', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?'
            , text: "Do you want to reject this application?"
            , showCancelButton: true
            , confirmButtonColor: '#3085d6'
            , cancelButtonColor: '#d33'
            , confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.value === true) {
                var token = $("meta[name='csrf-token']").attr("content");
                $.ajax({
                    type: "Post"
                    , url: "{{ URL::route('admin.affiliate.applicationreject') }}"
                    , data: {
                        id: id
                        , is_approved: 3
                        , _token: token
                    , }
                    , success: function(data) {
                        Swal.fire({
                            title: 'Rejected!'
                            , text: 'Application has been rejected.'
                            , icon: 'success'
                            , showConfirmButton: false
                        , });
                        location.reload();
                    }
                    , error: function(data) {
                        Swal.fire({
                            icon: 'error'
                            , title: 'Oops...'
                            , text: 'Something went wrong!'
                            , showConfirmButton: false
                        , });
                    }
                });
            }
        })
    })

</script>
@endpush
