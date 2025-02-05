<li data-option_id="{{ $option->id }}" data-option_odds="{{ $bet->odds }}">
    <div class="betslip__list-content">
        <div>
            <div class="betslip__list-team">{{ __(@$option->question->game->teamOne->short_name) }} @lang('vs') {{ __(@$option->question->game->teamTwo->short_name) }}</div>
        </div>
        <span class="betslip__list-question">{{ __(@$option->question->title) }}</span>
        <span class="betslip__list-match">{{ __($option->name) }}</span>
        @if (isSuspendBet($bet))
            <div class="betslip__list-text text--danger fw-bold">@lang('Suspended')</div>
        @else
            <div class="d-flex gap-2">
                <div class="betslip__list-text">{{ rateData($bet->odds) }}</div>
                <div class="betslip__list-text">{{ $bet->checker }}</div>
            </div>
        @endif
    </div>

    <div class="betslip-right">
    <div class="d-flex justify-content-end">
                <button class="betslip__list-close text--danger removeFromSlip" data-option_id="{{ $option->id }}" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <div class="betslip__list-ratio">
            <input class="investAmount" name="invest_amount" type="number" @if (@$bet->stake_amount) value="{{ @$bet->stake_amount }}" @endif autocomplete="off" step="any" placeholder="0.0">
            <span>@lang('STAKE')</span>
        </div>
        <small class="text--danger validation-msg"></small>
        <span class="betslip-return">@lang('Returns'):
             @if (auth()->check()) {{ auth()->user()->currency }} @endif<span class="bet-return-amount">{{ showAmount($bet->return_amount) }}</span>
        </span>
    </div>
</li>
