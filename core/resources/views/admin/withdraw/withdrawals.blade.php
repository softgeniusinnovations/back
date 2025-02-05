@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        @if (request()->routeIs('admin.withdraw.log') ||
                request()->routeIs('admin.withdraw.method') ||
                request()->routeIs('admin.users.withdrawals') ||
                request()->routeIs('admin.users.withdrawals.method'))
            <div class="col-xl-4 col-sm-6 mb-30">
                <div class="widget-two box--shadow2 has-link b-radius--5 bg--success">
                    <a href="{{ route('admin.withdraw.approved') }}" class="item-link"></a>
                    <div class="widget-two__content">
                        <h2 class="text-white">{{ __(adminCurrency()) }}{{ showAmount($successful) }}</h2>
                        <p class="text-white">@lang('Approved Withdrawals')</p>
                    </div>
                </div><!-- widget-two end -->
            </div>
            <div class="col-xl-4 col-sm-6 mb-30">
                <div class="widget-two box--shadow2 has-link b-radius--5 bg--6">
                    <a href="{{ route('admin.withdraw.pending') }}" class="item-link"></a>
                    <div class="widget-two__content">
                        <h2 class="text-white">{{ __(adminCurrency()) }}{{ showAmount($pending) }}</h2>
                        <p class="text-white">@lang('Pending Withdrawals')</p>
                    </div>
                </div><!-- widget-two end -->
            </div>
            <div class="col-xl-4 col-sm-6 mb-30">
                <div class="widget-two box--shadow2 b-radius--5 has-link bg--pink">
                    <a href="{{ route('admin.withdraw.rejected') }}" class="item-link"></a>
                    <div class="widget-two__content">
                        <h2 class="text-white">{{ __(adminCurrency()) }}{{ showAmount($rejected) }}</h2>
                        <p class="text-white">@lang('Rejected Withdrawals')</p>
                    </div>
                </div><!-- widget-two end -->
            </div>
        @endif
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">

                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Gateway')</th>
                                    <th>@lang('Order Number')</th>
                                    <th>@lang('Agent')</th>
                                    <th>@lang('Initiated')</th>
                                    <th>@lang('Bettor')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Status')</th>
                                    @can('super-admin')
                                        <th>@lang('Assigned')</th>
                                    @endcan
                                    <th>@lang('Action')</th>

                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdraw)
                                    @php
                                        $details = $withdraw->withdraw_information != null ? json_encode($withdraw->withdraw_information) : null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span
                                                class="fw-bold">{{ @$withdrawal->method->name ? @$withdrawal->method->name : @$withdraw->transectionProviders->name ?? 'Cash agent' }}</span>
                                            <br>
                                        </td>
                                        <td><strong>{{ $withdraw->trx }}</strong></td>
                                        <td>
                                            {{ @$withdraw->agent->username }}
                                        </td>
                                        <td>
                                            {{ showDateTime($withdraw->created_at) }} <br>
                                            {{ diffForHumans($withdraw->created_at) }}
                                        </td>

                                        <td>
                                            <span class="fw-bold">{{ $withdraw->user->fullname }}</span>
                                            <br>
                                            <span class="small"> <a
                                                    href="{{ appendQuery('search', @$withdraw->user->username) }}"><span>@</span>{{ $withdraw->user->username }}</a>
                                            </span>
                                        </td>

                                        <td>
                                            <strong title="@lang('Amount after charge')">
                                                {{ showAmount($withdraw->amount - $withdraw->charge) }}
                                                {{ __($withdraw->user->currency) }}
                                            </strong>

                                        </td>

                                        <td>
                                            @php echo $withdraw->statusBadge @endphp
                                        </td>
                                        @can('super-admin')
                                            <td>
                                                {{ $withdraw->assign_agent == 0 ? 'Not Assigned' : 'Assigned' }}
                                            </td>
                                        @endcan
                                        <td>
                                            @if (
                                                $withdraw->assign_agent == 1 ||
                                                    auth()->user()->can('super-admin'))
                                                <a href="{{ route('admin.withdraw.details', $withdraw->id) }}"
                                                    class="btn btn-sm btn-outline--primary ms-1">
                                                    <i class="la la-desktop"></i> @lang('Details')
                                                </a>
                                            @endif
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
                @if ($withdrawals->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($withdrawals) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form dateSearch='yes' />
@endpush
