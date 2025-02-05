@extends($activeTemplate . 'layouts.master')
@section('master')
    <h5 class="mt-0 mb-0">
        {{ __($pageTitle) }}
    </h5>

    <table class="table table-responsive--md custom--table custom--table-separate">
        <thead>
            <tr>
                <th>@lang('Bet Id')</th>
                <th>@lang('Game')</th>
                <th>@lang('Market')</th>
                <th>@lang('Option')</th>
                <th>@lang('Status')</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($bets as $betData)
                <tr>
                    <td> <span class="text--base">{{ __($bet->bet_id) }}</span> </td>
                    <td>
                        {{ __(@$betData->option->question->game->teamOne->short_name) }} <span class="text--base">@lang('vs')</span>
                        {{ __($betData->option->question->game->teamTwo->short_name) }}
                    </td>
                    <td> {{ __($betData->option->question->title) }} </td>
                    <td> {{ __($betData->option->name) }} </td>
                    <td> @php echo $betData->betStatusBadge @endphp </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if (!request()->routeIs('user.betlog.multi.details'))
        <div class="mt-2 float-end">
            {{ $bets->links() }}
        </div>
    @endif
@endsection
