@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Bet Number')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Stake Amount')</th>
                                    <th>@lang('Return')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bets as $bet)
                                    <tr>
                                        <td>
                                            <span>{{ __($bet->bet_number) }}</span>
                                            <br>
                                            <a
                                                href="{{ route('admin.users.detail', $bet->user->id) }}"><span>@</span>{{ $bet->user->username }}</a>
                                        </td>

                                        <td> @php echo $bet->betTypeBadge @endphp </td>
                                        <td> {{ getAmount($bet->stake_amount, 8) }} {{ __($general->cur_text) }} </td>
                                        <td> {{ getAmount($bet->return_amount, 8) }} {{ __($general->cur_text) }} </td>
                                        <td> @php echo $bet->betStatusBadge @endphp </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--primary bet-detail"
                                                data-bet_details='{{ $bet->bets }}' type="button">
                                                <i class="las la-desktop"></i> @lang('Detail')
                                            </button>
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

                @if ($bets->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($bets) }}
                    </div>
                @endif
            </div>
        </div>
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
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Game')</th>
                                    <th>@lang('Market')</th>
                                    <th>@lang('Option')</th>
                                    <th>@lang('Odds')</th>
                                    <th>@lang('Status')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search by bet number" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.bet-detail').on('click', function(e) {
                var modal = $('#betDetailModal');
                modal.find('tbody').html('');
                var betDetails = $(this).data('bet_details');
                var tableRow = ``;
                console.log(betDetails);
                $.each(betDetails, function(index, detail) {
                    var status = ``;
                    if (detail.status == 1) {
                        status = `<span class="badge badge--success">@lang('Won')</span>`
                    } else if (detail.status == 2) {
                        status = `<span class="badge badge--warning">@lang('Pending')</span>`
                    } else if (detail.status == 3) {
                        status = `<span class="badge badge--danger">@lang('Lose')</span>`
                    } else if (detail.status == 4) {
                        status = `<span class="badge badge--primary">@lang('Refund')</span>`
                    }
                    tableRow += `<tr>
                                    <td data-label="@lang('Game')">
                                        ${detail.option.question.game.team_one.short_name}
                                        <span class="text--base">@lang('vs')</span>
                                        ${detail.option.question.game.team_two.short_name}
                                    </td>
                                    <td data-label="@lang('Market')">${detail.option.question.title}</td>
                                    <td data-label="@lang('Option')">${detail.option.name}</td>
                                    <td data-label="@lang('Odds')">${Math.abs(detail.option.odds)}</td>
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
