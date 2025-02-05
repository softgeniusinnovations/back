@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="row justify-content-end mb-3">
        <div class="col-xl-8 col-md-8">
            <form action="" >
                <div class="input-group" class="w-100">
                   @if(auth()->user()->is_affiliate == "1")
                   <input type="text" class="form--control form-control" name="dates" value="{{ request()->dates }}" placeholder="Search by date">
                   @endif
                   <input class="form-control form--control" name="search" type="text" value="{{ request()->search }}"
                   placeholder="@lang('Search by Transaction')">
                   <button class="input-group-text bg--base text-white">Generate</button>
                </div>
            </form>
        </div>
    </div>
    
    
<div class="table-responsive">
@if(auth()->user()->is_affiliate == "1")
    <table class="table-responsive table-sm custom--table table table-striped table-bordered">
        <thead>
            <tr>
                <th>@lang('Currency')</th>
                <th>@lang('Date')</th>
                <th>@lang('Payout')</th>
                <th>@lang('Revenue')</th>
                <th>@lang('Balance')</th>
                <th>@lang('Status')</th>
            </tr>
        </thead>

        <tbody>
            @forelse($withdraws as $withdraw)
                <tr>
                    <td>
                        {{ __(userCurrency()) }}
                    </td>
                    <td class="text-center">
                        {{ $withdraw->created_at->format('d.m.Y') }}
                    </td>

                    <td>
                        <strong title="@lang('Amount with charge')">
                            {{ showAmount($withdraw->amount + $withdraw->charge) }} {{ __(getSymbol(userCurrency())) }}
                         </strong>
                    </td>
                    <td>
                        <strong title="@lang('Revenue')">
                            {{ showAmount($withdraw->available_amount) }} {{ __(getSymbol(userCurrency())) }}
                         </strong>
                    </td>
                    <td>
                        <strong title="@lang('Balance')">
                            {{ showAmount($withdraw->available_amount - ($withdraw->amount + $withdraw->charge)) }} {{ __(getSymbol(userCurrency())) }}
                         </strong>
                    </td>
                    <td class="text-center">
                        @php echo $withdraw->statusBadge @endphp
                    </td>
                    {{-- <td>
                        <button class="btn btn--view detailBtn"
                            data-user_data="{{ json_encode($withdraw->withdraw_information) }}" type="button"
                            @if ($withdraw->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $withdraw->admin_feedback }}" @endif
                            @if ($withdraw->status != Status::PAYMENT_REJECT) disabled @endif>
                            <span class="las la-desktop"></span>
                        </button>
                    </td> --}}
                </tr>
            @empty
                <tr>
                    <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="float-end mt-2">
        {{ $withdraws->links() }}
    </div>
    @else
    <table class="table-responsive table-sm custom--table table table-striped table-bordered">
        <thead>
            <tr>
                <th>@lang('Gateway | Transaction')</th>
                <th>@lang('Initiated')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Status')</th>
                <th>@lang('Details')</th>
            </tr>
        </thead>

        <tbody>
            @forelse($withdraws as $withdraw)
                <tr>
                    <td>
                        <div class="">
                            <span class="text--base fw-bold">
                                @if (@$withdraw->transectionProviders->name)
                                    {{ __(@$withdraw->transectionProviders->name) }}
                                @else
                                    {{ __('Cash Agent') }}
                                @endif
                            </span>
                            <br>
                            <small> {{ $withdraw->trx }} </small>
                        </div>
                    </td>

                    <td class="text-center">
                        {{ showDateTime($withdraw->created_at) }}<br>{{ diffForHumans($withdraw->created_at) }}
                    </td>

                    <td>
                        <div class="">
                            <strong title="@lang('Amount with charge')">
                                {{ showAmount($withdraw->amount + $withdraw->charge) }} {{ __(userCurrency()) }}
                            </strong>
                        </div>
                    </td>
                    <td class="text-center">
                        @php echo $withdraw->statusBadge @endphp
                    </td>

                    <td>
                        <button class="btn btn--view detailBtn"
                            data-user_data="{{ json_encode($withdraw->withdraw_information) }}" type="button"
                            @if ($withdraw->status == Status::PAYMENT_REJECT) data-admin_feedback="{{ $withdraw->admin_feedback }}" @endif
                            @if ($withdraw->status != Status::PAYMENT_REJECT) disabled @endif>
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

    <div class="float-end mt-2">
        {{ $withdraws->links() }}
    </div>
    @endif
</div>

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
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .daterangepicker td.in-range {
        background-color: #357ebd;
        border-color: transparent;
        color: #ffffff;
        border-radius: 0;
    }

</style>
@endpush
@push('script')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $('input[name="dates"]').daterangepicker({
            autoUpdateInput: false
        });

        $('input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('input[name="dates"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

    </script>
    <script>
        (function($) {
            "use strict";
            $('.detailBtn').on('click', function() {
                var modal = $('#detailModal');
                var userData = $(this).data('user_data');
                var html = ``;
                userData.forEach(element => {
                    if (element.type != 'file') {
                        html += `<li class="d-flex flex-wrap align-items-center justify-content-between">
                                    <span class="deposit-card__title fw-bold">
                                        ${element.name}
                                    </span>
                                    <span class="deposit-card__amount">
                                        ${element.value}
                                    </span>
                                </li>`;
                    }
                });
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
