@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="my-2">
                    <form action={{route('admin.event.promo.banners')}} method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-9">
                                <input type="file" name="banners" class="form-control" />
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary mt-1">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="table-responsive--md table-responsive">
                    <table class="table--light style--two table">
                        <thead>
                            <tr>
                                <th>@lang('#')</th>
                                <th>@lang('Title')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Created Time')</th>
                                <th>@lang('Update Time')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $i = 0;
                            @endphp
                            @forelse ($events as $item)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td><img src={{ asset('/assets/promo_banners/'. $item->image) }} width="150" height="25" /></td>
                                <td>
                                    @if ($item->status == 1)
                                    <span class="badge badge--success">@lang('Active')</span>
                                    @elseif($item->status == 0)
                                    <span class="badge badge--warning">@lang('Inactive')</span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }} <br>
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('h:i:s A') }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->updated_at)->format('d M Y') }} <br>
                                    {{ \Carbon\Carbon::parse($item->updated_at)->format('h:i:s A') }}
                                </td>
                                <td>
                                    <div class="button--group">
                                        <form action="{{ route('admin.event.promo.delete', $item->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger deleteBtn">
                                                <i class="fa fa-trash"></i> @lang('Delete')
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="100%" class="text-center">@lang('No Data Available')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($events->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($events) }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
