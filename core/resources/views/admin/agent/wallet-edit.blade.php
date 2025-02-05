@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('create-wallet-number')
            <div class="col-lg-6 m-auto">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.agent.diposit.wallet.update', $wallet->id) }}" method="post">
                            @method('PUT')
                            @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Wallet Name')</label>
                                        <input class="form-control form--control mb-3" name="name" type="text"
                                            value="{{ $wallet->name }}" required placeholder="Wallet Name">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Wallet Number')</label>
                                        <input class="form-control form--control mb-3" name="wallet_number" type="text"
                                            value="{{ $wallet->wallet_number }}" required placeholder="Wallet Number">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Available currency</label> <br>
                                        @foreach ($currency as $c)
                                            <input type="checkbox" name="currency[]" value="{{ $c['value'] }}"
                                                id="{{ $c['name'] }}"
                                                {{ in_array($c['value'], array_column(json_decode($wallet->currency, true), 'value')) ? 'checked' : '' }}>
                                            <label for="{{ $c['name'] }}">{{ $c['name'] }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Status')</label>
                                        <select class="form-control form--control mb-3" name="status" required>
                                            <option value="1" @selected($wallet->status == '1')>Active</option>
                                            <option value="0" @selected($wallet->status == '0')>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

    </div>
@endsection
