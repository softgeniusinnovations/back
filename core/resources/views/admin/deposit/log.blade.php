@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        @if (request()->routeIs('admin.deposit.list') ||
                request()->routeIs('admin.deposit.method') ||
                request()->routeIs('admin.users.deposits') ||
                request()->routeIs('admin.users.deposits.method'))
            <div class="col-xxl-3 col-sm-6 mb-30">
                <div class="widget-two box--shadow2 b-radius--5 bg--success has-link">
                    <a class="item-link" href="{{ route('admin.deposit.successful') }}"></a>
                    <div class="widget-two__content">
                        <h2 class="text-white">{{ __(adminCurrency()) }}{{ showAmount($successful) }}</h2>
                        <p class="text-white">@lang('Successful Deposit')</p>
                    </div>
                </div><!-- widget-two end -->
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <div class="widget-two box--shadow2 b-radius--5 bg--6 has-link">
                    <a class="item-link" href="{{ route('admin.deposit.pending') }}"></a>
                    <div class="widget-two__content">
                        <h2 class="text-white">{{ __(adminCurrency()) }}{{ showAmount($pending) }}</h2>
                        <p class="text-white">@lang('Pending Deposit')</p>
                    </div>
                </div><!-- widget-two end -->
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <div class="widget-two box--shadow2 has-link b-radius--5 bg--pink">
                    <a class="item-link" href="{{ route('admin.deposit.rejected') }}"></a>
                    <div class="widget-two__content">
                        <h2 class="text-white">{{ __(adminCurrency()) }}{{ showAmount($rejected) }}</h2>
                        <p class="text-white">@lang('Rejected Deposit')</p>
                    </div>
                </div><!-- widget-two end -->
            </div>
            <div class="col-xxl-3 col-sm-6 mb-30">
                <div class="widget-two box--shadow2 has-link b-radius--5 bg--dark">
                    <a class="item-link" href="{{ route('admin.deposit.initiated') }}"></a>
                    <div class="widget-two__content">
                        <h2 class="text-white">{{ __(adminCurrency()) }}{{ showAmount($initiated) }}</h2>
                        <p class="text-white">@lang('Initiated Deposit')</p>
                    </div>
                </div><!-- widget-two end -->
            </div>
        @endif

        <div class="col-md-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway')</th>
                                    <th>@lang('Order No')</th>
                                    <th>@lang('Agent')</th>
                                    <th>@lang('Initiated')</th>
                                    <th>@lang('Bettor')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Transaction No')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                    @php
                                        $details = $deposit->detail ? json_encode($deposit->detail) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if (@$deposit->gateway->alias)
                                                <span class="fw-bold"> <a
                                                        href="{{ appendQuery('method', @$deposit->gateway->alias) }}">{{ __(@$deposit->gateway->name) }}</a>
                                                </span>
                                            @elseif (@$deposit->transectionProviders->name)
                                                <span>{{ @$deposit->transectionProviders->name }}</span>
                                            @else
                                                <span>Cash Agent</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $deposit->trx }}</strong>
                                        </td>

                                        <td>
                                            {{ @$deposit->agent->username }}
                                        </td>

                                        <td>
                                            {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ @$deposit->user->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a
                                                    href="{{ appendQuery('search', @$deposit->user->username) }}"><span>@</span>{{ $deposit->user->username }}</a>
                                            </span>
                                        </td>
                                        <td>
                                            <strong title="@lang('Amount with charge')">
                                                {{ showAmount($deposit->amount + $deposit->charge) }}
                                                {{ __($deposit->user->currency) }}
                                               
                                            </strong>
                                        </td>
                                        <td>
                                            <strong> {{ $deposit->method_trx_number }}</strong>
                                        </td>
                                        <td>
                                            @php echo $deposit->statusBadge @endphp
                                        </td>
                                        <td>
                                            <a class="btn btn-sm btn-outline--primary ms-1"
                                                href="{{ route('admin.deposit.details', $deposit->id) }}">
                                                <i class="la la-desktop"></i> @lang('Details')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($deposits->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($deposits) @endphp
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    @if (!request()->routeIs('admin.users.deposits') && !request()->routeIs('admin.users.deposits.method'))
        <x-search-form dateSearch='yes' />
    @endif
@endpush
