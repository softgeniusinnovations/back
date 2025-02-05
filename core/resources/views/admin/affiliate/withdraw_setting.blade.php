@extends('admin.layouts.app')
@php
    $types = ['super-admin', 'agent', 'cash-agent', 'mob-agent', 'affiliator', 'support', 'report'];
@endphp
@section('panel')
    @if($existingRecord && $existingRecord->withdraw_date == old('withdraw_date', $existingRecord->withdraw_date ?? ''))

        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning!</strong> Affiliate withdraw Balance Activated only on {{$existingRecord->withdraw_date}}.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>



    @endif
    @if($existingRecord && $existingRecord->can_withdraw_after == old('can_withdraw_after', $existingRecord->can_withdraw_after ?? ''))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning!</strong> Affiliate user can withdraw after Balance is greater than ${{$existingRecord->can_withdraw_after}}.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row mb-4">

        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">

                    <form action="{{ route('admin.affiliate.withdraw.setting.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $existingRecord->id ?? '' }}">
                        <div class="row">

                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Date')</label>
                                    <select name="withdraw_date" class="form-control form--control" required>
                                        <option value="">Select a Day</option>
                                        @foreach ($sevenDays as $day)
                                            <option value="{{ $day }}"
                                                    @if (old('withdraw_date', $existingRecord->withdraw_date ?? '') == $day) selected @endif>
                                                {{ $day }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Can Withdraw After')</label>
                                    <input type="number" name="can_withdraw_after" class="form-control form--control"
                                           value="{{ old('can_withdraw_after', $existingRecord->can_withdraw_after ?? '') }}" required>
                                </div>
                            </div>

                        </div>

                        <div class="text-end">
                            <button class="btn btn--primary mt-3" type="submit">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
