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
                                <th>@lang('Name')</th>
                                <th>@lang('Email')</th>
                                <th>@lang('Address')</th>
                                <th>@lang('Phone')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Joined At')</th>
                                <th>@lang('Email Verified')</th>
                                <th>@lang('KYC Verified')</th>
                                <th>@lang('Balance')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($affiliate as $item)
                                <tr style="background: {{ $item->status == 0 ? '#ffecec' : '' }}">
                                    <td>
                                        <span>{{ $item->firstname }} {{ $item->lastname }} -
                                            {{ $item->country_code }}</span>
                                        <br>
                                        <a href="{{route('admin.affiliate.details', $item->id)}}">
                                            <span>@</span>{{ $item->username }}
                                        </a>
                                    </td>

                                    <td>{{ $item->email }} </td>
                                    <td style="text-align: left">

                                        @foreach ($item->address as $key => $details)
                                            <b>{{ $key }}</b>: {{ $details }} <br>
                                        @endforeach
                                    </td>
                                    <td>{{ $item->mobile }}</td>
                                    <td>
                                        @if ($item->status == 1)
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @elseif($item->status == 0)
                                            <span class="badge badge--warning">@lang('Inactive')</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
                                    </td>
                                    <td>{{ $item->ev == 1 ? 'Yes' : 'No' }}</td>
                                    <td>{{ $item->kv == 1 ? 'Yes' : 'No' }}</td>
                                    <td>{{ showAmount($item->balance) }} {{$item->currency}}</td>
                                    <td>
                                        <div class="button--group">
                                            <a href="javascript:void(0);" data-id="{{ $item->id }}" class="btn btn-sm btn-outline--danger">
                                                <i class="la la-ban"></i> @lang('Ban')
                                            </a>
                                            <a href="{{ route('admin.affiliate.details', $item->id) }}" class="btn btn-sm btn-outline--primary">
                                                <i class="las la-desktop"></i> @lang('Details')
                                            </a>
                                            @if (request()->routeIs('admin.users.kyc.pending'))
                                                <a href="{{ route('admin.users.kyc.details', $item->id) }}" target="_blank" class="btn btn-sm btn-outline--dark">
                                                    <i class="las la-user-check"></i>@lang('KYC Data')
                                                </a>
                                            @endif
                                        </div>
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

            @if ($affiliate->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($affiliate) }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
