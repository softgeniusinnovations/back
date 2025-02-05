@extends('admin.layouts.app')

@php
    $types = ['super-admin', 'agent', 'cash-agent', 'mob-agent', 'affiliator', 'support', 'report'];
@endphp

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Agent Name')</th>
                                    <th>@lang('Identity')</th>
                                    <th>Balance</th>
                                    <th>@lang('Email')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Address')</th>
                                    <th>@lang('Phone')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Joined At')</th>
                                    <th>@lang('Verified')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($agents as $agent)
                                    <tr style="background: {{ $agent->status == 0 ? '#ffecec' : '' }}">
                                        <td>
                                            <span>{{ __($agent->name) }} -
                                                {{ $agent->country_code }}</span>
                                            <br>
                                            <a href="{{ route('admin.agent.details', $agent->id) }}">
                                                <span>@</span>{{ $agent->username }}
                                            </a>
                                        </td>
                                        <td> {{$agent->identity}}</td>
                                        <td>{{@$agent->currency .' '. $agent->balance}}</td>

                                        <td> {{ $agent->email }} </td>
                                        <td> {{ $types[@$agent->type === null ? 0 : @$agent->type] }}</td>
                                        <td> {{ $agent->type > 1 ? $agent->address : '' }}</td>
                                        <td> {{ $agent->phone }}</td>
                                        <td>
                                            @if ($agent->type > null || $agent->type > 0)
                                                <a
                                                    class="btn {{ $agent->status == 1 ? 'btn-primary' : 'btn-danger' }} "href="{{ route('admin.agent.status', $agent->id) }}">{{ $agent->status == 1 ? 'Active' : 'Inactive' }}</a>
                                            @endif
                                        </td>
                                        <td> {{ \Carbon\Carbon::parse($agent->created_at)->diffForHumans() }}</td>
                                        <td> {{ $agent->email_verified_at ? 'Yes' : 'No' }}</td>
                                        <td>
                                            @if ($agent->type == 1)
                                                <button class="btn btn-sm btn-outline--primary bet-detail"
                                                    data-bet_details='{{ @$agent->transectionProviders }}' type="button">
                                                    <i class="las la-desktop"></i> @lang('Providers (' . $agent->transectionProviders->count() . ')')
                                                </button>
                                            @endif
                                            @can('agent-update')
                                                <a href="{{ route('admin.agent.edit', $agent->id) }}" title="Edit"
                                                    class="btn btn-sm btn-outline--primary"><i class="fa fa-pencil"></i></a>
                                               @can('agent-password-change')
                                                 <a href="{{ route('admin.agent.password', $agent->id) }}" title="Password change"
                                                    class="btn btn-sm btn-outline--danger"><i class="fa fa-key"></i></a>
                                               @endcan
                                               @can('agent-amount-change')
                                                 <a href="{{ route('admin.agent.change.amount', $agent->id) }}" title="Amount change"
                                                    class="btn btn-sm btn-outline--success"><i class="fa fa-dollar"></i></a>
                                               @endcan
                                               @can('agent-dashboard-login')
                                                 <a href="{{ route('admin.agent.dashboard', $agent->id) }}" title="Agent Dashboard"
                                                    class="btn btn-sm btn-outline--success"><i class="fa fa-sign-in"></i></a>
                                               @endcan
                                                    
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($agents->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($agents) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="betDetailModal" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true"
        tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="m-0">@lang('Transaction providers list')</h5>
                    <span class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('#')</th>
                                    <th>@lang('Provider name')</th>
                                    <th>@lang('Wallet name')</th>
                                    <th>@lang('Wallet number')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search by agent" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.bet-detail').on('click', function(e) {

                var modal = $('#betDetailModal');
                modal.find('tbody').html('');
                var betDetails = $(this).data('bet_details');
                var tableRow = ``;
                $.each(betDetails, function(index, detail) {

                    tableRow += `<tr>
                            <td>${++index}</td>
                            <td>${detail?.name}</td>
                            <td>${detail?.pivot?.wallet_name}</td>
                            <td>${detail?.pivot?.mobile}</td>
                            <td>${detail?.pivot?.status == 1 ? 'Active' : 'Inactive'}</td>
                            <td><button class="btn btn-sm btn-outline--primary status-btn" data-pro_id="${detail?.pivot?.id}">${detail?.pivot?.status == 1 ? 'Inactive' : 'Active'}</button></td>
                        </tr>`
                });
                modal.find('tbody').html(tableRow);
                modal.modal('show');
            });



            // Transaction providers status change
            $(document).on('click', '.status-btn', function() {
                var id = $(this).data('pro_id');
                if (id) {
                    $.ajax({
                        method: 'POST',
                        url: "{{ route('admin.agent.provider.status') }}",
                        data: {
                            id: id,
                            '_token': "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Successfully status updated.');
                                location.reload();
                            } else {
                                alert('Something went wrong!');
                                location.reload();
                            }
                        }
                    })
                }
            });
        })(jQuery)
    </script>
@endpush
