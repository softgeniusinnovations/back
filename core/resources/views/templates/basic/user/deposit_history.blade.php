@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="row justify-content-end mb-3">
        <div class="col-xl-5 col-md-8">
            <form action="">
                <div class="input-group">
                    <input class="form-control form--control" name="search" type="text" value="{{ request()->search }}"
                        placeholder="@lang('Search by Transaction')">
                    <button class="input-group-text bg--base text-white"><i class="las la-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="bet-table">
        <table class="table-responsive--md custom--table custom--table-separate table">
            <thead>
                <tr>
                    <th>@lang('Gateway') | @lang('TRX. No.')</th>
                    <th>@lang('Amount') | @lang('Deposit No')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Initiated')</th>
                    <th>@lang('Details')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $deposit)
                    <tr>
                        <td>
                            <div class="">
                                <span class="text--base fw-bold">
                                    @if (@$deposit->transectionProviders->name)
                                        {{ __(@$deposit->transectionProviders->name) }}
                                    @else
                                        {{ __('Cash Agent') }}
                                    @endif
                                </span>
                                <br>
                                <small> {{ $deposit->trx }} </small>
                            </div>
                        </td>
                        <td>
                            <div class="">
                                <strong data-bs-toggle="tooltip" data-bs-title="@lang('Amount with charge')">
                                    {{ showAmount($deposit->amount + $deposit->charge) }} {{ __(userCurrency()) }}
                                </strong>
                            </div>
                            <small>{{ $deposit->method_trx_number }}</small>
                        </td>
                        <td class="text-center">
                            @php echo $deposit->statusBadge @endphp
                        </td>

                        @php
                            $details = $deposit->detail != null ? json_encode($deposit->detail) : null;
                        @endphp

                        <td class="text-center">
                            {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                        </td>


                        <td>
                            <button class="btn btn--view @if ($deposit->method_code == 0) detailBtn @endif" type="button"
                                @if ($deposit->method_code == 0) data-info="{{ $details }}" @endif
                                @if ($deposit->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $deposit->admin_feedback }}" @endif
                                @if ($deposit->status != Status::PAYMENT_REJECT) disabled @endif>
                                <span class="las la-desktop"></span>
                            </button>
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

    <div class="float-end mt-2">
        {{ $deposits->links() }}
    </div>

    {{-- DETAILS MODAL --}}
    <div class="modal fade custom--modal" id="detailModal" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="deposit-card">
                        <ul class="deposit-card__list list userData">
                        </ul>
                    </div>
                    <div class="feedback mt-2 pt-1 pb-1"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>

    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');

                var userData = $(this).data('info');
                var html = '';
                if (userData) {
                    userData.forEach(element => {
                        if (element.type != 'file') {
                            html += `<li class="d-flex flex-wrap aligh-items-center justify-content-between">
                                        <span class="deposit-card__title fw-bold">
                                            ${element.name}
                                        </span>
                                        <span class="deposit-card__amount">
                                            ${element.value}
                                        </span>
                                    </li>`;
                        }
                    });
                }

                modal.find('.userData').html(html);

                if ($(this).data('admin_feedback') != undefined) {
                    var adminFeedback = `
                        <div class="my-3">
                            <strong>@lang('Admin Feedback')</strong>
                            <p>${$(this).data('admin_feedback')}</p>
                        </div>
                    `;
                } else {
                    var adminFeedback = '';
                }

                if (adminFeedback) {
                    modal.find('.feedback').html(adminFeedback).addClass('deposit-card');
                } else {
                    modal.find('.feedback').removeClass('deposit-card').empty();
                }

                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
