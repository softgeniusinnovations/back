@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="d-flex justify-content-between align-items-center mt-0 flex-wrap gap-3 pb-3">
    <div class="action-area d-flex flex-wrap gap-2">
        <a class="btn btn-outline--base btn-sm @if (!request()->type) active @endif" href="{{ route('user.bets') }}">@lang('All')</a>
        <a class="btn btn-outline--base btn-sm @if (request()->type == 'pending') active @endif" href="{{ route('user.bets', 'pending') }}">@lang("Yesterday's Earning")</a>
        <a class="btn btn-outline--base btn-sm @if (request()->type == 'won') active @endif" href="{{ route('user.bets', 'won') }}">@lang("Today Earning")</a>
    </div>
</div>

<div class="bet-table">
    <table class="table-responsive--md custom--table custom--table-separate table">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('Bet No.')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Date')</th>
            </tr>
        </thead>

        {{-- <tbody>
            @forelse ($bets as $bet)
                <tr>
                    <td><span class="fw-bold">{{ __($bet->bet_number) }}</span> </td>
                    <td>
                        @php echo $bet->betTypeBadge @endphp
                    </td>
                    <td> {{ $bet->bets->count() }} </td>
                    <td> {{ getAmount($bet->stake_amount, 8) }} {{ __($general->cur_text) }} </td>
                    <td> {{ getAmount($bet->return_amount, 8) }} {{ __($general->cur_text) }} </td>
                    <td>
                        @if ($bet->amount_returned)
                            <span class="badge badge--warning">@lang('Pending')</span>
                        @else
                            @php echo $bet->betStatusBadge @endphp
                        @endif
                    </td>
                    <td>
                        <button class="btn btn--view view-btn" data-amount_returned="{{ $bet->amount_returned }}" data-bet_details='{{ $bet->bets }}' type="button">
                            <i class="las la-desktop"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                </tr>
            @endforelse
        </tbody> --}}
    </table>
</div>
@endsection
