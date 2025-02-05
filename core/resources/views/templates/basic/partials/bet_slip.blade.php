@php
    $betData = collect(session()->get('bets'));
    $optionId = $betData->pluck('option_id')->toArray();
    $options = App\Models\Option::whereIn('id', $optionId)
        ->with(['question.game.teamOne', 'question.game.teamTwo'])
        ->get();
    $bets = $betData->zip($options);
    $totalReturn = 0;
@endphp

<div class="betslip">
    <div class="betslip__head d-flex align-items-center justify-content-between gap-2">
        <h6 class="m-0 text-white"> <i class="fa-thin fa-clipboard-list-check"></i> @lang('Bet Slip')</h6>
        <span class="just-close-betslip" style="display:inline-block; width: 20px; height:20px; color:red; cursor:pointer">X</span>
    </div>

    <div class="list-group bet-type py-1">
        <button class="bet-type__btn active bet-tab-single betTypeBtn" data-type="{{ Status::SINGLE_BET }}" type="button">@lang('Single Bet')</button>
        <button class="bet-type__btn bet-tab-multi betTypeBtn" data-type="{{ Status::MULTI_BET }}" type="button">@lang('Multi Bet')</button>
    </div>
    <div class="list-group bet-type py-1">
        <button class="bet-type__btn active bet-tab-deposit betTypeAmountBtn" data-amount_type="{{ Status::BET_DEPOSIT }}" type="button">@lang('Deposit') ({{showAmount(app('userBalance')['deposit'])}})</button>
        @if(app('userBalance')['bonus'] > 0 && app('userBalance')['kyc'])
        <button class="bet-type__btn bet-tab-bonus betTypeAmountBtn" data-amount_type="{{ Status::BET_BONUS }}" type="button">@lang('Bonus') ({{showAmount(app('userBalance')['bonus'])}})</button>
        @endif
        @if(app('userBalance')['tramcard'] > 0 && app('userBalance')['kyc']) 
        <button class="bet-type__btn bet-tab-tramcard betTypeAmountBtn" data-amount_type="{{ Status::BET_TRAMCARD }}" type="button">@lang('Tramcard') ({{showAmount(app('userBalance')['tramcard'])}})</button> 
        @endif
    </div>

    <div class="betslip__body" data-simplebar="init">
        <div class="simplebar-wrapper">
            <div class="simplebar-height-auto-observer-wrapper">
                <div class="simplebar-height-auto-observer"></div>
            </div>
            <div class="simplebar-mask">
                <div class="simplebar-offset">
                    <div class="simplebar-content-wrapper" role="region" aria-label="scrollable content" tabindex="0">
                        <div class="simplebar-content">
                            <ul class="list betslip__list">
                                @foreach ($bets as $bet)
                                    @include($activeTemplate . 'partials.bet_slip_item', ['bet' => $bet[0], 'option' => $bet[1]])
                                @endforeach
                            </ul>
                            <span class="empty-slip-message">
                                <span class="d-flex justify-content-center align-items-center">
                                    <img src="{{ asset($activeTemplateTrue . 'images/empty_list.png') }}" alt="@lang('image')">
                                </span>
                                @lang('Your selections will be displayed here')
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="simplebar-placeholder"></div>
        </div>

        <div class="simplebar-track simplebar-horizontal">
            <div class="simplebar-scrollbar"></div>
        </div>
        <div class="simplebar-track simplebar-vertical">
            <div class="simplebar-scrollbar"></div>
        </div>
    </div>

    <div class="betslip__footer" id="betSlipBody">
        <ul class="list betslip__footer-list">
            <li>
                <div class="betslip__list-content">
                    <div class="betslip__list-match">@lang('Singles') (x<span class="bet-slip-count">{{ $bets->count() }}</span>)</div>
                    <div class="betslip__list-bet">
                        <span class="betslip__list-odd">@lang('Stake Per Bet')</span>
                    </div>

                </div>
                <div class="betslip-righ">
                    <div class="betslip__list-ratio">
                        <input class="amount" name="total_invest" type="number" step="any" placeholder="0.0">
                        <span>@lang('STAKE')</span>
                    </div>
                    <div class="bet-return">
                        <small class="text--danger total-stake-amount"></small>
                        <small class="text--danger total-validation-msg"></small>
                        <span>@lang('Returns'): @if (auth()->check()) {{ auth()->user()->currency }} @endif<span class="total-return-amount">{{ showAmount($totalReturn) }}</span></span>
                        
                    </div>
                </div>
            </li>
        </ul>
        <div class="betslip__footer-bottom d-flex align-items-center">
            <input class="form-control form--control betslip-form" type="number" placeholder="@lang('Enter Amount')">
            <button class="delete-btn deleteAll"> <i class="las la-trash-alt"></i></button>
            <div class="place-btn">
                <button class="btn btn--base btn--md sm-text betslip__footer-btn bet-place-btn betPlaceBtn" type="button">
                    @lang('PLACE BET')
                </button>
            </div>
        </div>
    </div>
</div>

@auth
    <div class="modal fade custom--modal" id="betModal" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form id="betForm" action="{{ route('user.bet.place') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input name="stake_amount" type="hidden">
                        <input name="type" type="hidden">
                        <input name="amount_type" type="hidden">
                        <p>@lang('Are you sure to place bet')?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--base" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endauth

@push('style-lib')
    <link href="{{ asset($activeTemplateTrue . 'css/skeleton.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            let betType;
            let amountType;
            let stakeAmount;
            let totalStakeAmount;
            let auth = Number("{{ auth()->user() ? 1 : 0 }}");
            let multiBet = Number("{{ Status::MULTI_BET }}");
            let singleBet = Number("{{ Status::SINGLE_BET }}");
            let depositAmountBet = Number("{{Status::BET_DEPOSIT}}");
            let bonusAmountBet = Number("{{Status::BET_BONUS}}");
            let tramcardAmountBet = Number("{{Status::BET_TRAMCARD}}");
            let tramBalance = {{app('userBalance')['tramcard']}};

            initBetType();
            totalStakeInput();
            betReturnAmount();
            showEmptyMessage()
            initBetAmountType();

            function showEmptyMessage() {
                if (Number($('.betslip__list li').length)) {
                    $('.empty-slip-message').hide();
                } else {
                    $('.empty-slip-message').show();
                }
            }

            function initBetType() {
                betType = sessionStorage.getItem('type');
                if (!betType) {
                    betType = singleBet;
                    sessionStorage.setItem('type', betType);
                }
                $('.bet-type').find('.betTypeBtn').removeClass('active');
                $('.bet-type').find(`.betTypeBtn[data-type="${betType}"]`).addClass('active');
                controlStakeInputFields();
               
            }
            
            
            function initBetAmountType(){
                amountType = sessionStorage.getItem('amountType');
                if(!amountType){
                    amountType = depositAmountBet;
                    sessionStorage.setItem('amountType', amountType);
                }
                $('.bet-type').find('.betTypeAmountBtn').removeClass('active');
                $('.bet-type').find(`.betTypeAmountBtn[data-amount_type="${amountType}"]`).addClass('active');
            }
            
             $('.betTypeAmountBtn').on('click', function() {
                amountType = Number($(this).data('amount_type'));
                if ($(this).hasClass('active')) {
                    return;
                }

                $('.total-validation-msg').text('');
                if(amountType == 3){
                     $('[name=total_invest]').val(tramBalance)
                     sessionStorage.setItem('total_stake_amount', tramBalance);
                }
                $('.total-stake-amount').text('');
                $('.betslip__list li').find('.validation-msg').text('')

                sessionStorage.setItem('type', amountType);

                $(`.betTypeAmountBtn`).removeClass('active');
                $(this).addClass('active');
               
            });

            function controlStakeInputFields() {
                if (betType == multiBet) {
                    $('.betslip__list li .betslip-right').hide();
                    $('.betslip__list-odd').hide();

                } else {
                    $('.betslip__list li .betslip-right').show();
                    $('.betslip__list-odd').show();
                }
            }

            function betSlipCount() {
                let totalBetSlipData = $('.betslip__list li').length;

                if (!totalBetSlipData) {
                    sessionStorage.removeItem('total_stake_amount');
                    totalStakeInput();
                    showEmptyMessage();
                    return 0;
                }
                return totalBetSlipData;
            }

            function setStakeAmount(amount = 0) {
                $('.investAmount').each(function(index) {
                    $(this).val(amount);
                    let odd = Number($(this).closest('li').data('option_odds'));
                    $(this).closest('.betslip-right').find('.bet-return-amount').text(Math.abs(amount * odd).toFixed(2))
                });
            }

            function totalStakeInput(totalStakeAmount = 0) {
                totalStakeAmount = sessionStorage.getItem('total_stake_amount');
                $('[name=total_invest]').val(totalStakeAmount);
            }

            function totalMultiBetReturnAmount() {
                let totalMultiBetReturnAmount = $('[name=total_invest]').val();
                let multiBetOdd = 1;
                $('.betslip__list li').each(function(index) {
                    var odd = $(this).data('option_odds');
                    multiBetOdd *= odd;
                });
                $('.total-return-amount').text(Math.abs(totalMultiBetReturnAmount * multiBetOdd).toFixed(2));
            }

            function totalSingleBetReturnAmount() {
                let totalSingleBetReturnAmount = 0;
                $('.investAmount').each(function(index) {
                    var odd = Number($(this).closest('li').data('option_odds'));
                    totalSingleBetReturnAmount += Number($(this).val()) * odd;
                });
                $('.total-return-amount').text(Math.abs(totalSingleBetReturnAmount).toFixed(2));
            }

            function betReturnAmount() {
                betType == multiBet ? totalMultiBetReturnAmount() : totalSingleBetReturnAmount();
            }

            function showTotalBetSlipCount(count = 0) {
                $('.bet-slip-count').text(count);
                $('.bet-count').text(count);
            }

            function skeleton(type) {
                let loader = `<li class="loading">
                                    <button class="betslip__list-close"></button>
                                    <div class="betslip__list-content">
                                        <span class="betslip__list-match"></span>
                                        <span class="betslip__list-team"></span>
                                        <span class="betslip__list-question"></span>
                                        <div class="betslip__list-text"></div>
                                    </div>
                                    <div class="betslip-right">
                                        <div class="betslip__list-ratio">
                                            <span></span>
                                        </div>
                                        <span class="betslip-return"></span>
                                    </div>
                                </li>`;
                $('.betslip__list').append(loader);

                if (type == 'show') {
                    $(document).find('.loading').show();
                } else {
                    $(document).find('.loading').remove();
                }
            }


            function removeSessionTotalStakeAmount() {
                if (sessionStorage.getItem('total_stake_amount')) {
                    sessionStorage.removeItem('total_stake_amount');
                }
            }

            $(document).on('click', '.oddBtn', function() {
                let button = $(this);
                if ($(this).hasClass('active')) {
                    removeBet(button);
                    return;
                }

                $('.empty-slip-message').hide();

                skeleton('show');

                let data = {
                    _token: '{{ csrf_token() }}',
                    id: $(this).data('option_id'),
                    type: betType
                }

                if (betType == singleBet) {
                    data.amount = sessionStorage.getItem('total_stake_amount');
                }

                $.get(`{{ route('bet.slip.add') }}`, data,
                    function(response) {
                        if (response.error) {
                            skeleton('hide');
                            $('.empty-slip-message').show();
                            notify('error', response.error);
                        } else {
                            button.addClass('active');
                            setTimeout(() => {
                                skeleton('hide');
                                $('.betslip__list').append(response);
                                controlStakeInputFields();
                                showTotalBetSlipCount(betSlipCount())
                                betReturnAmount();
                            }, 500);
                        }
                    }
                );
            });

            $(document).on('input focusout', '.investAmount', function(event) {
                $('.total-validation-msg').text('');
                $('.total-stake-amount').text('');
                $('.betslip__list li').find('.validation-msg').text('')

                stakeAmount = Number($(this).val());
                if (!stakeAmount) {
                    return;
                }

                let odd = Number($(this).closest('li').data('option_odds'));
                $(this).closest('.betslip-right').find('.bet-return-amount').text(Math.abs(stakeAmount * odd).toFixed(2))


                if (event.type == 'focusout') {
                    let data = {
                        _token: '{{ csrf_token() }}',
                        id: $(this).closest('li').data('option_id'),
                        amount: stakeAmount
                    }
                    $.ajax({
                        type: "POST",
                        url: `{{ route('bet.slip.update') }}`,
                        data: data,
                        success: function(response) {

                            if (betType == singleBet) {
                                var isInvestAmountSame = false;
                                var firstInvestAmountValue = $('.investAmount').first().val();
                                if (betSlipCount() > 1) {
                                    $('.investAmount').each(function(index) {
                                        var currentInvestAmountValue = $(this).val();
                                        if (currentInvestAmountValue && currentInvestAmountValue == firstInvestAmountValue) {
                                            isInvestAmountSame = true;
                                        } else {
                                            isInvestAmountSame = false;
                                        }
                                    });
                                }
                                if (isInvestAmountSame) {
                                    $('[name=total_invest]').val(firstInvestAmountValue)
                                    sessionStorage.setItem('total_stake_amount', firstInvestAmountValue);
                                } else {
                                    removeSessionTotalStakeAmount();
                                    totalStakeInput();
                                }
                            } else {
                                removeSessionTotalStakeAmount();
                                totalStakeInput();
                            }
                            betReturnAmount();
                        }
                    });
                }
            });


            $(document).on('click', '.removeFromSlip', function() {
                removeBet($(this));
            });

            function removeBet(button) {
                $('.total-validation-msg').text('');
                $('.total-stake-amount').text('');
                $('.betslip__list li').find('.validation-msg').text('')
                let id = button.data('option_id');
                let data = {
                    _token: '{{ csrf_token() }}'
                };
                $.post(`{{ route('bet.slip.remove', '') }}/${id}`, data,
                    function(response) {
                        if (response.status == 'success') {
                            $(document).find(`.oddBtn[data-option_id="${id}"]`).removeClass('active');
                            $(document).find(`.removeFromSlip[data-option_id="${id}"]`).parent().parent().parent().remove();
                            showTotalBetSlipCount(betSlipCount())
                            betReturnAmount();
                        }
                    }
                );
            }

            $('.betTypeBtn').on('click', function() {
                betType = Number($(this).data('type'));
                if ($(this).hasClass('active')) {
                    return;
                }

                $('.total-validation-msg').text('');
                $('.total-stake-amount').text('');
                $('.betslip__list li').find('.validation-msg').text('')

                sessionStorage.setItem('type', betType);

                $(`.betTypeBtn`).removeClass('active');
                $(this).addClass('active');
                stakeAmount = sessionStorage.getItem('total_stake_amount');

                if (stakeAmount && betType == singleBet) {
                    setStakeAmount(stakeAmount);
                    let totalSingleStakeAmount = 0;
                    $('.investAmount').each(function(index) {
                        if (!$(this).val()) {
                            $(this).closest('.betslip-right').find('.validation-msg').text(`@lang('Stake is required')`);
                        } else {
                            totalSingleStakeAmount += Number($(this).val());
                        }
                    });
                    if (totalSingleStakeAmount) {
                        stakeLimitValidation(totalSingleStakeAmount)
                    }
                } else {
                    totalStakeInput(stakeAmount);
                    if (stakeAmount) {
                        stakeLimitValidation(stakeAmount);
                    }
                }
                controlStakeInputFields();
                betReturnAmount();
            });

            $('.deleteAll').on('click', function() {
                let data = {
                    _token: '{{ csrf_token() }}'
                };
                $.post(`{{ route('bet.slip.remove.all', '') }}`, data,
                    function(response) {
                        if (response.status == 'success') {
                            $('.betslip__list li').remove();
                            $('.oddBtn').removeClass('active');
                            showTotalBetSlipCount(betSlipCount());
                            betReturnAmount();
                        }
                    }
                );
            })

            $('[name=total_invest]').on('input focusout', function(event) {

                $('.total-validation-msg').text('');
                $('.total-stake-amount').text('');
                $('.betslip__list li').find('.validation-msg').text('');


                totalStakeAmount = Number($(this).val());
                if (!totalStakeAmount) {
                    let hasValue = false;
                    $('.investAmount').each(function(index) {
                        if ($(this).val()) {
                            hasValue = true;
                        }
                    });
                    if (hasValue) {
                        removeSessionTotalStakeAmount();
                    }
                    return;
                } else {
                    sessionStorage.setItem('total_stake_amount', totalStakeAmount);
                    setStakeAmount(Number($(this).val()));
                    betReturnAmount();
                }

                if (event.type == 'focusout') {
                    let data = {
                        _token: '{{ csrf_token() }}',
                        amount: totalStakeAmount,
                    }
                    $.ajax({
                        type: "POST",
                        url: `{{ route('bet.slip.update.all') }}`,
                        data: data,
                        success: function(response) {
                            $('.total-validation-msg').text('');
                        }
                    });
                }

            })

            $('.betPlaceBtn').on('click', function(e) {
                let error = false;
                let message = '';
                let totalBetCount = betSlipCount();
                let finalStakeAmount = 0;

                if (betType == multiBet && totalBetCount < 2) {
                    notify('error', "Minimum of two bets are required for multi bet");
                    return;
                }

                if (betType == multiBet) {
                    finalStakeAmount = Number($('[name=total_invest]').val());
                    if (!finalStakeAmount) {
                        $('.total-validation-msg').text(`@lang('Stake amount is required')`);
                        return;
                    }

                } else {
                    if (!totalBetCount) {
                        notify('error', "Your bet slip is empty");
                        return;
                    }
                    finalStakeAmount = 0;

                    $('.investAmount').each(function(index) {
                        if (!$(this).val()) {
                            $(this).closest('.betslip-right').find('.validation-msg').text(`@lang('Stake is required')`);
                            error = true;
                        } else {
                            finalStakeAmount += Number($(this).val());
                        }
                    });

                    if (error) {
                        return;
                    }
                }

                let stakeLimit = stakeLimitValidation(finalStakeAmount);
                if (stakeLimit) {
                    return;
                }

                stakeAmount = finalStakeAmount;
                if (auth) {
                    var modal = $("#betModal");
                    modal.find('[name=stake_amount]').val(finalStakeAmount);
                    modal.find('[name=type]').val(betType);
                    modal.find('[name=amount_type]').val(amountType);
                } else {
                    var modal = $("#loginModal");
                    var html = `<input type="hidden" name="location" value=${window.location.href}/>`;
                    modal.find('.input--group').prepend(html);
                }
                modal.modal('show');
            });

            function stakeLimitValidation(finalAmount) {
                let minLimit = betType == singleBet ? Number("{{ getAmount($general->single_bet_min_limit) }}") : Number("{{ getAmount($general->multi_bet_min_limit) }}");
                let maxLimit = betType == singleBet ? Number("{{ getAmount($general->single_bet_max_limit) }}") : Number("{{ getAmount($general->multi_bet_max_limit) }}");
                if (finalAmount < minLimit) {
                    $('.total-stake-amount').text(`Total stake {{ auth()->check() ? auth()->user()->currency :$general->cur_sym }}${finalAmount}`)
                    $('.total-validation-msg').text(`Min stake limit {{ auth()->check() ? auth()->user()->currency :$general->cur_sym }}${minLimit}`);
                    return true;
                }

                if (finalAmount > maxLimit) {
                    $('.total-stake-amount').text(`Total stake {{ auth()->check() ? auth()->user()->currency :$general->cur_sym }}${finalAmount}`)
                    $('.total-validation-msg').text(`Max stake limit {{ auth()->check() ? auth()->user()->currency :$general->cur_sym }}${maxLimit}`);
                    return true;
                }
                return false;
            }

            $('#betForm').on('submit', function(e) {
                sessionStorage.removeItem('total_stake_amount');
                return true;
            });
        })(jQuery);
    </script>
@endpush
