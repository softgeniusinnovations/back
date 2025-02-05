@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Image')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td><em class="fw-bold">{{ $category->name }}</em></td>
                                        <td>
                                            @if($category->image)
                                            <img src="data:image/jpeg;base64, {{ $category->image }}" > 
                                            @else 
                                            <img src="https://placehold.co/50x30" width="50" height="30" >
                                            @endif
                                        </td>
                                        <td><em class="fw-bold">{{ $category->status == 1 ? 'Active' : 'Inactive' }}</em></td>
                                        <td>
                                            <div class="button--group">
                                                
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>

                @if ($categories->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($categories) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-confirmation-modal />
    @endsection

