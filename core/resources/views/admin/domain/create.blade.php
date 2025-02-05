@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
      <a href="{{ route('admin.domain.list') }}" class="btn btn-sm btn-primary my-2">List Page</a>
            <div class="card b-radius--10">
                <div class="card-body p-3">
                    <form action="{{ route('admin.domain.store') }}" method="post" enctype="multipart/form-data">
                        @method('POST')
                        @csrf
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Domain name')</label>
                                    <input class="form-control form--control mb-3" name="name" type="text"
                                        value="{{ old('name') }}" required placeholder="manualfunds.com">
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
                                        value="{{ old('title') }}" required>
                                </div>
                            </div> 
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Sub title')</label>
                                    <input class="form-control form--control mb-3" name="subtitle" type="text"
                                        value="{{ old('subtitle') }}" required>
                           </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Logo')</label>
                                    <input type="file" name="file" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-12 col-sm-12 text-right">
                                <button class="btn btn-primary">Submit</button>
                            </div>

                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

