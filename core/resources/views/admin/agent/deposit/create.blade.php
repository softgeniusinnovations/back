@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('make-deposit')
            <div class="col-lg-6 m-auto">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.agent.deposit.store') }}" method="post" enctype="multipart/form-data">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <h4 class="p-2 border-bottom">Make a Payment</h4>
                                <div class="p-2">
                                    <p class="p-2 d-flex"
                                        style="background: #F2ECEC; color: #CC6878; gap:15px; border-radius:5px; gap:15px">
                                        <i class="fa fa-info"
                                            style="display: flex; width: 45px; height: 25px; align-items:center; justify-content:center; border-radius:50%; background: #CC6878; color: #fff"></i>
                                        <span>Before requesting a deposit, please make a transfer using the payment details
                                            started
                                            below. You will receive a new wallet number within the next hour.</span>
                                    </p>
                                </div>
                                <p class="d-flex align-items-center justify-content-between mt-2"><strong>Method:</strong>
                                    <span>Crypto</span>
                                </p>
                                <p class="d-flex align-items-center justify-content-between mt-2"><strong>Wallet Name:</strong>
                                    <span>{{ $wallet->name }}</span>
                                </p>
                                <p class="d-flex align-items-center justify-content-between mt-2"><strong>Wallet
                                        Number:</strong>
                                    <span>{{ $wallet->wallet_number }}</span>
                                </p>
                                <p class="d-flex align-items-center justify-content-between mt-2"><strong>Available
                                        currency:</strong>
                                    <span>
                                        @php
                                            $currencyes = json_decode($wallet->currency ?? [], true);
                                        @endphp
                                        @foreach ($currencyes as $key => $currency)
                                            {{ $currency['name'] }} {{ count($currencyes) - 1 > $key ? ',' : '' }}
                                        @endforeach
                                    </span>
                                </p>
                                <input type="hidden" name="wallet_id" value="{{ $wallet->id }}">
                                <div class="col-lg-12 mt-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Your Name')</label>
                                        <input class="form-control form--control mb-1" name="name" type="text"
                                            value="{{ old('name') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Coin name')</label>
                                        <div class="input-group">
                                            <input class="form-control form--control" name="deposit_currency" type="text"
                                                value="{{ old('deposit_currency') }}" required placeholder="USDT">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Amount')</label>
                                        <input class="form-control form--control mb-1" name="amount" type="text"
                                            value="{{ old('amount') }}" required placeholder="300">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Your Wallet address')</label>
                                        <input class="form-control form--control mb-1" name="depositor_wallet_number"
                                            type="text" value="{{ old('depositor_wallet_number') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Transaction ID')</label>
                                        <input class="form-control form--control mb-1" name="deposit_trx" type="text"
                                            value="{{ old('deposit_trx') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Transaction Image')</label>
                                        <input class="form-control form--control mb-1" name="file" type="file"
                                            value="{{ old('file') }}" required accept="image/*">
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <button class="btn btn-primary">Request</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

    </div>
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
        })(jQuery);
    </script>
@endpush
