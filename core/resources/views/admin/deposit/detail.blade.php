@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30 justify-content-center">
        <div class="col-xl-4 col-md-6 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">@lang('Deposit Via') {{ __(@$deposit->gateway->name) }}</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Date')
                            <span class="fw-bold">{{ showDateTime($deposit->created_at) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Transaction Number')
                            <span class="fw-bold">{{ $deposit->trx }}</span>
                        </li>
                        @if ($deposit->method_trx_number)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @lang('Method Trx')
                                <span class="fw-bold">{{ $deposit->method_trx_number }}</span>
                            </li>
                        @endif
                        @if ($deposit->depositor_name)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @lang('Depositor Name')
                                <span class="fw-bold">{{ $deposit->depositor_name }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span class="fw-bold">
                                <a
                                    href="{{ route('admin.users.detail', $deposit->user_id) }}">{{ @$deposit->user->username }}</a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Client Phone')
                            <span
                                    class="fw-bold">{{ __(@$deposit->payment_number ? @$deposit->payment_number : '') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Agent Phone')
                            <span
                                    class="fw-bold">{{ __(@$deposit->agent->phone ? @$deposit->agent->phone : '') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Method')
                            <span
                                class="fw-bold">{{ __(@$deposit->transectionProviders->name ? @$deposit->transectionProviders->name : 'Cash Agent') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Amount')
                            <span class="fw-bold">{{ showAmount($deposit->amount) }} {{ __(userCurrency()) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Payable')
                            <span class="fw-bold">{{ showAmount($deposit->final_amo) }}
                                {{ __($deposit->method_currency) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            @php echo $deposit->statusBadge @endphp
                        </li>
                        @if ($deposit->admin_feedback)
                            <li class="list-group-item">
                                <strong>@lang('Admin Response')</strong>
                                <br>
                                <p>{{ __($deposit->admin_feedback) }}</p>
                            </li>
                        @endif
                        
                        @if($deposit->status == 1)
                            <li class="list-group-item">
                                <form action={{route('admin.deposit.refund', $deposit->id)}} method="POST">
                                    @csrf
                                    <textarea class="form-control" name="message" placeholder="Refund reason"></textarea>
                                    <button class="btn btn-sm btn-danger mt-2">Deposit Refund/ Reject</button>
                                </form>
                            </li>
                        @endif
                         @if($deposit->status == 3)
                            <li class="list-group-item">
                                <form action={{route('admin.deposit.approve', $deposit->id)}} method="POST">
                                @csrf
                                    <button class="btn btn-sm btn-success mt-2">Deposit Approved</button>
                                </form>
                            </li>
                        @endif
                        
                        @can('super-admin')
                        <li class="list-group-item">
                            <form action="{{route('admin.deposit.agent.change.for.deposit')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <input type="hidden" name="deposit_id" value="{{$deposit->id}}" />
                                    <label>Change agent</label>
                                    <select class="form-control  select2" name="agent_id">
                                        <option>---select the agent---</option>
                                        @foreach($agents as $da)
                                        <option value="{{@$da->id}}" @selected(@$deposit->agent->id == $da->id)>{{@$da->username . ' - '. $da->identity}}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary mt-1">Change agent</button>
                                </div>
                            </form>
                        </li>
                        @endcan
                        
                         @if($deposit->status == 3)
                            <li class="list-group-item">
                                <form action={{route('admin.deposit.adjustment', $deposit->id)}} method="POST">
                                @csrf
                                    <input class="form-control mt-2 mb-1" name="adjustment_amount" value={{showAmount($deposit->final_amo)}} />
                                    <button class="btn btn-sm btn-success mt-2">Balance Adjustment</button>
                                </form>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        @if ($details || $deposit->status == Status::PAYMENT_PENDING)
            <div class="col-xl-8 col-md-6 mb-30">
                <div class="card b-radius--10 overflow-hidden box--shadow1">
                    <div class="card-body">
                        <h5 class="card-title mb-50 border-bottom pb-2">@lang('User Deposit Information')</h5>
                        @if ($details != null)
                            @foreach (json_decode($details) as $val)
                                @if ($deposit->method_code >= 1000)
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
                                @endif
                            @endforeach
                            @if ($deposit->method_code < 1000)
                                @include('admin.deposit.gateway_data', [
                                    'details' => json_decode($details),
                                ])
                            @endif
                        @endif
                        @if ($deposit->status == Status::PAYMENT_PENDING)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    @if ($deposit->amount > 0)
                                        <button class="btn btn-outline--success btn-sm ms-1 confirmationBtn"
                                            data-action="{{ route('admin.deposit.approve', $deposit->id) }}"
                                            data-question="@lang('Are you sure to approve this transaction?')" data-amount={{$deposit->amount}}><i class="las la-check-double"></i>
                                            @if($deposit->admin_feedback == 'Waiting for reapprove') @lang('Reapprove') @else @lang('Approve') @endif
                                        </button>
                                    @endif

                                        <button class="btn btn-outline--primary btn-sm ms-1 requestDepositBtn"
                                            data-id="{{ $deposit->id }}"
                                            data-amount="{{ $deposit->amount }} {{ __($deposit->deposit_currency) }}"><i
                                                class="las la-ban"></i>
                                            @lang('Change/Request Deposit amount')
                                        </button>

                                    <button class="btn btn-outline--danger btn-sm ms-1 rejectBtn"
                                        data-id="{{ $deposit->id }}" data-info="{{ $details }}"
                                        data-amount="{{ showAmount($deposit->amount) }} {{ __($deposit->user->currency) }}"
                                        data-username="{{ @$deposit->user->username }}"><i class="las la-ban"></i>
                                        @lang('Reject')
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>


    {{-- REQUEST  MODAL --}}
    <div id="requestDepositModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Chnage/ Request Deposit Amount')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.deposit.request') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">

                        <div class="form-group">
                            <label class="mt-2">@lang('Amount')</label>
                            <input type="text" name="amount" min="{{ $deposit->amount }}" max="20000"
                                class="form-control" required placeholder="300{{ $deposit->method_currency }}"
                                value="{{ $deposit->amount }}" />
                        </div>
    
                        <div class="form-group">
                            <label class="mt-2">@lang('Remark')</label>
                            <textarea placeholder="remark" name="remark"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />


    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Deposit Confirmation')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.deposit.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p>@lang('Are you sure to') <span class="fw-bold">@lang('reject')</span> <span
                                class="fw-bold withdraw-amount text--success"></span> @lang('deposit of') <span
                                class="fw-bold withdraw-user"></span>?</p>

                        <div class="form-group">
                            <label class="mt-2">@lang('Reason for Rejection')</label>
                            <textarea name="message" maxlength="255" class="form-control" rows="5" required>{{ old('message') }}</textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('.withdraw-amount').text($(this).data('amount'));
                modal.find('.withdraw-user').text($(this).data('username'));
                modal.modal('show');
            });
            $('.requestDepositBtn').on('click', function() {
                var modal = $('#requestDepositModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.modal('show');
            });
            
            $('.select2').select2();
        })(jQuery);
    </script>
@endpush
