@extends('admin.layouts.app')

@section('panel')
    <div class="card">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.game.import') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <h3 class="mb-1">@lang('How to import?')</h3>

                    <div class="alert-info p-3">
                        <div class="d-flex gap-2">
                            <span class="fw-bold">@lang('Step') @lang('01'):</span>
                            <a href="{{ asset('assets/files/game_template.csv') }}" download>@lang('Download')</a> @lang('template file')
                        </div>

                        <div class="d-flex gap-2">
                            <span class="fw-bold">@lang('Step') @lang('02'):</span>
                            <a href="{{ route('admin.game.download.teams') }}">@lang('Download')</a> @lang('teams')
                        </div>

                        <div class="d-flex gap-2"><span class="fw-bold">@lang('Step') @lang('03'):</span> <a href="{{ route('admin.game.download.leagues') }}">@lang('Download')</a> @lang('leagues')</div>
                    </div>

                    <div class="form-group mt-3">
                        <label>@lang('Upload the file')</label>
                        <input type="file" class="form-control" name="file" id="">
                    </div>

                    <button type="submit" class="btn btn--primary w-100">@lang('Submit')</button>
                </form>
            </div>
        </div>
    </div>
@endsection
