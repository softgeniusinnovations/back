@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12 mb-2 text-end">
            {{-- <a href="{{route('admin.goal.league.import')}}" class="btn btn-primary">Import Sub Categories</a> --}}
            <a href="{{route('admin.goal.team.image.upload.page')}}" class="btn btn-primary">Team Image Import</a>
        </div>
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('#')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('League')</th>
                                    <th>@lang('Game')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Last Corn')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td><em class="fw-bold">{{ $category->id }}</em></td>
                                        <td><em class="fw-bold">{{ $category->name }}</em></td>
                                        <td><em class="fw-bold">{{ $category->league }}</em></td>
                                        <td><em class="fw-bold">{{ $category->game }}</em></td>
                                        <td><em class="fw-bold">{{ $category->status == 1 ? 'Active' : 'Inactive' }}</em></td>
                                        <td>{{$category->last_cron ? \Carbon\Carbon::parse($category->last_cron)->diffForHumans() : '---'}}</td>
                                        <td>
                                            <div class="button--group">
                                                <a class="btn btn-sm btn-outline--info" href="{{ route('admin.goal.game.import', $category->id) }}" title="Game Import">
                                                    <i class="la la-download"></i>
                                                </a>
                                                {{-- <a class="btn btn-sm btn-outline--success" href="{{ route('admin.goal.category.teams', $category->id) }}" title="Team List">
                                                    <i class="la la-list"></i>
                                                </a> --}}
                                                <a class="btn btn-sm btn-outline--success" href="{{ route('admin.goal.sub.category', $category->id) }}" title="Sub categories">
                                                    <i class="la la-question-circle"></i>({{$category->leagues_count}})
                                                </a>
                                                @if($category->image)
                                                <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.goal.game.teams.logo', $category->id) }}" title="Team Logo Import">
                                                    <i class="la la-photo"></i>
                                                </a>
                                                @endif
                                                
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

