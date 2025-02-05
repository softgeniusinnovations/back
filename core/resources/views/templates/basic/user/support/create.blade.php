@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="card custom--card">
        <h5 class="card-header">
            <i class="las la-ticket-alt"></i>
            @lang('Open New Ticket')
        </h5>
        <div class="card-body">
            <form action="{{ route('ticket.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Subject')</label>
                            {{-- <input class="form-control form--control" name="subject" type="text"
                                value="{{ old('subject') }}" required> --}}
                            <select name="subject" id="subject" class="form-control form--control" required>
                                <option value="">---Select subject ---</option>
                                <option @selected(old('subject') == 'Withdraw problem') value="Withdraw problem">Withdraw problem</option>
                                <option @selected(old('subject') == 'Deposit problem') value="Deposit problem">Deposit problem</option>
                                <option @selected(old('subject') == 'Bet issue') value="Bet issue">Bet issue</option>
                                <option @selected(old('subject') == 'KYC') value="KYC">KYC</option>
                                <option @selected(old('subject') == 'Banned') value="Banned">Banned</option>
                                <option @selected(old('subject') == 'Others') value="Others">Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">@lang('Priority')</label>
                            <div class="form--select">
                                <select class="form-select" name="priority" required>
                                    <option value="3">@lang('High')</option>
                                    <option value="2">@lang('Medium')</option>
                                    <option value="1">@lang('Low')</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 bet" style="display: none">
                    <div class="form-group">
                        <label class="form-label" for="bet_no">Bet</label>
                        <select name="bet_no" id="bet_no" class="form-control form--control">
                            <option value="">---Select Bet ---</option>
                            @forelse ($allBets as $bet)
                                <option value="{{ $bet->bet_num }}">
                                    {{ $bet->bet_number . '--' . $bet->amount . '--' }}
                                    {{ $bet->type === 1 ? 'Single' : 'Multiple' }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="col-12 trx" style="display: none">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="transaction_id">Transaction ID*</label>
                                <input type="text" name="transaction_id" class="form-control"
                                    placeholder="Transaction ID">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="transaction_date">Transaction Date*</label>
                                <input type="date" name="transaction_date" class="form-control"
                                    placeholder="Transaction Date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label">@lang('Message')</label>
                        <textarea class="form-control form--control" name="message" rows="3" required>{{ old('message') }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">@lang('Attachments')</label>
                        <button class="btn btn--add-more addFile text--base" type="button"><i
                                class="las la-plus-circle"></i> @lang('Add More')</button>
                    </div>
                    <input class="form-control form--control" name="attachments[]" type="file"
                        accept=".jpg, .jpeg, .png, .pdf, .doc, .docx">
                    <div class="list mt-3" id="fileUploadsContainer"></div>
                    <code class="xsm-text text-muted"><i class="fas fa-info-circle"></i> @lang('Allowed File Extensions'):
                        .@lang('jpg'), .@lang('jpeg'), .@lang('png'), .@lang('pdf'),
                        .@lang('doc'), .@lang('docx')</code>
                </div>
                <div class="text-end">
                    <button class="btn btn--base mt-3" type="submit">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('script')
    <script>
        (function($) {
            "use strict";



            // payment gateway selection
            $('[name=subject]').on('change', function() {
                var subjectValue = $(this).find('option:selected').val();
                if (subjectValue == 'Withdraw problem' || subjectValue == 'Deposit problem') {
                    $('.trx').show();
                    $('[name=transaction_id], [name=transaction_date]').attr('required', true);
                } else {
                    $('[name=transaction_id], [name=transaction_date]').attr('required', false);
                    $('.trx').hide();
                }

                if (subjectValue == 'Bet issue') {
                    $('[name=bet_no]').attr('required', true);
                    $('.bet').show();
                } else {
                    $('[name=bet_no]').attr('required', false);
                    $('.bet').hide();
                }

            });

        })(jQuery);
    </script>
@endpush
@push('style')
    <style>
        .input--group .input-group-text {
            top: 8px !important;
        }
    </style>
@endpush
