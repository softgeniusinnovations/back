@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">


        <div class="col-lg-4 col-md-4 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">@lang('Withdraw Via') {{ __(@$withdrawal->method->name) }}</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Date')
                            <span class="fw-bold">{{ showDateTime($withdrawal->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Trx Number')
                            <span class="fw-bold">{{ $withdrawal->trx }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span class="fw-bold">
                                <a
                                    href="{{ route('admin.users.detail', $withdrawal->user_id) }}">{{ @$withdrawal->user->username }}</a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Method')
                            <span
                                class="fw-bold">{{ __(@$withdrawal->method->name ? @$withdrawal->method->name : @$withdrawal->transectionProviders->name ?? 'Cash agent') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Provider Number')
                            <span
                                    class="fw-bold">{{ __(@$withdrawal->phone ? @$withdrawal->phone : 'N/a') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Amount')
                            <span class="fw-bold">{{ showAmount($withdrawal->amount) }}
                                {{ __($withdrawal->user->currency) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Payable')
                            <span class="fw-bold">{{ showAmount($withdrawal->final_amount) }}
                                {{ __($withdrawal->currency) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            @php echo $withdrawal->statusBadge @endphp
                        </li>

                        @if ($withdrawal->admin_feedback)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @lang('Admin Response')
                                <p>{{ $withdrawal->admin_feedback }}</p>
                            </li>
                        @endif
                        @can('super-admin')
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @lang('Assign agent')
                                <p>{{ $withdrawal->assign_agent == 0 ? 'Not Assigned' : 'Assigned' }}</p>
                            </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 mb-30">

            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('Bettor Withdraw Information')</h5>


                    @if ($details != null)
                        @foreach (json_decode($details) as $val)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h6>{{ __($val->name) }}</h6>
                                    @if ($val->type == 'checkbox')
                                        {{ implode(',', $val->value) }}
                                    @elseif($val->type == 'file')
                                        @if ($val->value)
                                            <a href="{{ route('admin.download.attachment', encrypt(getFilePath('verify') . '/' . $val->value)) }}"
                                                class="me-3"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                        @else
                                            @lang('No File')
                                        @endif
                                    @else
                                        <p>{{ __($val->value) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif


                    @if ($withdrawal->status == Status::PAYMENT_PENDING)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                @if (
                                    $withdrawal->assign_agent == 1 ||
                                        auth()->user()->can('super-admin'))
                                    <button class="btn btn-outline--success ms-1 approveBtn"
                                        data-id="{{ $withdrawal->id }}"
                                        data-amount="{{ showAmount($withdrawal->final_amount) }} {{ $withdrawal->currency }}">
                                        <i class="fas la-check"></i> @lang('Approve')
                                    </button>

                                    <button class="btn btn-outline--danger ms-1 rejectBtn" data-id="{{ $withdrawal->id }}">
                                        <i class="fas fa-ban"></i> @lang('Reject')
                                    </button>

                                    @if ($withdrawal->agent_id == 1 && $withdrawal->assign_agent == 0)
                                        <form action="{{ route('admin.withdraw.agent.assign', $withdrawal->id) }}"
                                            class="mt-2">
                                            <div class="form-group">
                                                <select name="agent_id" id="agent_id" class="form-control">
                                                    <option value="">---Select Agent---</option>
                                                    @foreach ($agents as $agent)
                                                        <option value="{{ $agent->id }}">
                                                            {{ $agent->username . '(' . $agent->phone . ')' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="local" value="local">
                                            </div>
                                            <button class="btn btn-sm btn-primary">Assign</button>
                                        </form>
                                    @else
                                        @can('super-admin')
                                            <a href="{{ route('admin.withdraw.agent.assign', $withdrawal->id) }}"
                                                class="btn btn-outline--primary ms-1">{{ $withdrawal->assign_agent == 0 ? 'Assign' : 'Revoked' }}</a>
                                        @endcan
                                    @endif

                                @endif
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>



    {{-- APPROVE MODAL --}}
    <div id="approveModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Approve Withdrawal Confirmation')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.withdraw.approve') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p>@lang('Have you sent') <span class="fw-bold withdraw-amount text--success"></span>?</p>
                        <p class="withdraw-detail"></p>
                        <textarea name="details" class="form-control pt-3" value="{{ old('details') }}" rows="3"
                            placeholder="@lang('Provide the details. eg: transaction number')" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Withdrawal Confirmation')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.withdraw.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Reason of Rejection')</label>
                            <textarea name="details" class="form-control pt-3" rows="3" value="{{ old('details') }}" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.approveBtn').on('click', function() {
                var modal = $('#approveModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('.withdraw-amount').text($(this).data('amount'));
                modal.modal('show');
            });

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
