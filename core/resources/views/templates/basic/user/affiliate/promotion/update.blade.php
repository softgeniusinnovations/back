@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="card custom--card">
    <h5 class="card-header">
        <i class="las la-ticket-alt"></i>
        @lang('Update Promos')
    </h5>

    <div class="card-body">
        <form action="{{ route('user.promotions.update', $promo->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('put')
            <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Title')</label>
                            <input class="form-control form--control" name="title" type="text" value="{{ $promo->title }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Promo Code')</label>
                            <input class="form-control form--control" name="promo_code" type="text" value="{{ $promo->promo_code }}">
                        </div>
                    </div>

                <div class="col-md-6">
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
                                <option value="1" {{ $promo->status == 1 ? 'selected' : '' }}>@lang('Active')</option>
                                <option value="0" {{ $promo->status == 0 ? 'selected' : '' }}>@lang('Inactive')</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">@lang('Description')</label>
                        <textarea class="form-control form--control" name="details" rows="5" required>{{ $promo->details }}</textarea>
                    </div>
                </div>

            </div>

            <div class="text-end">
                <button class="btn btn--base mt-3" type="submit">@lang('Update')</button>
            </div>
        </form>
    </div>
</div>
@endsection
