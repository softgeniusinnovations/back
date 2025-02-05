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
                <form action="{{ route('admin.event.store') }}" method="POST" enctype="multipart/form-data">
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
                                <label class="form-label">@lang('Sub Title')</label>
                                <input class="form-control form--control" name="sub_title" type="text" value="{{ old('sub_title') }}" required>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Bonus')</label>
                                <input type="number" min="0" class="form-control form--control" name="bonus_percentage" required value="{{ old('bonus_percentage') }}" >
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Bonuses Type')</label>
                                <div class="form--select">
                                    <select class="form-select" name="type" required>
                                        <option value="permanent" {{ old('status') == 'permanent' ? 'selected' : '' }}>@lang('Permanent bonuses')</option>
                                        <option value="offers" {{ old('status') == 'offers' ? 'selected' : '' }}>@lang('Offers')</option>
                                    </select>
                                </div>
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
                                <label class="form-label">@lang('Description')</label>
                                <textarea class="form-control form--control nicEdit" name="details" rows="10"></textarea>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Start Date')</label>
                                <input type="date" class="form-control form--control" name="start_date"  required value="{{ old('start_date') }}" >
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('End Date')</label>
                                <input type="date" class="form-control form--control" name="end_date" required value="{{ old('end_date') }}" >
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Status')</label>
                                <div class="form--select">
                                    <select class="form-select" name="status" required>
                                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>@lang('Active')</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>@lang('Inactive')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Is Featured')</label>
                                <div class="form--select">
                                    <select class="form-select" name="featured" required>
                                        <option value="1" {{ old('featured') == '1' ? 'selected' : '' }}>@lang('Yes')</option>
                                        <option value="0" {{ old('featured') == '0' ? 'selected' : '' }}>@lang('No')</option>
                                    </select>
                                </div>
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
