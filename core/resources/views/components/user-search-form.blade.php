@props(['placeholder' => 'Transaction Number', 'btn' => 'btn--base'])
<form action="">
    <div class="d-flex flex-wrap gap-4">
        <div class="trans-number">
            <input class="form-control form--control" name="search" type="text" value="{{ request()->search }}" placeholder="{{ $placeholder }}">
        </div>
        <div class="trans-btn align-self-end">
            <button class="btn {{ $btn }} btn--xl"><i class="las la-filter"></i> @lang('Filter')</button>
        </div>
    </div>
</form>

