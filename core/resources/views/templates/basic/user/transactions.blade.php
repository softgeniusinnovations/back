@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="show-filter mb-3 text-end">
        <button class="btn btn--base showFilterBtn btn-sm" type="button"><i class="las la-filter"></i> @lang('Filter')</button>
    </div>
    <div class="responsive-filter-card mb-3">
        <form action="">
            <div class="d-flex flex-wrap gap-4">
                <div class="flex-grow-1">
                    <label class="form-label">@lang('Transaction Number')</label>
                    <input class="form-control form--control" name="search" type="text" value="{{ request()->search }}">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label">@lang('Type')</label>
                    <div class="form--select">
                        <select class="form-select" name="trx_type">
                            <option value="">@lang('All')</option>
                            <option value="+" @selected(request()->trx_type == '+')>@lang('Plus')</option>
                            <option value="-" @selected(request()->trx_type == '-')>@lang('Minus')</option>
                        </select>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label">@lang('Remark')</label>
                    <div class="form--select">
                        <select class="form-select" name="remark">
                            <option value="">@lang('Any')</option>
                            @foreach ($remarks as $remark)
                                <option value="{{ $remark->remark }}" @selected(request()->remark == $remark->remark)>{{ __(keyToTitle($remark->remark)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex-grow-1 align-self-end">
                    <button class="btn btn--base btn--xl w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                </div>
            </div>
        </form>
    </div>
    @include($activeTemplate . 'partials.transaction_table')
@endsection
