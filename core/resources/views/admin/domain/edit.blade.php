@extends('admin.layouts.app')

@section('panel')
    <div class="row">
       <div class="col-lg-12">
           <a href="{{ route('admin.domain.list') }}" class="btn btn-sm btn-primary my-2">List Page</a>
            <div class="card b-radius--10">
                <div class="card-body p-3">
                    <form action="{{ route('admin.domain.update', $domain->id) }}" method="post" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Domain name')</label>
                                    <input class="form-control form--control mb-3" name="name" type="text"
                                        value="{{ old('name', $domain->domain_name) }}" required placeholder="manualfunds.com">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Page')</label>
                                    <select name="page" class="form-control form--control" required>
                                       <option value="login">Login Page</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Title')</label>
                                    <input class="form-control form--control mb-3" name="title" type="text"
                                        value="{{ old('title', $contents["login"]['title'] ?? '') }}" required>
                                </div>
                            </div> 
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Sub title')</label>
                                    <input class="form-control form--control mb-3" name="subtitle" type="text"
                                        value="{{ old('subtitle', $contents["login"]['subtitle'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Logo')</label>
                                    <input type="file" name="file" class="form-control">
                                    <small class="text-muted">Leave blank to keep the existing logo.</small>
                                    @if($domain->logo)
                                        <div class="mt-2">
                                            <img src="{{ asset('/core/public/storage/' . $domain->logo)}}" alt="Logo" style="max-width: 100px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Status')</label>
                                    <select name="status" class="form-control form--control" required>
                                       <option value="1" {{ $domain->status == 1 ? 'selected' : '' }}>Active</option>
                                       <option value="0" {{ $domain->status == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                 </div>
                            </div>

                            <div class="col-md-12 col-sm-12 text-right">
                                <button class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
