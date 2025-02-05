@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $cat)
                                    <tr>
                                        <td>{{ @$cat->name }}</td>

                                        

                                        <td>
                                            <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.outcomes.declare.upcoming.category.result', ['name' => $cat->league, 'type'=>'home']) }}">
                                                <i class="las la-clipboard-list"></i> Result
                                            </a>
                                            @if(!$cat->esports)
                                            <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.outcomes.declare.upcoming.category.result', ['name' => $cat->league, 'type'=>'d-1']) }}">
                                                <i class="las la-clipboard-list"></i> Day
                                            </a>
                                            @endif
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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

   
@endsection
