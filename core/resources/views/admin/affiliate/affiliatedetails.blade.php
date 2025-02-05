@extends('admin.layouts.app')
@php
    $types = ['super-admin', 'agent', 'cash-agent', 'mob-agent', 'affiliator', 'support', 'report'];
@endphp
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    <h5>Account Details : </h5>
                    <div class="row">
                        <div class="col-sm-6">
                            <p><b>Name: </b>{{ $affiliate->firstname }} {{ $affiliate->lastname }}</p>
                            <p><b>Username: </b>{{ $affiliate->username }}</p>
                            <p><b>Email: </b>{{ $affiliate->email }}</p>
                            <p><b>Mobile: </b>{{ $affiliate->mobile }}</p>
                            <p><b>Currency: </b>{{ $affiliate->currency }}</p>
                            {{-- <p><b>Date of Birth: </b>{{ $affiliate->dob }}</p> --}}
                            <p><b>Current Balance: </b>{{ showAmount($affiliate->balance) }} {{ $affiliate->currency }}</p>
                            <p><b>Youtube: </b><a href="{{ $affiliate->youtube_link }}">{{ $affiliate->youtube_link }}</a>
                            </p>
                            <p><b>Website: </b><a href="{{ $affiliate->website }}">{{ $affiliate->website }}</a></p>
                            <p><b>Joined At: </b>{{ \Carbon\Carbon::parse($affiliate->created_at)->diffForHumans() }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p>
                                @foreach ($affiliate->address as $key => $details)
                                    <span><b class="">{{ Str::title($key) }}</b>:
                                        {{ $details }}</span> <br>
                                @endforeach
                            </p>
                            <p><b>Email Verified: </b>{{ $affiliate->ev == 1 ? 'Yes' : 'No' }}</p>
                            <p><b>KYC Verified: </b>{{ $affiliate->kv == 1 ? 'Yes' : 'No' }}</p>
                            <p><b>Promo Code Count: </b> {{ $promocode->count() }}</p>
                            <p><b>Registered User: </b> {{ $registredUser->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 mt-3">
            <div class="card b-radius--10">
                <div class="card-body">
                    <h5>Promo Code List</h5>
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <th>@lang('Promo Code')</th>
                                <th>@lang('Percentage')</th>
                                <th>@lang('Is Approved')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Create At')</th>
                                <th>@lang('Update At')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($promocode as $item)
                                    <tr>
                                        <td>{{ $item->promo_code }}</td>
                                        <td>{{ $item->promo_percentage ?? '0' }}%</td>
                                        <td>{{ $item->is_approved == 1 ? 'Yes' : 'No' }}</td>
                                        <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</td>
                                        {{-- <td>
                                            <a href="{{ route('admin.promo.edit', $item->id) }}"
                                                class="icon-btn btn--primary ml-1" data-toggle="tooltip" title=""
                                                data-original-title="Edit">
                                                <i class="las la-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.promo.delete', $item->id) }}"
                                                class="icon-btn btn--danger ml-1" data-toggle="tooltip" title=""
                                                data-original-title="Delete">
                                                <i class="las la-trash"></i>
                                            </a>
                                        </td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                        </table>
                    </div>

                    @if ($promocode->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($promocode) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-12 mt-3">
            <div class="card b-radius--10">
                <div class="card-body">
                    <h5>Promo Code User</h5>
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('User Id')</th>
                                    <th>@lang('Promo Code')</th>
                                    <th>@lang('Join At')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($registredUser as $item)
                                    <tr>
                                        <td>
                                            {{ optional($item->betterUser)->firstname }}
                                            {{ optional($item->betterUser)->lastname }} <br>
                                            <span class="small">
                                                <a
                                                    href="{{ route('admin.users.detail', optional($item->betterUser)->id) }}"><span>@</span>{{ optional($item->betterUser)->username }}</a>
                                            </span>
                                        </td>
                                        <td>{{ optional($item->promo)->promo_code }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->created_at)->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                        </table>
                    </div>

                    @if ($registredUser->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($registredUser) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
