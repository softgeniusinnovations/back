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
                                <th>@lang('Promo Code')</th>
                                <th>@lang('Percentage')</th>
                                <th>@lang('Company Expenses')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Create At')</th>
                                <th>@lang('Is Approved')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($promocode as $item)
                            <tr>
                                <td>
                                    <span>{{ optional($item->user)->firstname }} {{ optional($item->user)->lastname }}</span>
                                    <br>
                                    {{-- <a href="{{ route('admin.agent.details', $agent->id) }}"> --}}
                                    <a href="#">
                                        <span>@</span>{{ optional($item->user)->username }}
                                    </a>
                                </td>
                                <td>{{ $item->promo_code }}</td>
                                <td>{{ $item->promo_percentage ? $item->promo_percentage.'%' : '0%' }}</td>
                                <td>{{ $item->company_expenses ? $item->company_expenses.'%' : '0%' }}</td>
                                <td>
                                    @if ($item->status == 1)
                                    <span class="text--small badge font-weight-normal badge--success">@lang('Active')</span>
                                    @else
                                    <span class="text--small badge font-weight-normal badge--warning">@lang('Inactive')</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                                <td>
                                    @if ($item->is_admin_approved == 1)
                                    <span class="text--small badge font-weight-normal badge--success">@lang('Approved')</span>
                                    @elseif ($item->is_admin_approved == 0)
                                    <span class="text--small badge font-weight-normal badge--info">@lang('Pending')</span>
                                    @elseif ($item->is_admin_approved == 2)
                                    <span class="text--small badge font-weight-normal badge--danger">@lang('Rejected')</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="javascript:void(0);" data-id="{{ $item->id }}" class="btn btn-sm btn-outline--success approvedBtn">
                                        <i class="la la-check-circle-o"></i>@lang('Approved')
                                    </a>
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

            @if ($promocode->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($promocode) }}
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
                        <label for="percentage">Set Percentage</label>
                        <input type="text" class="form-control" id="percentage" name="percentage" placeholder="Percentage" value="20" />
                        <span class="invalid feedback text-danger list-percentage" role="alert" style="display:none"></span>
                    </div>

                    <div class="form-group">
                        <label for="percentage">Company Expenses</label>
                        <input type="number" class="form-control" id="company_expenses" name="company_expenses" placeholder="Percentage" value="10" />
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

<!--Reject Modal -->
<div class="modal fade" id="RejectModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <form class="forms-sample" id="RejectForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- <ul class="alert alert-warning d-none" id="save_errorList"></ul> --}}
                    <div style="display: none">
                        <input type="text" class="form-control" id="edit_id" name="edit_id">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control is_admin_approved" id="is_admin_approved" name="is_admin_approved">
                            <option value="0">Pending</option>
                            <option value="2">Rejected</option>
                        </select>
                        <span class="invalid feedback text-danger list-is_admin_approved" role="alert" style="display:none"></span>
                    </div>
                    <div class="form-group">
                        <label for="reject_reason">Reject Reason</label>
                        <textarea class="form-control reject_reason" id="reject_reason" name="reject_reason" placeholder="Reject Reason" rows="4"></textarea>
                    </div>
                    <div class="text-center pb-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-success" type="submit">@lang('Reject')</button>
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
        var url = `{{ URL::route('admin.affiliate.promoCodeEdit', ':id') }}`;
        url = url.replace(':id', id);
        $.ajax({
            type: "GET"
            , url: url
            , success: function(data) {
                $('#AcceptModal').modal('show');
                $('#edit_id').val(data.id);
                $('#percentage').val(data.promo_percentage);
                $('#is_admin_approved').val(data.is_admin_approved);
            }
        , });
    });

    $(document).on('click', '.rejectBtn', function() {
        var id = $(this).data('id');
        var url = `{{ URL::route('admin.affiliate.promoCodeEdit', ':id') }}`;
        url = url.replace(':id', id);
        $.ajax({
            type: "GET"
            , url: url
            , success: function(data) {
                $('#RejectModal').modal('show');
                $('#edit_id').val(data.id);
                $('#reject_reason').val(data.admin_comment);
                $('#is_admin_approved').val(data.is_admin_approved);
            }
        , });
    });

    $('#AcceptForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();
        let company_expenses = $('#company_expenses').val();
        let percentage = $('#percentage').val();
        let is_admin_approved = $('#is_admin_approved').val();
        let _token = $("input[name=_token]").val();
        let url = "{{ URL::route('admin.affiliate.promoCodeUpdate') }}";
        $.ajax({
            type: "POST"
            , url: url
            , data: {
                id: id
                , company_expenses: company_expenses
                , percentage: percentage
                , is_admin_approved: is_admin_approved
                , _token: _token
            }
            , success: function(response) {
                console.log(response.success);
                notify('success', response.success);
                $("#AcceptForm").trigger("reset");
                $('#AcceptModal').modal('hide');
                window.location.reload();
            }
            , error: function(error) {
                console.log(error);
            }
        });
    })

    $('#RejectForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();
        let reject_reason = $('.reject_reason').val();
        let is_admin_approved = $('.is_admin_approved').val();
        let _token = $("input[name=_token]").val();
        let url = "{{ URL::route('admin.affiliate.promoCodeReject') }}";
        $.ajax({
            type: "POST"
            , url: url
            , data: {
                id: id
                , reject_reason: reject_reason
                , is_admin_approved: is_admin_approved
                , _token: _token
            }
            , success: function(response) {
                console.log(response.success);
                notify('success', response.success);
                $("#RejectForm").trigger("reset");
                $('#RejectModal').modal('hide');
                window.location.reload();
            }
            , error: function(error) {
                console.log(error);
            }
        });
    })

</script>
@endpush
