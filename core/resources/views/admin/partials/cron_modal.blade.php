@can('super-admin')
    <div class="modal fade" id="cronModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">@lang('Cron Job Setting')</h5>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <div class="cron-p-style cron-p-style alert-info text--dark p-3">
                                <p class="mb-1">
                                    <span><i class="las la-info-circle"></i></span>
                                    @lang('To automate the return process of ') <b>@lang('placed bets')</b> , @lang('you need to set ') <b>@lang('cron jobs')</b>
                                    @lang('on your server. A cron job is a task that is scheduled to run at a specific time. In this case, the cron job would run every few minutes to check for any placed bets that have been completed. If a bet has been completed, the cron job would automatically return the winnings to the bettor.')
                                </p>
                                <p>
                                    @lang('The cron time can be set as low as possible, but it is important to make sure that the cron job does not overload the server. A good starting point would be to set the cron time to run') <b>@lang('every five minutes')</b>. @lang('This would ensure that the return process is quick and efficient, while also avoiding overloading the server.')
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>@lang('Win Cron Command')</label>
                            <div class="input-group">
                                <input class="form-control copyText" type="text" value="curl -s {{ route('win.cron') }}"
                                    readonly>
                                <button class="input-group-text btn--primary copyBtn border-0"> @lang('COPY')</button>
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>@lang('Lose Cron Command')</label>
                            <div class="input-group">
                                <input class="form-control copyText" type="text" value="curl -s {{ route('lose.cron') }}"
                                    readonly>
                                <button class="input-group-text btn--primary copyBtn border-0"> @lang('COPY')</button>
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>@lang('Refund Cron Command')</label>
                            <div class="input-group">
                                <input class="form-control copyText" type="text"
                                    value="curl -s {{ route('refund.cron') }}" readonly>
                                <button class="input-group-text btn--primary copyBtn border-0"> @lang('COPY')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan
@push('script')
    @if (Carbon\Carbon::parse($general->last_win_cron)->diffInSeconds() >= 5400 ||
            Carbon\Carbon::parse($general->last_lose_cron)->diffInSeconds() >= 5400 ||
            !$general->last_win_cron ||
            !$general->last_lose_cron)
        <script>
            'use strict';
            $(document).ready(function(e) {
                $("#cronModal").modal('show');
                $('.copyBtn').on('click', function() {
                    var copyText = $(this).siblings('.copyText')[0];
                    copyText.select();
                    copyText.setSelectionRange(0, 99999);
                    document.execCommand("copy");
                    copyText.blur();
                    $(this).addClass('copied');
                    setTimeout(() => {
                        $(this).removeClass('copied');
                    }, 1500);
                });
            });
        </script>
    @endif
@endpush
