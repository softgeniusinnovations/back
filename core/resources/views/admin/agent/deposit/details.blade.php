@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30 justify-content-center">
        <div class="col-xl-4 col-md-6 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">@lang('Deposit') {{ __(@$deposit->trx) }}</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Date')
                            <span class="fw-bold">{{ showDateTime($deposit->created_at) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Transaction Number')
                            <span class="fw-bold">{{ $deposit->trx }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Wallet Number')
                            <span class="fw-bold">{{ $deposit->wallet->wallet_number }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Amount')
                            <span class="fw-bold">{{ $deposit->amount . $deposit->deposit_currency }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Rate')
                            <span class="fw-bold">{{ $deposit->rate }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Final Amount')
                            <span class="fw-bold">
                                @if ($deposit->status == 2)
                                    {{ @$deposit->rate * $deposit->amount }} {{ @$deposit->agent->currency }}
                                @else
                                    ---
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span class="fw-bold">
                                <a
                                    href="{{ route('admin.agent.details', $deposit->agent_id) }}">{{ @$deposit->agent->username }}</a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Method')
                            <span
                                class="fw-bold">{{ __(@$deposit->transectionProviders->name ? @$deposit->transectionProviders->name : 'Cash Agent') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Depositor Wallet')
                            <span class="fw-bold">{{ $deposit->depositor_account }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Depositor Trx')
                            <span class="fw-bold">{{ $deposit->deposit_trx }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            @php
                                $status = ['Rejected', 'Pending', 'Approved', 'Back'];
                            @endphp
                            <span>{{ $status[$deposit->status] }}</span>
                        </li>
                        @if ($deposit->feedback)
                            <li class="list-group-item">
                                <strong>@lang('Admin Response')</strong>
                                <br>
                                <p>{{ __($deposit->feedback) }}</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        @can('super-admin')
            @if ($deposit->status == 1 || $deposit->status == 3 || $deposit->status == 0)
                <div class="col-xl-8 col-md-6 mb-30">
                    <div class="card b-radius--10 overflow-hidden box--shadow1">
                        <div class="card-body">
                            <h5 class="card-title mb-50 border-bottom pb-2">@lang('Agent Deposit Information')</h5>
                            @if ($deposit->status == 1 || $deposit->status == 3 || $deposit->status == 0)
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        @if($deposit->status != 2)
                                        <button class="btn btn-outline--success btn-sm ms-1 approvedBtn"
                                            data-action="{{ route('admin.agent.deposit.approve', $deposit->id) }}"
                                            data-question="@lang('Are you sure to approve this transaction?')"><i class="las la-check-double"></i>
                                            @lang('Approve')
                                        </button>
                                        @endif
                                        @if($deposit->status != 0)
                                        <button class="btn btn-outline--danger btn-sm ms-1 rejectBtn"
                                            data-id="{{ $deposit->id }}"
                                            data-amount="{{ $deposit->amount }} {{ __($deposit->deposit_currency) }}"
                                            data-username="{{ @$deposit->agent->username }}"><i class="las la-ban"></i>
                                            @lang('Reject')
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            @if ($deposit->status == 2)
                <div class="col-xl-8 col-md-6 mb-30">
                    <div class="card b-radius--10 overflow-hidden box--shadow1">
                        <div class="card-body">
                            <h5 class="card-title mb-50 border-bottom pb-2">@lang('Agent Deposit Information')</h5>
                            @if ($deposit->status == 2)
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <button class="btn btn-outline--danger btn-sm ms-1 backBtn"
                                            data-id="{{ $deposit->id }}"
                                            data-amount="{{ $deposit->amount }} {{ __($deposit->deposit_currency) }}"
                                            data-username="{{ @$deposit->agent->username }}"><i class="las la-ban"></i>
                                            @lang('Depoit Back')
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endcan
    </div>

    {{-- Approved MODAL --}}
    <div id="approveModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Deposit Confirmation')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.agent.deposit.approve', $deposit->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure to approve this transaction?</p>

                        <div class="form-group">
                            <label class="mt-2">Rate </label>
                            <input type="text" name="rate" class="form-control"
                                placeholder="1 {{ $deposit->deposit_currency }} = ? {{ $deposit->currency }}" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- BACK MODAL --}}
    <div id="backModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang(' Deposit Back')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.agent.deposit.back') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id">
                    <div class="modal-body">
                        <p>@lang('Are you sure to') <span class="fw-bold">@lang('reject')</span> <span
                                class="fw-bold withdraw-amount text--success"></span> @lang('deposit of') <span
                                class="fw-bold withdraw-user"></span>?</p>

                        <div class="form-group">
                            <label class="mt-2">@lang('Reason for Back')</label>
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
                <form action="{{ route('admin.agent.deposit.reject') }}" method="POST">
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
            $('.backBtn').on('click', function() {
                var modal = $('#backModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('.withdraw-amount').text($(this).data('amount'));
                modal.find('.withdraw-user').text($(this).data('username'));
                modal.modal('show');
            });

            $('.approvedBtn').on('click', function() {
                var modal = $('#approveModal');
                modal.find('input[name=id]').val($(this).data('id'));
                modal.find('input[name=rate]').val();
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
