@extends('admin.layouts.app')
@php
    $types = ['super-admin', 'agent', 'cash-agent', 'mob-agent', 'affiliator', 'support', 'report'];
@endphp
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    {{-- <form action="" method="POST" enctype="multipart/form-data"> --}}
                    <form action="{{ route('admin.affiliate.company_expenses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Company Expenses')</label>
                                    <input class="form-control form--control" name="company_expenses" type="number" min="0" value="{{ old('company_expenses') }}" required>
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
