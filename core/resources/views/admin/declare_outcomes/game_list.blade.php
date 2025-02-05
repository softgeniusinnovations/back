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
                                    <th>@lang('Game')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Team')</th>
                                    <th>@lang('League')</th>
                                    <th>@lang('Bet Start')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($games as $game)
                                    <tr>
                                        <td class="text-start">{{ $games->firstItem() + $loop->index }}. {{ __(@$game->slug) }}</td>
                                        <td>{{ @$game->category }}</td>

                                        <td>
                                            <div class="d-flex align-items-center justify-content-end justify-content-lg-center gap-3">
                                                <div class="thumb">
                                                    <div class="d-flex align-items-center flex-column">
                                                        <img src="{{ getImage(getFilePath('team') . '/' . @$game->teamOne->image, getFileSize('team')) }}" alt="@lang('image')">
                                                        <span title="{{ @$game->teamOne->name }}">{{ __(@$game->teamOne->short_name) }}</span>
                                                    </div>
                                                </div>

                                                <span>@lang('VS')</span>

                                                <div class="thumb">
                                                    <div class="d-flex align-items-center flex-column">
                                                        <img src="{{ getImage(getFilePath('team') . '/' . @$game->teamTwo->image, getFileSize('team')) }}" alt="@lang('image')">
                                                        <span title="{{ @$game->teamTwo->name }}">{{ __(@$game->teamTwo->short_name) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ @$game->league->name }}</td>
                                        <td>
                                            {{ showDateTime(@$game->bet_start_time) }}
                                            <br>
                                            {{ diffForHumans(@$game->bet_start_time) }}
                                        </td>

                                        <td>
                                            @if($game->game_end == 0 && $game->questions_count == 0)
                                            <button class="btn btn-sm btn-outline--success confirmationBtn" data-question="@lang('Are you sure closed the game')?" data-action="{{ route('admin.outcomes.declare.game.end', $game->id) }}" type="button">
                                                <i class="la la-info-circle"></i>@lang('Game End')
                                            </button>
                                            @endif
                                            <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.outcomes.declare.pending', $game->id) }}">
                                                <i class="las la-clipboard-list"></i> @lang('Bookmakers')({{$game->questions_count}})
                                            </a>

                                            @if($type == 'Pending')
                                            <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.outcomes.declare.match', $game->id) }}">
                                                    <i class="las la-clipboard-list"></i> @lang('Results')
                                                </a>
                                            @else
                                            <a class="btn btn-sm btn-outline--dark" href="{{ route('admin.outcomes.declare.upcoming.settlement', $game->id) }}">
                                                    <i class="las la-clipboard-list"></i> @lang('Results')
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

                @if ($games->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($games) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

   <x-confirmation-modal />
   
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
@endpush

@push('style')
    <style>
        .thumb img {
            width: 30px;
            height: 30px;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            
            // By default confirm
            let confirmationModal = $("#confirmationModal");

            $(document).on('click', '.confirmationBtn', function(e) {
                modal.modal('hide');
                confirmationModal.modal('show');
            });

            $(document).on('click', '#confirmationModal [data-bs-dismiss=modal]', function(e) {
                modal.modal('show');
                confirmationModal.modal('hide')
            });
           
        })(jQuery);
    </script>
@endpush
