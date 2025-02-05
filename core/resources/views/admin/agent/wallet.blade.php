@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('create-wallet-number')
            <div class="col-lg-12">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.agent.diposit.wallet.store') }}" method="post">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Wallet Name')</label>
                                        <input class="form-control form--control mb-3" name="name" type="text"
                                            value="{{ old('name') }}" required placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Wallet number')</label>
                                        <input class="form-control form--control mb-3" name="wallet_number" type="text"
                                            value="{{ old('wallet_number') }}" required placeholder="Wallet Number">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Available currency</label> <br>
                                        @foreach ($currency as $c)
                                            <input type="checkbox" name="currency[]" value="{{ $c['value'] }}"
                                                id="{{ $c['name'] }}"> <label
                                                for="{{ $c['name'] }}">{{ $c['name'] }}</label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <button class="btn btn-primary">Create</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('create-wallet-number')
            <div class="col-lg-12">
                <div class="card b-radius--10">
                    <div class="card-body p-0">
                        <div class="table-responsive--md table-responsive">
                            <table class="table--light style--two table">
                                <thead>
                                    <tr>
                                        <th>@lang('Name')</th>
                                        <th>@lang('Wallet Number')</th>
                                        <th>@lang('Currency')</th>
                                        <th>@lang('Status')</th>
                                        <th>@lang('Created at')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($wallets as $wallet)
                                        <tr>
                                            <td>{{ $wallet->name }}</td>
                                            <td>{{ $wallet->wallet_number }}</td>
                                            <td>
                                                @php
                                                    $currencyes = json_decode($wallet->currency ?? [], true);
                                                @endphp
                                                @foreach ($currencyes as $key => $currency)
                                                    {{ $currency['name'] }} {{ count($currencyes) - 1 > $key ? ',' : '' }}
                                                @endforeach
                                            </td>
                                            <td>
                                                {{ @$wallet->status == 1 ? 'Active' : 'Inactive' }}
                                            </td>
                                            <td>{{ $wallet->created_at->diffForHumans() }}</td>
                                            <td>
                                                <a href="{{ route('admin.agent.diposit.wallet.edit', $wallet->id) }}"
                                                    class="btn btn-sm btn-outline--primary">Edit</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center" colspan="100%">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($wallets->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($wallets) }}
                        </div>
                    @endif
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {

        })(jQuery);
    </script>
@endpush
