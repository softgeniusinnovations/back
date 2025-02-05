@extends($activeTemplate . 'layouts.master')
@section('master')
    <form action="{{ route('user.deposit.insert') }}" method="post">
        @csrf
        <input name="currency" type="hidden">
        <div class="card custom--card">
            <div class="card-header">
                <h5 class="card-title">@lang($pageTitle)</h5>
            </div>
            <div class="card-body">
                <div class="local">
                    <p class="p-4 d-flex" style="background: #dfe8f9; gap:15px; border-radius:5px; color:#6189d5"><i
                            class="fa fa-info"
                            style="display: flex; width: 26px; height: 26px; align-items:center; justify-content:center; border-radius:50%; background: #6189d5; color: #fff"></i>
                        Recommended payment method</p>
                </div>

                <div class="form-group">
                    <div class="row">
                        @foreach ($providers as $data)
                            <div class="col-md-2 mb-4 local">
                                <div class="border select-payment" style="cursor: pointer; background: #f4f5f7"
                                    data-min_val="{{ $data->dep_min_am }}" data-max_val="{{ $data->dep_max_am }}"
                                    data-image="{{ asset('/core/public/storage/providers/' . $data->file) }}"
                                    data-method="{{ $data->id }}" data-method_name={{ $data->name }}
                                    data-currency="{{ userCurrency() }}">
                                    <img src="{{ asset('/core/public/storage/providers/' . $data->file) }}" alt="Payment method"
                                        width="100%" height="70" style="object-fit: contain">
                                    <p class="m-0 p-1 bg-primary text-center text-light">{{ $data->name }}</p>
                                </div>
                            </div>
                        @endforeach

                        <div class="col-md-12 message-area" style="display: none">
                            <p class="p-4 d-flex " style="background: #F2ECEC; color: #CC6878; gap:15px; border-radius:5px">
                                <i class="fa fa-info"
                                    style="display: flex; width: 26px; height: 26px; align-items:center; justify-content:center; border-radius:50%; background: #CC6878; color: #fff"></i>
                                <span class="message"></span>
                            </p>
                        </div>

                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <p class="p-4 d-flex"
                                        style="background: #dfe8f9; gap:15px; border-radius:5px; color:#6189d5"><i
                                            class="fa fa-info"
                                            style="display: flex; width: 26px; height: 26px; align-items:center; justify-content:center; border-radius:50%; background: #6189d5; color: #fff"></i>
                                        Recommended Mob/Cash agent method</p>
                                </div>
                                <div class="col-md-2 mb-4 local">
                                    <div class="border select-payment" style="cursor: pointer; background: #f4f5f7">
                                        <a href="{{ route('user.deposit.mobcash') }}">
                                            <img src="{{ asset('/core/public/storage/providers/cash-agent.png') }}" alt="Payment method"
                                                width="100%" height="70" style="object-fit: contain">
                                            <p class="m-0 p-1 bg-primary text-center text-light">Mob cash</p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 payment-system-area" style="display: none">
                            <h4>1. Make a Transfer</h4>
                            <p class="p-4 d-flex " style="background: #F2ECEC; color: #CC6878; gap:15px; border-radius:5px">
                                <i class="fa fa-info"
                                    style="display: flex; width: 26px; height: 26px; align-items:center; justify-content:center; border-radius:50%; background: #CC6878; color: #fff"></i>
                                <span class="dep-note"></span>
                            </p>
                            <div class="border py-2">
                                <p class="d-flex mb-1 border-bottom px-2 mb-1"
                                    style="align-items: center; justify-content:space-between ">
                                    <strong>Bank
                                        Name</strong> <span class="bank-name"></span>
                                </p>
                                <p class="d-flex mb-1  border-bottom px-2 mb-1"
                                    style="align-items: center; justify-content:space-between ">
                                    <strong><span class="bank-name"></span>
                                        Wallet Number</strong> <span class="wallet-number"></span>
                                </p>
                                <p class="d-flex px-2 mb-0" style="align-items: center; justify-content:space-between ">
                                    <strong><span class="bank-name"></span> Wallet
                                        Name </strong> <span class="wallet-name"></span>
                                </p>
                            </div>
                            <h4>2. Request a Deposit</h4>
                            <input type="hidden" name="agent">
                            <input type="hidden" name="provider">
                            <input type="hidden" name="payment_gateway" value="local">
                            <input type="hidden" name="currency" value="{{ userCurrency() }}">
                            <div class="form-group">
                                <label for="sendamount">Amount</label>
                                <input type="number" value="" id="sendamount" name="amount" max=""
                                    min="" readonly class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="ybw">Your <span class="bank-name"></span> Wallet Number</label>
                                <input type="text" value="" id="ybw" name="payment_number"
                                    class="form-control" required>
                            </div>
                            <div class="form-group">
                                {{-- <label for="uname">Your Name</label> --}}
                                <input type="hidden" value="" id="uname" name="depositor_name"
                                    class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="transaction">Transaction ID</label>
                                <input type="text" value="" id="transaction" name="transaction_id"
                                    class="form-control" required>
                            </div>
                        </div>

                        {{-- payment modal area start --}}
                        <div class="payment-method-selection"
                            style="position: fixed; background: #000000bf; left: 0; top: 0; width: 100%; height: 100vh; z-index: 999999; align-items:center; justify-content:center; display: none">
                            <div class="payment-modal-area-start"
                                style="background: #F4F5F7; border: 1px solid #ddd; padding: 10px 15px; width: 350px; height: auto; position:relative">
                                <div class="payment-modal-header"
                                    style="border-bottom: 1px solid #ddd; min-height: 80px;">
                                    <div class="payemnt-method-image text-center"></div>
                                    <div class="close-btn"
                                        style="position: absolute;top: 5px; right: 5px; width:25px; height: 25px; display:flex; align-items:center; justify-content:center; background: #ff0000; color: #fff; cursor: pointer">
                                        X</div>
                                </div>
                                <div class="payment-modal-body">
                                    <p class="amount-min-max" style="font-size: 14px; font-weight: 800 mb-1 mt-2"></p>
                                    <input type="number" class="predict-number form-control mb-2" name="samount"
                                        placeholder="amount">
                                    <input type="hidden" name="method_id">
                                    <button type="button"
                                        class="form-control btn btn-sm btn-primary confirm-btn">Confirm</button>
                                </div>
                            </div>
                        </div>

                        {{-- payment modal area end --}}

                    </div>

                    <div class="text-end make-payment-btn" style="display: none">
                        <button class="btn btn--xl btn--base mt-3" type="submit">@lang('Make Payment')</button>
                    </div>
                </div>
            </div>
    </form>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            // payment gatway selection
            $('.select-payment').on('click', function() {
                var min = parseInt($(this).data('min_val'));
                var max = parseInt($(this).data('max_val'));
                var image = $(this).data('image');
                var paymentMethod = $(this).data('method');
                var currency = $(this).data('currency');
                if (paymentMethod) {
                    $('.payemnt-method-image').html('<img src="' + image + '" height="70" />');
                    $('.amount-min-max').text('Amount minimum ' + min + currency + ' / ' + 'maximum ' + max +
                        currency);
                    $('.predict-number').attr('min', min);
                    $('.predict-number').attr('max', max);
                    $('.predict-number').val(min);
                    $('[name=method_id]').val(paymentMethod);
                    $('.payment-method-selection').css('display', 'flex')
                }
            });

            $('.close-btn').on('click', function() {
                $('.payment-method-selection').hide();
            });


            // Confoirm button
            $(document).on('click', '.confirm-btn', function() {
                var value = parseInt($('.predict-number').val());
                var method_id = parseInt($('[name=method_id]').val());
                var min = parseInt($('.predict-number').attr('min'));
                var max = parseInt($('.predict-number').attr('max'));
                if (min <= value && value <= max && method_id) {
                    $.ajax({
                        url: "{{ route('user.deposit.agent') }}",
                        metho: 'GET',
                        data: {
                            provider: method_id
                        },
                        success: function(data) {
                            $('.local').hide();
                            if (data?.message) {
                                $('.message').text(data?.message);
                                $('.message-area').show();
                                $('.payment-method-selection').hide();
                                $('.make-payment-btn').hide();
                            } else {
                                $('.make-payment-btn').show();
                                $('.message-area').hide();
                                $('.payment-system-area').css('display', 'block');
                                $('.dep-note').text(data?.success[0]?.dep);
                                $('.bank-name').text(data?.success[0]?.method_name);
                                $('.wallet-number').text(data?.success[0]?.mobile);
                                $('.wallet-name').text(data?.success[0]?.wallet_name);
                                $('[name=amount]').val(value);
                                $('[name=agent]').val(data?.success[0]?.admin_id);
                                $('[name=provider]').val(data?.success[0]?.transection_provider_id);
                                $('.payment-method-selection').hide();
                            }

                        }
                    });
                } else {
                    alert('Please check the amount');
                }
            })



            // payment gateway selection
            $('[name=payment_gateway]').on('change', function() {
                var payment_gateway_value = $(this).find('option:selected').val();
                if (payment_gateway_value == 'local') {
                    $('[name=provider], [name=transaction_id], [name=payment_number]').attr('required', true);
                    $('.local').show();
                    $('.agent').hide();
                } else {
                    $('.agent').show();
                    $('.local').hide();
                }
                if (payment_gateway_value == 'cash') {
                    $.ajax({
                        url: "{{ route('user.deposit.agent') }}",
                        metho: 'GET',
                        data: {
                            gateway: payment_gateway_value
                        },
                        success: function(data) {
                            $('.select2').css({
                                'width': '100%',
                            });
                            $('[name=provider], [name=transaction_id], [name=payment_number]').attr(
                                'required', false);
                            $('.agent').show();
                            $('[name=agent]').html('');
                            $('[name=agent]').append(data);
                        }
                    });
                } else {
                    $('.agent').hide();
                }
            });


            // Provider selection
            $('[name=provider]').on('change', function() {
                var provider = $(this).find('option:selected').val();
                if (provider) {
                    $.ajax({
                        url: "{{ route('user.deposit.agent') }}",
                        metho: 'GET',
                        data: {
                            provider: provider
                        },
                        success: function(data) {
                            $('.select2').css({
                                'width': '100%',
                            });
                            $('.agent').show();
                            $('[name=agent]').html('');
                            $('[name=agent]').append(data);
                        }
                    })
                }
            });




            $('select[name=gateway]').change(function() {
                if (!$('select[name=gateway]').val()) {
                    return false;
                }
                var resource = $('select[name=gateway] option:selected').data('gateway');
                var fixed_charge = parseFloat(resource.fixed_charge);
                var percent_charge = parseFloat(resource.percent_charge);
                var rate = parseFloat(resource.rate)
                if (resource.method.crypto == 1) {
                    var toFixedDigit = 8;
                    $('.crypto_currency').removeClass('d-none');
                } else {
                    var toFixedDigit = 2;
                    $('.crypto_currency').addClass('d-none');
                }
                $('.min').text(parseFloat(resource.min_amount).toFixed(2));
                $('.max').text(parseFloat(resource.max_amount).toFixed(2));
                var amount = parseFloat($('input[name=amount]').val());
                if (!amount) {
                    amount = 0;
                }
                if (amount <= 0) {
                    return false;
                }
                var charge = parseFloat(fixed_charge + (amount * percent_charge / 100)).toFixed(2);
                $('.charge').text(charge);
                var payable = parseFloat((parseFloat(amount) + parseFloat(charge))).toFixed(2);
                $('.payable').text(payable);
                var final_amo = (parseFloat((parseFloat(amount) + parseFloat(charge))) * rate).toFixed(
                    toFixedDigit);
                $('.final_amo').text(final_amo);
                if (resource.currency != '{{ $general->cur_text }}') {
                    var rateElement =
                        `<span class="fw-bold">@lang('Conversion Rate')</span> <span><span  class="fw-bold">1 {{ __($general->cur_text) }} = <span class="rate">${rate}</span>  <span class="method_currency">${resource.currency}</span></span></span>`;
                    $('.rate-element').html(rateElement)
                    $('.rate-element').removeClass('d-none');
                    $('.in-site-cur').removeClass('d-none');
                    $('.rate-element').addClass('d-flex');
                    $('.in-site-cur').addClass('d-flex');
                } else {
                    $('.rate-element').html('')
                    $('.rate-element').addClass('d-none');
                    $('.in-site-cur').addClass('d-none');
                    $('.rate-element').removeClass('d-flex');
                    $('.in-site-cur').removeClass('d-flex');
                }
                $('.method_currency').text(resource.currency);
                $('.method_currency').text(resource.currency);
                $('input[name=currency]').val(resource.currency);
                $('input[name=amount]').on('input');
            });
            $('input[name=amount]').on('input', function() {
                $('select[name=gateway]').change();
                $('.amount').text(parseFloat($(this).val()).toFixed(2));
            });
        })(jQuery);
    </script>
@endpush
