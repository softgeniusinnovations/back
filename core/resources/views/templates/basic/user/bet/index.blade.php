@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="d-flex justify-content-between align-items-center mt-0 flex-wrap gap-3 pb-3">
        <div class="action-area d-flex flex-wrap gap-2">
            <a class="btn btn-outline--base btn-sm @if (!request()->type) active @endif"
                href="{{ route('user.bets') }}">@lang('All')</a>
            <a class="btn btn-outline--base btn-sm @if (request()->type == 'pending') active @endif"
                href="{{ route('user.bets', 'pending') }}">@lang('Pending')</a>
            <a class="btn btn-outline--base btn-sm @if (request()->type == 'won') active @endif"
                href="{{ route('user.bets', 'won') }}">@lang('Won')</a>
            <a class="btn btn-outline--base btn-sm @if (request()->type == 'lose') active @endif"
                href="{{ route('user.bets', 'lose') }}">@lang('Lose')</a>
            <a class="btn btn-outline--base btn-sm @if (request()->type == 'refunded') active @endif"
                href="{{ route('user.bets', 'refunded') }}">@lang('Refunded')</a>
        </div>
        <form action="">
            <div class="input-group">
                <input class="form-control form--control" name="search" type="text" value="{{ request()->search }}"
                    placeholder="@lang('Search by bet number')">
                <button class="input-group-text bg--base text-white"><i class="las la-search"></i></button>
            </div>
        </form>
    </div>
    <div class="bet-table">
        <table class="table-responsive--md custom--table custom--table-separate table">
            <thead>
                <tr>
                    <th>@lang('Bet No.')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Bet Count')</th>
                    <th>@lang('Invested')</th>
                    <th>@lang('Return')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Details')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($bets as $bet)
                    <tr>
                        <td><span class="fw-bold">{{ __($bet->bet_number) }}</span> </td>
                        <td>
                            @php echo $bet->betTypeBadge @endphp
                        </td>
                        <td> {{ $bet->bets->count() }} </td>
                        <td> {{ getAmount($bet->stake_amount, 8) }} {{ userCurrency() }} </td>
                        <td> {{ getAmount($bet->return_amount, 8) }} {{ userCurrency() }} </td>
                        <td>
                            @if ($bet->amount_returned)
                                <span class="badge badge--warning">@lang('Pending')</span>
                            @else
                                @php echo $bet->betStatusBadge @endphp
                            @endif
                        </td>
                        <td>
                            <button class="btn btn--view view-btn" data-amount_returned="{{ $bet->amount_returned }}"
                                data-bet_details='{{ $bet->bets }}' type="button">
                                <i class="las la-desktop"></i>
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
        {{ $bets->links() }}
    </div>

    <div class="modal fade" id="betDetailModal" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true"
        tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="m-0">@lang('Bet Detail')</h5>
                    <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <table class="table-responsive--md custom--table custom--table-separate table">
                        <thead>
                            <tr>
                                <th>@lang('Game')</th>
                                <th>@lang('Market')</th>
                                <th>@lang('Option')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";
            $('.view-btn').on('click', function(e) {
                var modal = $('#betDetailModal');
                modal.find('tbody').html('');
                var betDetails = $(this).data('bet_details');
                var betStatus = $(this).data('amount_returned');
                var tableRow = ``;
                $.each(betDetails, function(index, detail) {
                    var status = ``;
                    if (betStatus) {
                        status = `<span class="badge badge--warning">@lang('Pending')</span>`
                    } else {
                        if (detail.status == 1) {
                            status = `<span class="badge badge--success">@lang('Won')</span>`
                        } else if (detail.status == 2) {
                            status = `<span class="badge badge--warning">@lang('Pending')</span>`
                        } else if (detail.status == 3) {
                            status = `<span class="badge badge--danger">@lang('Lose')</span>`
                        } else if (detail.status == 4) {
                            status = `<span class="badge badge--info">@lang('Refund')</span>`
                        }
                    }
                    tableRow += `<tr>
                                    <td data-label="@lang('Game')">
                                        ${detail.option.question.game.team_one.name}
                                        <span class="text--base px-1">@lang('vs')</span>
                                        ${detail.option.question.game.team_two.name}
                                    </td>
                                    <td data-label="@lang('Market')">${detail.option.question.title}</td>
                                    <td data-label="@lang('Option')">${detail.option.name}</td>
                                    <td data-label="@lang('Status')">
                                        ${status}
                                    </td>
                                </tr>`
                });
                modal.find('tbody').html(tableRow);
                modal.modal('show');
            });
        })(jQuery)
    </script>
@endpush
