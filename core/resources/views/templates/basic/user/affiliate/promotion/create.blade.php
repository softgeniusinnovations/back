@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="card custom--card">
    <h5 class="card-header">
        <i class="las la-ticket-alt"></i>
        @lang('Create Promotion')
    </h5>

    <div class="card-body">
        <form action="{{ route('user.promotions.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">@lang('Title')</label>
                        <input class="form-control form--control" name="title" type="text" value="{{ old('title') }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">@lang('Promo Code')</label>
                        <input class="form-control form--control" id="promo_code" name="promo_code" type="text" value="{{ old('promo_code') }}" required readonly>
                    </div>
                </div>

                <div class="col-6">
                    <label class="form-label mb-0">@lang('Attachments')</label>
                    <input class="form-control form--control" name="attachments" type="file" accept=".jpg, .jpeg, .png">
                    <div class="list mt-3" id="fileUploadsContainer"></div>
                    <code class="xsm-text text-muted"><i class="fas fa-info-circle"></i> @lang('Allowed File Extensions'):
                        .@lang('jpg'), .@lang('jpeg'), .@lang('png')</code>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">@lang('Status')</label>
                        <div class="form--select">
                            <select class="form-select" name="status" {{ old('status') }} required>
                                <option value="1">@lang('Active')</option>
                                <option value="0">@lang('Inactive')</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">@lang('Start Date')</label>
                        <input class="form-control form--control" name="start_date" type="date" value="{{ old('start_date') }}" required>
            </div>
    </div> --}}
    {{-- <div class="col-md-6">
        <div class="form-group">
            <label class="form-label">@lang('Discount (%)')</label>
            <input class="form-control form--control" name="promo_percentage" type="number" min="0" max="100" value="{{ old('promo_percentage') }}" required>
        </div>
    </div> --}}

    {{-- <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">@lang('End Date')</label>
                        <input class="form-control form--control" name="end_date" type="date" value="{{ old('end_date') }}" required>
</div>
</div> --}}

<div class="col-12">
    <div class="form-group">
        <label class="form-label">@lang('Description')</label>
        <textarea class="form-control form--control" name="details" rows="5">{{ old('details') }}</textarea>
    </div>
</div>

{{-- <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">@lang('Link')</label>
                        <input class="form-control form--control" name="learn_more_link" type="text" value="{{ old('learn_more_link') }}">
</div>
</div> --}}

</div>

<div class="text-end">
    <button class="btn btn--base mt-3" type="submit">@lang('Submit')</button>
</div>
</form>
</div>
</div>
@endsection
@push('script')

<script>
    //create a function which is genarate random promo code
    $(document).ready(function() {
        var text = "";
        // var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for (var i = 0; i < 6; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        $("#promo_code").val(text);
    });

</script>

@endpush
