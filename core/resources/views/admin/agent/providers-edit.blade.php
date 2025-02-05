@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('transaction-providers-edit')
            <div class="col-lg-12">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.agent.transaction.providers.update', $provider->id) }}" method="post"
                            enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Name')</label>
                                        <input class="form-control form--control mb-3" name="name" type="text"
                                            value="{{ $provider->name }}" required placeholder="Provider name">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Country')</label>
                                        <select name="country_code" required class="form-control">
                                            <option value="">---Select country---</option>
                                            @foreach ($countries as $key => $country)
                                                <option value="{{ $key }}"
                                                    {{ $provider->country_code == $key ? 'selected' : '' }}>
                                                    {{ $country->country }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Logo')</label>
                                        <input type="file" name="file" class="form-control"> <br>
                                        @if ($provider->file)
                                            <img src="{{ asset('/core/public/storage/providers/' . $provider->file) }}" alt="Logo"
                                                width="50">
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Minimum deposit')</label>
                                        <input type="number" name="dep_min_am" class="form-control" placeholder="0"
                                            min="1" value={{ $provider->dep_min_am }} required>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Maximum deposit')</label>
                                        <input type="number" name="dep_max_am" class="form-control" placeholder="0"
                                            min="1" value={{ $provider->dep_max_am }} required>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Minimum withdraw')</label>
                                        <input type="number" name="with_min_am" class="form-control" placeholder="0"
                                            min="1" value={{ $provider->with_min_am }} required>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Maximum withdraw')</label>
                                        <input type="number" name="with_max_am" class="form-control" placeholder="0"
                                            min="1" value={{ $provider->with_max_am }} required>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Status')</label>
                                        <select name="status" required class="form-control">
                                            <option value="1" {{ $provider->status == 1 ? 'selected' : '' }}>Active
                                            </option>
                                            <option value="0" {{ $provider->status == 0 ? 'selected' : '' }}>Disabled
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Deposit note')</label>
                                        <textarea name="note_dep" id="" cols="30" rows="2" class="form-control">{{ $provider->note_dep }}</textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Withdraw note')</label>
                                        <textarea name="note_with" id="" cols="30" rows="2" class="form-control">{{ $provider->note_with }}</textarea>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <button class="btn btn-primary">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {

        })(jQuery);
    </script>
@endpush
