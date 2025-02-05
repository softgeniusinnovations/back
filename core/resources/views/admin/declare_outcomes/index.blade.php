@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    {{--                    @dd($questions)--}}

                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                            <tr>
                                <th>@lang('Market id')</th>
                                <th>@lang('Market')</th>
                                {{--                                    <th>@lang('value')</th>--}}
                                <th>@lang('League')</th>
                                <th>@lang('Match')</th>
                                <th>@lang('Bet End Time')</th>
                                <th>@lang('Bet Placed')</th>
                                @if (request()->routeIs('admin.outcomes.declare.declared'))
                                    <th>@lang('Win Option')</th>
                                @endif
                                @if (request()->routeIs('admin.outcomes.declare.pending'))
                                    <th>@lang('Action')</th>
                                @endif

                            </tr>
                            </thead>

                            <tbody>
                            @forelse ($questions as $question)
                                <tr>
                                    @php
                                        $details = @$question->betDetails->first()?->details;
//                                            dd(json_decode($details, true))
                                    $decodedDetails = $details ? json_decode($details, true) : [];
                                    
                                   

                                    $title = @$question->title;

                                    preg_match('/(\d+)$/', $title, $matches);
                                    $numericNumber = $matches[1] ?? null;
                                    $titleWithoutNumber = preg_replace('/\s*\d+$/', '', $title);
                                    @endphp

                                    <td class="text-start">{{$numericNumber}}</td>
                                    <td class="text-start">
                                        {{ $titleWithoutNumber}}</td>

                                    {{--                                        <td class="text-start">{{$decodedDetails['overUnder'] ?? '' }}</td>--}}
                                    <td class="text-start">{{ __(@$question->game->league->name)}}</td>

                                    <td>
                                        <div class="d-flex align-items-center justify-content-end justify-content-lg-center gap-3">
                                            <div class="thumb">
                                                <div class="d-flex align-items-center flex-column">
                                                    <img src="{{ getImage(getFilePath('team') . '/' . @$question->game->teamOne->image, getFileSize('team')) }}"
                                                         alt="@lang('image')">
                                                    <span title="{{ @$question->game->teamOne->name }}">{{ __(@$question->game->teamOne->short_name) }}</span>
                                                </div>
                                            </div>

                                            <span>@lang('VS')</span>

                                            <div class="thumb">
                                                <div class="d-flex align-items-center flex-column">
                                                    <img src="{{ getImage(getFilePath('team') . '/' . @$question->game->teamTwo->image, getFileSize('team')) }}"
                                                         alt="@lang('image')">
                                                    <span title="{{ @$question->game->teamTwo->name }}">{{ __(@$question->game->teamTwo->short_name) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        {{ showDateTime(@$question->game->bet_end_time) }}
                                        <br>
                                        {{ diffForHumans(@$question->game->bet_end_time) }}
                                    </td>

                                    <td>
                                        <span>{{ getAmount(@$question->bet_details_count) }} </span>
                                    </td>

                                    @if (request()->routeIs('admin.outcomes.declare.declared'))
                                        <td>
                                            @if (@$question->winOption)
                                                <span class="text--success">{{ __(@$question->winOption->name) }}</span>
                                            @else
                                                <span class="text--info">@lang('Refunded')</span>
                                            @endif
                                        </td>
                                    @endif

                                    @if (request()->routeIs('admin.outcomes.declare.pending'))
                                        <td>
                                            <div class="button--group d-flex justify-content-end flex-wrap">
                                                <button class="btn btn-sm btn-outline--primary option-btn"
                                                        data-question="{{ $question->title }}"
                                                        data-options='{{ $question->options }}'
                                                        data-value="{{ ($decodedDetails['overUnder'] ?? '') . '-' . ($decodedDetails['player_name'] ?? '') }}"
                                                        type="button">
                                                    <i class="la la-info-circle"></i>@lang('Select Outcome')
                                                </button>
                                                @if($question->bet_details_status_pending_count == 0)
                                                    <button class="btn btn-sm btn-outline--success confirmationBtn"
                                                            data-question="@lang('Are you sure declared this question')?"
                                                            data-action="{{ route('admin.outcomes.declare.question', $question->id) }}"
                                                            type="button">
                                                        <i class="la la-info-circle"></i>@lang('Declare')
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline--info confirmationBtn"
                                                        data-action="{{ route('admin.outcomes.declare.refund', $question->id) }}"
                                                        data-question="@lang('Are you sure refund this question')?"
                                                        type="button">
                                                    <i class="las la-undo-alt"></i> @lang('Refund Bet')
                                                </button>
                                                <a class="btn btn-sm btn-outline--dark"
                                                   href="{{ route('admin.bet.question', $question->id) }}">
                                                    <i class="las la-clipboard-list"></i> @lang('Bets')
                                                </a>
                                            </div>
                                        </td>
                                    @endif

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


                @if ($questions->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($questions) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal" id="optionModal" role="dialog" tabindex="-1">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="result-area"></div>
                        <div class="action-area"></div>
                    </div>
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                            <tr>
                                <th>@lang('Name')</th>
                                <th>@lang('Value')</th>
                                <th>@lang('Rate')</th>
                                <th>@lang('Bet Count')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal/>



    <!-- Confirmation Modal -->
    <div id="confirmationModalComment" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage"></p>
                    <div class="form-group">
                        <label for="comments">Comments:</label>
                        <textarea id="comments" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmActionComment" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('breadcrumb-plugins')
    <x-search-form/>
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
        (function ($) {
            "use strict";
            let modal = $("#optionModal");
            let confirmationModalComment = $("#confirmationModalComment");
            let confirmActionBtn = $('#confirmActionComment');
            let selectedOption; // To keep track of the selected option

            $('.option-btn').on('click', function (e) {
                modal.find('tbody').html('')
                var question = $(this).data('question');
                var options = $(this).data('options');
                var value = $(this).data('value');

                var modalTitle = `Options for - ${question}`;
                modal.find('.modal-title').text(modalTitle);
                var tableRow = ``;

                function getActionButtons(option) {
                    if (option.button_action == 0) {
                        return `
                                <button class="btn btn-sm btn-outline--primary confirmationBtnComment" data-id="${option.id}" data-name="${option.name}" data-action="win" ${Number(option.bets_count) < 1 ? 'disabled' : ''}>
                                    <i class="las la-trophy"></i> @lang('Win')
                        </button>
                        <button class="btn btn-sm btn-outline--primary confirmationBtnComment" data-id="${option.id}" data-name="${option.name}" data-action="half_win" ${Number(option.bets_count) < 1 ? 'disabled' : ''}>
                                    <i class="las la-trophy"></i> @lang('Half Win')
                        </button>
                        <button class="btn btn-sm btn-outline--danger confirmationBtnComment" data-id="${option.id}" data-name="${option.name}" data-action="loss" ${Number(option.bets_count) < 1 ? 'disabled' : ''}>
                                    <i class="las la-sad-tear"></i> @lang('Loss')
                        </button>
                        <button class="btn btn-sm btn-outline--danger confirmationBtnComment" data-id="${option.id}" data-name="${option.name}" data-action="half_loss" ${Number(option.bets_count) < 1 ? 'disabled' : ''}>
                                    <i class="las la-sad-tear"></i> @lang('Half Loss')
                        </button>

                        <button class="btn btn-sm btn-outline--success confirmationBtnComment" data-id="${option.id}" data-name="${option.name}" data-action="refund" ${Number(option.bets_count) < 1 ? 'disabled' : ''}>
                                    <i class="las la-backward"></i> @lang('Refund')
                        </button>`;
                    }
                    if (option.button_action == 1) {
                        return `<button class="btn btn-sm btn-primary" disabled>
                                @lang('Win')
                        </button>`;
                    }
                    if (option.button_action == 4) {
                        return `<button class="btn btn-sm btn-primary" disabled>
                                @lang('Half Win')
                        </button>`;
                    }
                    if (option.button_action == 2) {
                        return `<button class="btn btn-sm btn-primary" disabled>
                                @lang('Loss')
                        </button>`;
                    }

                    if (option.button_action == 5) {
                        return `<button class="btn btn-sm btn-primary" disabled>
                                @lang('Half Loss')
                        </button>`;
                    }
                    if (option.button_action == 3) {
                        return `<button class="btn btn-sm btn-primary" disabled>
                                @lang('Refund')
                        </button>`;
                    }
                }

                $.each(options, function (index, option) {
                    tableRow += `<tr>
                                    <td data-label="@lang('Name')">${option.name}</td>
                                    <td data-label="@lang('value')">${value}</td>
                                    <td data-label="@lang('Odds')">${Math.abs(option.odds)}</td>
                                    <td data-label="@lang('Bet Count')">${option.bets_count}</td>
                                    <td data-label="@lang('Action')" class="d-flex align-items-center gap-1 justify-content-end">
                                        
                                        ${getActionButtons(option)}
                                    
                                    
                                        {{-- <button class="btn btn-sm btn-outline--primary confirmationBtn" data-action="{{ route('admin.outcomes.declare.winner', '') }}/${option.id}" data-question="@lang('Are you sure to select') <b>${option.name}</b>?">
                                            <i class="las la-trophy"></i>@lang('Select')
                                        </button> --}}
                    </td>
                </tr>`;
                });
                modal.find('tbody').append(tableRow)
                modal.modal('show')
            });


            // By default confirm
            let confirmationModal = $("#confirmationModal");

            $(document).on('click', '.confirmationBtn', function (e) {
                modal.modal('hide');
                confirmationModal.modal('show');
            });

            $(document).on('click', '#confirmationModal [data-bs-dismiss=modal]', function (e) {
                modal.modal('show');
                confirmationModal.modal('hide')
            });

            // Confirm with comments
            $(document).on('click', '.confirmationBtnComment', function (e) {
                var button = $(this);
                selectedOption = {
                    id: button.data('id'),
                    name: button.data('name'),
                    action: button.data('action')
                };
                var confirmationMessage = `Are you sure to select <b>${selectedOption.name}</b> as ${selectedOption.action}?`;

                $('#confirmationMessage').html(confirmationMessage);
                modal.modal('hide');
                confirmationModalComment.modal('show');
            });

            $(document).on('click', '#confirmationModalComment [data-bs-dismiss=modal]', function (e) {
                modal.modal('show');
                confirmationModalComment.modal('hide');
            });

            confirmActionBtn.on('click', function (e) {
                var comments = $('#comments').val();
                var actionUrl = "{{ route('admin.outcomes.declare.make.decision') }}";

                if (selectedOption.action != 'win' && comments == '') {
                    alert('Comments is required.');
                } else {
                    $.ajax({
                        url: actionUrl,
                        method: 'POST',
                        data: {
                            comments: comments,
                            id: selectedOption.id,
                            type: selectedOption.action,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('An error occurred. Please try again.');
                        }
                    });
            
                    confirmationModal.modal('hide');
                }
        
               
            });


        })(jQuery);
    </script>
@endpush
