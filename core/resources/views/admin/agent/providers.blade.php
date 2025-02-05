@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        @can('transaction-providers-create')
            <div class="col-lg-12">
                <div class="card b-radius--10">
                    <div class="card-body p-3">
                        <form action="{{ route('admin.agent.transaction.providers.create') }}" method="post"
                            enctype="multipart/form-data">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Name')</label>
                                        <input class="form-control form--control mb-3" name="name" type="text"
                                            value="{{ old('name') }}" required placeholder="Provider name">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Country')</label>
                                        <select name="country_code" required class="form-control">
                                            <option value="">---Select country---</option>
                                            @foreach ($countries as $key => $country)
                                                <option value="{{ $key }}"
                                                    {{ old('country_code') == $key ? 'selected' : '' }}>{{ $country->country }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Logo')</label>
                                        <input type="file" name="file" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Minimum deposit')</label>
                                        <input type="number" name="dep_min_am" class="form-control" placeholder="0"
                                            min="1" value={{ old('dep_min_am') }} required>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Maximum deposit')</label>
                                        <input type="number" name="dep_max_am" class="form-control" placeholder="0"
                                            min="1" value={{ old('dep_max_am') }} required>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Minimum withdraw')</label>
                                        <input type="number" name="with_min_am" class="form-control" placeholder="0"
                                            min="1" value={{ old('with_min_am') }} required>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Maximum withdraw')</label>
                                        <input type="number" name="with_max_am" class="form-control" placeholder="0"
                                            min="1" value={{ old('with_max_am') }} required>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Status')</label>
                                        <select name="status" required class="form-control">
                                            <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Disabled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Deposit note')</label>
                                        <textarea name="note_dep" value={{ old('note_dep') }} id="" cols="30" rows="2"
                                            class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Withdraw note')</label>
                                        <textarea name="note_with" value={{ old('note_with') }} id="" cols="30" rows="2"
                                            class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <button class="btn btn-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('transaction-providers-view')
            <div class="col-lg-12 mt-2">
                <div class="card b-radius--10">
                    <div class="card-body p-0">
                        <div class="table-responsive--md table-responsive">
                            <table class="table--light style--two table">
                                <thead>
                                    <tr>
                                        <th>@lang('Provider Name')</th>
                                        <th>@lang('Logo')</th>
                                        <th>@lang('Deposit')</th>
                                        <th>@lang('Withdraw')</th>
                                        <th>@lang('Country')</th>
                                        <th>@lang('Status')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($providers as $provider)
                                        <tr>
                                            <td>{{ $provider->name }}</td>
                                            <td>
                                                @if ($provider->file)
                                                    <img src="{{ asset('/core/public/storage/providers/' . $provider->file) }}"
                                                        alt="Logo" width="50">
                                                @else
                                                    <img src="https://via.placeholder.com/150x150" alt="Logo"
                                                        width="50">
                                                @endif
                                            </td>
                                            <td><span>Min: {{ $provider->with_min_am . $provider->currency }}</span> <br>
                                                <span>Max: {{ $provider->with_max_am . $provider->currency }}</span>
                                            </td>
                                            <td><span>Min: {{ $provider->dep_min_am . $provider->currency }}</span> <br>
                                                <span>Max: {{ $provider->dep_max_am . $provider->currency }}</span>
                                            </td>
                                            <td>{{ $provider->country_code }}</td>
                                            <td>{{ $provider->status == 1 ? 'Active' : 'Disabled' }}</td>
                                            <td>
                                                @can('transaction-providers-edit')
                                                    <a href="{{ route('admin.agent.transaction.providers.edit', $provider->id) }}"
                                                        class="btn btn-sm btn-outline--primary bet-detail "><i
                                                            class="fa fa-pencil"></i>
                                                    </a>
                                                @endcan
                                                @can('transaction-providers-delete')
                                                    <form style="display:inline-block"
                                                        action="{{ route('admin.agent.transaction.providers.delete', $provider->id) }}"
                                                        method="post">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline--danger"><i
                                                                class="fa fa-trash"></i></button>
                                                    </form>
                                                @endcan
                                                @can('transaction-providers-edit')
                                                    <a href="{{ route('admin.agent.transaction.providers.status', $provider->id) }}"
                                                        class="btn btn-sm btn-outline--{{ $provider->status == 1 ? 'danger' : 'success' }} bet-detail ">{{ $provider->status == 1 ? 'Disable' : 'Active' }}
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-muted text-center" colspan="100%">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($providers->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($providers) }}
                        </div>
                    @endif
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
