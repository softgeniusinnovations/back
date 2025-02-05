@extends('admin.layouts.app')

@section('panel')
    <div class="row gy-4">
        <div class="col-xxl-4">
            <div class="card">

                <div class="card-header d-flex justify-content-between flex-wrap">
                    <h5>@lang('Game Info')</h5>
                    <div>@php echo $game->statusBadge;@endphp</div>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center mb-3 flex-wrap gap-2">
                        <div class="team-logo">
                            <span>{{ __($game->teamOne->short_name) }}</span>
                            <img src="{{ getImage(getFilePath('team') . '/' . $game->teamOne->image, getFileSize('league')) }}" alt="image">
                        </div>
                        <span class="px-2">@lang('VS')</span>
                        <div class="team-logo">
                            <img src="{{ getImage(getFilePath('team') . '/' . $game->teamTwo->image, getFileSize('league')) }}" alt="image">
                            <span>{{ __($game->teamTwo->short_name) }}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-3 flex-wrap gap-3">
                        <div>
                            <small class="text-muted"> @lang('League')</small>
                            <h6 class="f-size-16px"> {{ $game->league->name }}</h6>
                        </div>

                        <div class="text-end">
                            <small class="text-muted"> @lang('Category')</small>
                            <h6 class="f-size-16px"> {{ $game->league->category->name }}</h6>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between flex-wrap gap-3">
                        <div>
                            <small class="text-muted"> @lang('Betting Starts From')</small>
                            <h6 class="f-size-16px"> {{ showDateTime($game->start_time, 'd M Y, h:i A') }}</h6>
                        </div>

                        <div class="text-end">
                            <small class="text-muted"> @lang('Betting Ends At')</small>
                            <h6 class="f-size-16px"> {{ showDateTime($game->end_time, 'd M Y, h:i A') }}</h6>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-xxl-8">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Markets')</th>
                                    <th>@lang('Options')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($questions as $question)
                                    <tr>
                                        <td>{{ strLimit(__($question->title), 40) }}</td>
                                        <td>{{ $question->options->count() }}</td>
                                        <td> @php echo $question->statusBadge @endphp </td>
                                        <td>
                                            <div class="button--group">
                                                <button class="btn btn-outline--primary btn-sm cuModalBtn" data-resource="{{ $question }}" data-modal_title="@lang('Edit Market')" type="button">
                                                    <i class="la la-pencil"></i>@lang('Edit')
                                                </button>

                                                <button class="btn btn-outline--info btn-sm option-btn" data-resource="{{ $question }}" data-game_upcoming="{{ !$question->result && !$game->isUpcoming }}" data-game_expired="{{ !$question->result && !$game->isExpired }}" type="button">
                                                    <i class="la la-list-ol"></i>@lang('Options')
                                                </button>

                                                @if ($question->status)
                                                    <button class="btn btn-outline--danger btn-sm confirmationBtn" data-action="{{ route('admin.question.status', $question->id) }}" data-question="@lang('Are you sure to disable this market')?">
                                                        <i class="la la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--success btn-sm confirmationBtn" data-action="{{ route('admin.question.status', $question->id) }}" data-question="@lang('Are you sure to enable this market')?">
                                                        <i class="la la-eye"></i>@lang('Enable')
                                                    </button>
                                                @endif

                                                @if ($question->locked)
                                                    <button class="btn btn-outline--success btn-sm confirmationBtn" data-action="{{ route('admin.question.locked', $question->id) }}" data-question="@lang('Are you sure to unlock this market')?">
                                                        <i class="las la-unlock"></i> @lang('Unlock')
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--dark btn-sm confirmationBtn" data-action="{{ route('admin.question.locked', $question->id) }}" data-question="@lang('Are you sure to lock this market')?">
                                                        <i class="las la-lock"></i> @lang('Lock')
                                                    </button>
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

    {{-- Create or Update Modal --}}
    <div class="modal fade" id="cuModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.question.store') }}" method="POST">
                    @csrf
                    <input name="game_id" type="hidden" value="{{ $game->id }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>@lang('Title')</label>
                                    <input class="form-control" name="title" type="text" value="{{ old('name') }}" required />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Option Modal --}}

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
                        <div class="game-area d-flex align-items-center">
                            <div class="team-logo">
                                <span>{{ __($game->teamOne->short_name) }}</span>
                                <img src="{{ getImage(getFilePath('team') . '/' . $game->teamOne->image, getFileSize('league')) }}" alt="@lang('image')">
                            </div>
                            <span class="px-2">
                                @lang('VS')
                            </span>
                            <div class="team-logo">
                                <img src="{{ getImage(getFilePath('team') . '/' . $game->teamTwo->image, getFileSize('league')) }}" alt="@lang('image')">
                                <span>{{ __($game->teamTwo->short_name) }}</span>
                            </div>
                        </div>

                        <div class="result-area"></div>

                        @if (!@$game->isExpired)
                            <div class="action-area"></div>
                        @endif
                    </div>
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Option')</th>
                                    <th>@lang('Rate')</th>
                                    <th>@lang('Bet Count')</th>
                                    <th>@lang('Status')</th>
                                    @if (!@$game->isExpired)
                                        <th>@lang('Action')</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Option --}}
    <div class="modal" id="optionStoreModal" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button class="close close-option-modal" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form id="optionStore" action="" method="POST">
                    <div class="modal-body">
                        <input name="question_id" type="hidden">
                        <div class="form-group">
                            <label>@lang('Option')</label>
                            <input class="form-control" name="name" type="text" value="{{ old('name') }}" required />
                        </div>

                        <div class="form-group mb-0">
                            <label>@lang('Rate in Decimal Odds')</label>
                            <input class="form-control" name="odds" type="number" value="{{ old('odds') }}" step="any" required />
                            <small class="text--info text--small"><i class="la la-info-circle"></i> @lang('Default odds type is decimal')</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="optionStatusModal" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button class="close close-option-modal" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form id="optionStatusForm" action="" method="POST">
                    <div class="modal-body">
                        <p class="question"></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark close-option-modal" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.game.index') }}"></x-back>
    <button class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="@lang('Add New Market')" type="button">
        <i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush

@push('style')
    <style>
        .f-size-16px {
            font-size: 14px !important;
        }

        @media(min-width:768px) {
            .button--group button {
                width: 100px;
            }
        }
    </style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/cu-modal.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            let modal = $("#optionModal");

            function optionTable(options) {
                var isExpired = "{{ $game->isExpired }}";
                var tableRow = ``;
                if (!options.length) {
                    tableRow = `<tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>`
                }
                $.each(options, function(index, option) {
                    tableRow += `<tr>
                                    <td data-label="@lang('Option')">${option.locked ? `<span class="text--danger"><i class="las la-lock"></i></span>` : ''}${option.name}</td>
                                    <td data-label="@lang('Rate')">${option.odds}</td>
                                    <td data-label="@lang('Bet Count')">${option.bets_count}</td>
                                    <td data-label="@lang('Status')">
                                    ${option.status == 1 ?
                                        `<span class="badge badge--success">@lang('Enabled')</span>` :
                                        `<span class="badge badge--danger">@lang('Disabled')</span>`
                                    }
                                    </td>
                                    ${isExpired ? `` :
                                    `<td data-label="@lang('Action')">
                                                                                                                                                    <div class="button--group option-button-group">
                                                                                                                                                        <button type="button" class="btn btn-sm btn-outline--primary edit-option" data-resource='${JSON.stringify(option)}'><i class="la la-pencil"></i>@lang('Edit')</button>
                                                                                                                                                        ${option.status == 1 ?`<button class="btn btn-sm btn-outline--danger option-status-btn" data-action="{{ route('admin.option.status', '') }}/${option.id}" data-question="@lang('Are you sure to disable this option')?"><i class="la la-eye-slash"></i>@lang('Disable')</button>` : `<button class="btn btn-sm btn-outline--success option-status-btn" data-action="{{ route('admin.option.status', '') }}/${option.id}" data-question="@lang('Are you sure to enable this option')?"><i class="la la-eye"></i>@lang('Enable')</button>`} ${option.locked == 1 ?

                                                                                                                                                        `<button class="btn btn-sm btn-outline--success option-status-btn" data-action="{{ route('admin.option.locked', '') }}/${option.id}" data-question="@lang('Are you sure to unlock this option')?">
                                                <i class="las la-unlock"></i> @lang('Unlock')
                                            </button>` : `<button class="btn btn-sm btn-outline--dark option-status-btn" data-action="{{ route('admin.option.locked', '') }}/${option.id}" data-question="@lang('Are you sure to lock this option')?">
                                                <i class="las la-lock"></i> @lang('Lock')
                                            </button>`} </div>
                                                                                                                                                </td>`}

                                </tr>`
                });
                return tableRow;
            }

            $('.option-btn').on('click', function(e) {
                modal.find('tbody').html('')
                var question = $(this).data('resource');

                var modalTitle = `Option for - ${question.title}`;
                modal.find('.modal-title').text(modalTitle);
                modal.find('tbody').html('');
                var tableRow = optionTable(question.options);
                modal.find('tbody').html(tableRow)

                var actionBtn = `<div class="button--group m-0">
                                        <button class="btn btn-sm btn-outline--primary add-option" data-question_id="${question.id}" data-modal_title="@lang('Add New Option')" type="button">
                                            <i class="las la-plus"></i>@lang('Add New')
                                        </button>
                                </div>`;
                modal.find('.action-area').html(actionBtn);

                if (question.options.length) {
                    var result = ``;
                    if (!question.result) {
                        result =
                            `<span class="text--small">@lang('Result') : </span><span class="text--small badge badge--warning">@lang('Undeclared')</span>`
                    } else if (question.result && question.lose) {
                        result =
                            `<span class="text--small">@lang('Result') : </span><span class="text--small badge badge--success">@lang('Lose')</span>`
                    } else if (question.result && question.refund) {
                        result =
                            `<span class="text--small">@lang('Result') : </span><span class="text--small badge badge--primary">@lang('Refunded')</span>`
                    } else {
                        result =
                            `<span class="text--small">@lang('Result') : </span><span class="text--small badge badge--success">@lang('Declared')</span>`
                    }
                }
                modal.find('.result-area').html(result);
                modal.modal('show')
            });

            let optionStoreModal = $("#optionStoreModal");
            $(document).on('click', '.add-option', function(e) {
                optionStoreModal.find('.modal-title').text('Add New Option');
                optionStoreModal.find('form').attr('action', `{{ route('admin.option.store', '') }}`)
                optionStoreModal.find('[name=question_id]').val($(this).data('question_id'));
                optionStoreModal.modal('show');
                modal.modal('hide');
            });

            $(document).on('click', '.edit-option', function() {
                var data = $(this).data('resource');
                optionStoreModal.find('.modal-title').text('Update Option');
                optionStoreModal.find('form').attr('action',
                    `{{ route('admin.option.store', '') }}/${data.id}`)
                optionStoreModal.find('[name=question_id]').val(data.question_id);
                optionStoreModal.find('[name=name]').val(data.name);
                optionStoreModal.find('[name=odds]').val(Math.abs(data.odds));
                optionStoreModal.modal('show');
                modal.modal('hide');
            })

            optionStoreModal.on('hidden.bs.modal', function() {
                $('#optionStoreModal form')[0].reset();
            });

            $(document).on('click', '.close-option-modal', function() {
                modal.modal('show')
            });

            $(document).on('submit', '#optionStore', function(event) {
                event.preventDefault();
                let data = {
                    _token: '{{ csrf_token() }}',
                    name: $(this).find('[name=name]').val(),
                    odds: $(this).find('[name=odds]').val(),
                    question_id: $(this).find('[name=question_id]').val()
                };
                var action = $(this).attr('action')
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: data,
                    success: function(response) {
                        if (response.error) {
                            notify('error', response.error);
                        } else {
                            modal.find('tbody').html('');
                            var tableRow = optionTable(response.options);
                            modal.find('tbody').html(tableRow)
                            optionStoreModal.modal('hide')
                            modal.modal('show')
                            notify('success', response.success);
                        }
                    }
                });
            });

            let optionStatusModal = $("#optionStatusModal");

            $(document).on('click', '.option-status-btn', function() {
                modal.modal('hide');
                let data = $(this).data();
                optionStatusModal.find('.question').text(`${data.question}`);
                optionStatusModal.find('form').attr('action', `${data.action}`);
                optionStatusModal.modal('show');
            })
            $(document).on('submit', '#optionStatusForm', function(event) {
                event.preventDefault();
                let data = {
                    _token: '{{ csrf_token() }}',
                };
                var action = $(this).attr('action')
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: data,
                    success: function(response) {
                        if (response.error) {
                            notify('error', response.error);
                        } else {
                            modal.find('tbody').html('');
                            var tableRow = optionTable(response.options);
                            modal.find('tbody').html(tableRow)
                            optionStatusModal.modal('hide')
                            modal.modal('show')
                            notify('success', response.success);
                        }
                    }
                });
            })
        })(jQuery)
    </script>
@endpush
