@extends($activeTemplate . 'layouts.' . $layout)
@section($layout)
    @if ($layout == 'frontend')
        <div class="container">
            <div class="section">
            @else
                <div class="col-12">
    @endif
    <div class="card custom--card">
        <div class="card-header d-flex flex-warp justify-content-between align-items-center">
            <h5 class="m-0">
                @php
                    echo $myTicket->statusBadge;
                @endphp
                [@lang('Ticket')#{{ $myTicket->ticket }}] {{ $myTicket->subject }} <br /> <br />
                {{ @$myTicket->trx_no ? 'Transaction ID :' . @$myTicket->trx_no : '' }} <br />
                {{ @$myTicket->trx_date ? 'Transaction Date :' . @$myTicket->trx_date : '' }}
                {{ @$myTicket->bets->bet_number ? 'Bet: ' . @$myTicket->bets->bet_number : '' }}
                {{ @$myTicket->bets->stake_amount ? 'Amount: ' . @$myTicket->bets->stake_amount : '' }} <br />
                {{ @$myTicket->bets->type ? (@$myTicket->bets->type == 1 ? 'Single' : 'Multiple') : '' }} <br />
                @php
                    $BetStatus = ['', 'Win', 'Pending', 'Lose', 'Refunded'];
                @endphp
                {{ @$myTicket->bets->status ? 'Status:' . $BetStatus[@$myTicket->bets->status] : '' }}
            </h5>

            @if ($myTicket->status != Status::TICKET_CLOSE && $myTicket->user)
                <button class="btn btn--danger btn-sm" data-bs-toggle="modal" data-bs-target="#ticketCloseModal" type="button">
                    <i class="las la-times"></i>
                </button>
            @endif
        </div>

        <div class="card-body">
            <form action="{{ route('ticket.reply', $myTicket->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">@lang('Your Reply')</label>
                        <textarea class="form-control form--control" name="message" rows="3" required>{{ old('message') }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">@lang('Attachments')</label>
                        <button class="btn btn--add-more addFile text--base" type="button"><i
                                class="las la-plus-circle"></i> @lang('Add More')</button>
                    </div>
                    <input class="form-control form--control mb-3" name="attachments[]" type="file">
                    <div class="list" id="fileUploadsContainer"></div>
                    <code class="xsm-text text-muted"><i class="fas fa-info-circle"></i> @lang('Allowed File Extensions'):
                        .@lang('jpg'), .@lang('jpeg'), .@lang('png'), .@lang('pdf'), .@lang('doc'),
                        .@lang('docx')</code>
                </div>
                <div class="text-end">
                    <button class="btn btn--base" type="submit"><i class="fas fa-reply"></i> @lang('Reply')</button>
                </div>
            </form>
        </div>
    </div>

    @if (!blank($messages))
        <h5 class="mb-1 mt-4">@lang('Previous Replies')</h5>
        <div class="list support-list">
            @foreach ($messages as $message)
                @if ($message->admin_id == 0)
                    <div class="support-card">
                        <div class="support-card__head">
                            <h6 class="support-card__title">
                                {{ $message->ticket->name }}
                            </h6>
                            <span class="support-card__date">
                                <code class="xsm-text text-muted"><i class="far fa-clock"></i>
                                    {{ $message->created_at->format('dS F Y @ H:i') }}</code>
                            </span>
                        </div>
                        <div class="support-card__body">
                            <p class="support-card__body-text">
                                {{ $message->message }}
                            </p>

                            @if ($message->attachments->count() > 0)
                                <ul class="list list--row support-card__list">
                                    @foreach ($message->attachments as $k => $image)
                                        <li>
                                            <a class="support-card__file"
                                                href="{{ route('ticket.download', encrypt($image->id)) }}">
                                                <span class="support-card__file-icon">
                                                    <i class="far fa-file-alt"></i>
                                                </span>
                                                <span class="support-card__file-text">
                                                    @lang('Attachment') {{ ++$k }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="support-card">
                        <div class="support-card__head">
                            <h6 class="support-card__title">
                                {{ $message->admin->name }}
                            </h6>
                            <span class="support-card__date">
                                <code class="xsm-text text-muted"><i class="far fa-clock"></i>
                                    {{ $message->created_at->format('dS F Y @ H:i') }}</code>
                            </span>

                        </div>
                        <div class="support-card__body">
                            <p class="support-card__body-text text-md-start mb-0 text-center">
                                {{ $message->message }}
                            </p>

                            @if ($message->attachments->count() > 0)
                                <ul
                                    class="list list--row support-card__list justify-content-center justify-content-md-start flex-wrap">
                                    @foreach ($message->attachments as $k => $image)
                                        <li>
                                            <a class="support-card__file"
                                                href="{{ route('ticket.download', encrypt($image->id)) }}">
                                                <span class="support-card__file-icon">
                                                    <i class="far fa-file-alt"></i>
                                                </span>
                                                <span class="support-card__file-text">
                                                    @lang('Attachment') {{ ++$k }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if ($layout == 'frontend')
        </div>
        </div>
    @else
        </div>
    @endif

    <div class="modal fade custom--modal" id="ticketCloseModal" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation')!</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    @lang('Are you sure to close this ticket?')
                </div>
                <div class="modal-footer">
                    <button class="btn btn--dark btn--sm sm-text" data-bs-dismiss="modal"
                        type="button">@lang('No')</button>
                    <form action="{{ route('ticket.close', $myTicket->id) }}" method="POST">
                        @csrf
                        <button class="btn btn--base btn--sm text--light sm-text" type="submit">@lang('Yes')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
