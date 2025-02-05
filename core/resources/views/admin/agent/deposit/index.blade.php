@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('TRX')</th>
                                    @can('super-admin')
                                        <th>@lang('Agent Name | Currency')</th>
                                    @endcan
                                    <th>@lang('File')</th>
                                    <th>@lang('Wallet Number')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Rate')</th>
                                    <th>@lang('Final Amount')</th>
                                    <th>@lang('Depositor Wallet | Trx')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created at')</th>
                                    @can('super-admin')
                                        <th>@lang('Action')</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $status = ['Rejected', 'Pending', 'Approved', 'Back'];
                                @endphp
                                @forelse ($deposits as $deposit)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.agent.deposit.show', $deposit->id) }}">{{ @$deposit->trx }}
                                            </a>
                                        </td>
                                        @can('super-admin')
                                            <td>
                                                <a href="{{ route('admin.agent.details', $deposit->id) }}"><span>@</span>{{ @$deposit->agent->username }}
                                                </a> <br>
                                                <small>{{ @$deposit->agent->currency }}</small>
                                            </td>
                                        @endcan
                                        <td><a href="{{ asset('/core/public/storage/agent/transactions/' . $deposit->file) }}">{{ $deposit->file }}
                                            </a></td>
                                        <td>{{ @$deposit->wallet->wallet_number }}</td>
                                        <td>{{ @$deposit->amount . ' ' . @$deposit->deposit_currency }}</td>
                                        <td>{{ @$deposit->rate }}</td>
                                        <td>
                                            @if ($deposit->status == 2)
                                                {{ $deposit->rate * $deposit->amount }} {{ @$deposit->agent->currency }}
                                            @else
                                                ---
                                            @endif
                                        </td>
                                        <td>{{ @$deposit->depositor_account }} <br>
                                            <small>{{ @$deposit->deposit_trx }}</small>
                                        </td>
                                        <td>{{ $status[$deposit->status] }}</td>
                                        <td> {{ \Carbon\Carbon::parse($deposit->created_at)->diffForHumans() }}</td>
                                        @can('super-admin')
                                            <td>
                                                <a href="{{ route('admin.agent.deposit.show', $deposit->id) }}"
                                                    class="btn btn-sm btn-outline--primary"><i class="fa fa-eye"></i>
                                                    Details</a>
                                            </td>
                                        @endcan
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

                @if ($deposits->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($deposits) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
