@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="card custom--card">
    <h5 class="card-header">
        <i class="las la-ticket-alt"></i>
        @lang('Create News')
    </h5>

    <div class="card-body">
        <form action="{{ route('user.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label">@lang('Title')</label>
                        <input class="form-control form--control" name="title" type="text" value="{{ old('title') }}" required>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label mb-0">@lang('Attachments')</label>
                    <input class="form-control form--control" name="attachments" type="file" accept=".jpg, .jpeg, .png">
                    <div class="list mt-3" id="fileUploadsContainer"></div>
                    <code class="xsm-text text-muted"><i class="fas fa-info-circle"></i> @lang('Allowed File Extensions'):
                        .@lang('jpg'), .@lang('jpeg'), .@lang('png')</code>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">@lang('News')</label>
                        <textarea class="form-control form--control" name="details" rows="5" required>{{ old('details') }}</textarea>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">@lang('Start Date')</label>
                        <input type="date" class="form-control form--control" name="start_date"  required {{ old('start_date') }} >
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">@lang('End Date')</label>
                        <input type="date" class="form-control form--control" name="end_date" required {{ old('end_date') }} >
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">@lang('Bonus')</label>
                        <input type="number" min="0" max="100" class="form-control form--control" name="bonus_percentage" required {{ old('bonus_percentage') }} >
                    </div>
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
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-label">@lang('Is Featured')</label>
                        <div class="form--select">
                            <select class="form-select" name="featured" {{ old('featured') }} required>
                                <option value="1">@lang('Yes')</option>
                                <option value="0">@lang('No')</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button class="btn btn--base mt-3" type="submit">@lang('Submit')</button>
            </div>
        </form>
    </div>
</div>
@endsection
