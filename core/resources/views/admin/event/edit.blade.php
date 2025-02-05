@extends('admin.layouts.app')
@php
$types = ['super-admin', 'agent', 'cash-agent', 'mob-agent', 'affiliator', 'support', 'report'];
@endphp
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body">
                <form action="{{ route('admin.event.update' , $news->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Title')</label>
                                <input class="form-control form--control" name="title" type="text" value="{{ $news->title }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Sub-Title')</label>
                                <input class="form-control form--control" name="sub_title" type="text" value="{{ $news->sub_title }}" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="col-md-12">
                                <label class="form-label mb-0">@lang('Attachments')</label>
                                <input class="form-control form--control" name="attachments" type="file" accept=".jpg, .jpeg, .png">
                                <div class="list mt-3" id="fileUploadsContainer"></div>
                                <code class="xsm-text text-muted"><i class="fas fa-info-circle"></i> @lang('Allowed File Extensions'):
                                    .@lang('jpg'), .@lang('jpeg'), .@lang('png')</code>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Bonus')</label>
                                <input type="number" min="0" max="100" class="form-control form--control" name="bonus_percentage" required value="{{ $news->bonus_percentage }}">
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">@lang('Description')</label>
                                <textarea class="form-control form--control nicEdit" name="details" rows="10" required>{{ $news->description }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($news->image != null)
                                <img src="{{ asset('assets/news/' . $news->image)}}" alt="@lang('image')" class="h-50 img-fluid">
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Start Date')</label>
                                <input type="date" class="form-control form--control" name="start_date" required value="{{ $news->start_date }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('End Date')</label>
                                <input type="date" class="form-control form--control" name="end_date" required value="{{ $news->end_date }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Status')</label>
                                <div class="form--select">
                                    <select class="form-select" name="status" required>
                                        <option value="1" {{ $news->status == '1' ? 'selected' : '' }}>@lang('Active')</option>
                                        <option value="0" {{ $news->status == '0' ? 'selected' : '' }}>@lang('Inactive')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="form-label">@lang('Is Featured')</label>
                                <div class="form--select">
                                    <select class="form-select" name="featured" required>
                                        <option value="1" {{ $news->featured == '1' ? 'selected' : '' }}>@lang('Yes')</option>
                                        <option value="0" {{ $news->featured == '0' ? 'selected' : '' }}>@lang('No')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <a class="btn btn--secondary mt-3" href="{{ route("admin.event.list") }}">@lang('Back')</a>
                        <button class="btn btn--primary mt-3" type="submit">@lang('Update')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
