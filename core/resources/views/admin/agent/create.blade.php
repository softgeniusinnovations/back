@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-3">
                    <form action="{{ route('admin.agent.register') }}" method="post" enctype="multipart/form-data">
                        @method('POST')
                        @csrf
                        <div class="row">
                            <div class="col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Name')</label>
                                    <input class="form-control form--control mb-3" name="name" type="text"
                                        value="{{ old('name') }}" required>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Email')</label>
                                    <input class="form-control form--control mb-3" name="email" type="email"
                                        value="{{ old('email') }}" required>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Country')</label>
                                    <div class="form--select">
                                        <select class="form-select" name="country" required class="form-control">
                                            @foreach ($countries as $key => $country)
                                                <option data-mobile_code="{{ $country->dial_code }}"
                                                    data-code="{{ $key }}" value="{{ $country->country }}"
                                                    {{ old('country') == $country->country ? 'selected' : '' }}>
                                                    {{ __($country->country) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Mobile')</label>
                                    <div class="input-group">
                                        <span class="input-group-text mobile-code"></span>
                                        <input name="mobile_code" type="hidden">
                                        <input name="country_code" type="hidden">
                                        <input class="form-control phone-number form--control checkUser border-start-0 px-1"
                                            name="mobile" type="number" value="{{ old('mobile') }}" required>
                                    </div>
                                    <small class="text--danger mobileExist"></small>
                                </div>
                            </div>


                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Username')</label>
                                    <input class="form-control form--control mb-3" name="username" type="text"
                                        value="{{ old('username') }}" required>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Password')</label>
                                    <input class="form-control form--control mb-3" name="password" type="password"
                                        value="{{ old('password') }}" required placeholder="*******">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Agent Type')</label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="1" @selected(old('type') == '1')>Agent</option>
                                        <option value="2" @selected(old('type') == '2')>Cash Agent</option>
                                        <option value="3" @selected(old('type') == '3')>Mob Agent</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Deposit commision')</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form--control" name="deposit_commision"
                                            type="text" value="{{ old('deposit_commision') }}" required min="0"
                                            max="100" placeholder="10">
                                        <span class="input-group-text" id="basic-addon1">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Withdraw commision')</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form--control" name="withdraw_commision"
                                            type="text" value="{{ old('withdraw_commision') }}" required min="0"
                                            max="100" placeholder="10">
                                        <span class="input-group-text" id="basic-addon1">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Telegram link')</label>
                                    <input type="text" name="telegram_link" class="form-control"
                                        value="{{ old('telegram_link') }}" placeholder="t.me/name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Bot token')</label>
                                    <input type="text" name="bot_token" class="form-control"
                                        value="{{ old('bot_token') }}" placeholder="Bot token" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Bot name')</label>
                                    <input type="text" name="bot_name" class="form-control"
                                        value="{{ old('bot_name') }}" placeholder="Bot name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Channel Chat ID')</label>
                                    <input type="text" name="chat_id" class="form-control"
                                        value="{{ old('chat_id') }}" placeholder="Chat ID" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Photo')</label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                            </div>


                            <div class="col-md-12 only-for-agent">
                                <h4 class="mb-2">@lang('Providers')</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table">
                                            <tbody class="providers">
                                                <tr class="item">
                                                    <td>
                                                        <div class="form-group">
                                                            <label class="form-label">@lang('Provider name')</label>
                                                            <select name="providers[]" id="providers"
                                                                class="form-control" required>
                                                                <option>---Select the provider name---</option>
                                                                @foreach ($providers as $key => $provider)
                                                                    <option value="{{ $provider->id }}">
                                                                        {{ $provider->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td style="text-align: left">
                                                        <div class="form-group">
                                                            <label class="form-label">
                                                                @lang('Wallet name')
                                                            </label>
                                                            <input type="text" name="wallet_name[]"
                                                                class="form-control" placeholder="Wallet name" required>
                                                        </div>
                                                    </td>
                                                    <td style="text-align: left">
                                                        <div class="form-group">
                                                            <label class="form-label">@lang('Wallet number')</label>
                                                            <input type="text" name="wallet_number[]"
                                                                class="form-control" placeholder="Wallet number" required>
                                                        </div>
                                                    </td>
                                                    <td style="text-align: left">
                                                        <div class="form-group">
                                                            <label class="form-label">@lang('Comments')</label>
                                                            <input type="text" name="comments[]"
                                                                class="form-control" placeholder="Comments">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-row">X</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td style="text-align:left" colspan="3"><button type="button"
                                                            class="btn btn-sm btn-primary new-provider-add">+ Add new
                                                            Provider</button></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 only-for-cash-agent" style="display: none">
                                <div class="form-group">
                                    <label class="form-label">@lang('Agent address')</label>
                                    <input type="text" name="address" class="form-control"
                                        value="{{ old('address') }}" placeholder="Agent address" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Balance')</label>
                                    <input type="text" name="balance" class="form-control"
                                        value="{{ old('balance') }}" placeholder="Amount" required min="0">
                                </div>
                            </div>

                            <div class="col-md-12 col-sm-12 text-right">
                                <button class="btn btn-primary">Submit</button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {

            // Provider add
            $('.new-provider-add').on('click', function() {
                var newItem = $('.item:first-child').clone();
                $('.providers').append(newItem);
            });

            $('table').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });


            $(window).on('load', function() {
                let load_agent_type = $('[name=type]').find('option:selected').val();
                if (load_agent_type == 1) {
                    $('.only-for-cash-agent').hide();
                    $('.only-for-agent').show();
                    $('[name=address]').attr('required',
                        false);
                    $('[name="wallet_name\\[\\]"], [name="wallet_number\\[\\]"]').attr('required', true);
                } else {
                    $('.only-for-agent').hide();
                    $('.only-for-cash-agent').show();
                    $('[name=address]').attr('required',
                        true);
                    $('[name="wallet_name\\[\\]"], [name="wallet_number\\[\\]"]').attr('required', false);
                }
            });

            $('[name=type]').on('change', function() {
                var agent_type = $(this).find('option:selected').val();
                if (agent_type == 1) {
                    $('.only-for-cash-agent').hide();
                    $('.only-for-agent').show();
                    $('[name=address]').attr('required',
                        false);
                    $('[name="wallet_name\\[\\]"], [name="wallet_number\\[\\]"]').attr('required', true);
                } else {
                    $('.only-for-agent').hide();
                    $('.only-for-cash-agent').show();
                    $('[name=address]').attr('required',
                        true);
                    $('[name="wallet_name\\[\\]"], [name="wallet_number\\[\\]"]').attr('required', false);
                }
            });


            $('.mobile-code').on('click', function(e) {
                $('[name=mobile]').focus();
            });

            $('[name=country]').on('change', function() {
                $('[name=mobile_code]').val($(this).find('option:selected').data('mobile_code'));
                $('[name=country_code]').val($(this).find('option:selected').data('code'));
                $('.mobile-code').text('+' + $(this).find('option:selected').data('mobile_code'));
            }).change();

            @if ($mobileCode)
                $(`option[data-code={{ $mobileCode }}]`).attr('selected', '');
            @endif


        })(jQuery);
    </script>
@endpush
