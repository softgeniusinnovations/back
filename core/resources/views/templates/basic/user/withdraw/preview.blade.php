@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="card custom--card">
        <div class="card-header">
            <h5 class="card-title">@lang('Withdraw Via')
                {{ @$withdraw->method->name ? @$withdraw->method->name : @$withdraw->transectionProviders->name ?? 'Cash agent' }}.
                We have to pay {{ $withdraw->phone }}
            </h5>
        </div>
        <form action="{{ route('user.withdraw.submit') }}" method="post" enctype="multipart/form-data">
            <div class="card-body row g-3">
                @csrf
                <div class="mb-2">
                    @php
                        echo ' Amount ' . @$withdraw->amount . userCurrency();
                    @endphp
                </div>
                {{-- <x-viser-form identifier="id" identifierValue="{{ @$withdraw->method->form_id }}" /> --}}

                {{-- @if (auth()->user()->ts)
                    <div class="form-group">
                        <label class="form-label">@lang('Google Authenticator Code')</label>
                        <input class="form-control form--control" name="authenticator_code" type="text" required>
                    </div>
                @endif --}}

                <div class="form-group mt-4">
                    <button class="btn btn--xl btn--base w-100" type="submit">@lang('Submit')</button>
                </div>
            </div>
        </form>
    </div>
@endsection
