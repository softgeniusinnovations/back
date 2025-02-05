@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="row justify-content-end mb-3">
        <div class="col-xl-5 col-md-8">
            <form action="">
                <div class="d-flex flex-wrap gap-4">
                    <div class="trans-number">
                        <div class="form--select">
                            <select class="form-select" name="type">
                                <option value="deposit" @selected($type == 'deposit')>@lang('Deposit commissions')</option>
                                <option value="bet" @selected($type == 'bet')>@lang('Bet place commissions')</option>
                                <option value="win" @selected($type == 'win')>@lang('Bet win commissions')</option>
                            </select>
                        </div>
                    </div>
                    <div class="trans-btn align-self-end">
                        <button class="btn btn--base btn--xl"><i class="las la-filter"></i> @lang('Filter')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="bet-table">
        <table class="table-responsive--md custom--table custom--table-separate table">
            <thead>
                <tr>
                    <th>@lang('Transaction ID')</th>
                    <th>@lang('From')</th>
                    <th>@lang('Level')</th>
                    <th>@lang('Percent')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Date')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td> {{ @$log->trx }} </td>
                        <td> {{ @$log->byWho->username }} </td>
                        <td> {{ __(ordinal($log->level)) }} @lang('Level') </td>
                        <td> {{ getAmount($log->percent) }} % </td>
                        <td> {{ showAmount($log->commission_amount) }} {{ __($general->cur_text) }} </td>
                        <td> {{ showDateTime($log->created_at, 'd M, Y') }} </td>
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
        {{ $logs->links() }}
    </div>
@endsection
