@extends('admin.layouts.app')
@php
    $types = ['super-admin', 'agent', 'cash-agent', 'mob-agent', 'affiliator', 'support', 'report'];
@endphp
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    {{-- <form action="" method="POST" enctype="multipart/form-data"> --}}
                    <form action="{{ route('admin.event.user.search') }}" method="get" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Search User')</label>
                                    <input class="form-control form--control" name="input" type="text" placeholder="Search user by User id or name or email"value="{{ old('input') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn--primary mt-3" type="submit">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">

            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                            <tr>
                                <th>@lang('#')</th>
                                <th>@lang('User Id')</th>
                                <th>@lang('User name')</th>
                                <th>@lang('Balance')</th>
                                <th>@lang('Bonus Balance')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($users as $key=>$item)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $item->user_id }}</td>
                                    <td>{{ $item->username}}</td>
                                    <td>{{ $item->balance }}</td>
                                    <td>{{ $item->bonus_account }}</td>

                                    <td>
                                        <div class="button--group d-flex flex-wrap">
                                            @if($item->has_active_bonus != true)
                                                <button id="send-bonus-btn"
                                                        class="btn btn-sm btn-outline-primary mr-1 view-user-btn"
                                                        data-id="{{ $item->id }}"
                                                        title="Send Bonus">
                                                    <i class="fa fa-rocket"></i>
                                                </button>
                                            @else
                                                <p class="text-danger mr-3">User has an active bonus. <br>You can't send any bonus</p>
                                            @endif

                                            @if($item->has_active_casinobonus != true)
                                                <button id="send-casino-bonus-btn"
                                                        class="btn btn-sm btn-outline-primary view-user-btn-casino"
                                                        data-id="{{ $item->id }}"
                                                        title="Send Casino Bonus">
                                                    <i class="fa fa-rocket"></i>
                                                </button>
                                            @else
                                                <p class="text-danger">User has an active Casino bonus. <br>You can't send any bonus</p>
                                            @endif
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

                </div>
            </div>
        </div>
    </div>

    <div id="userModal" class="modal" tabindex="-1" style="display:none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('User Details')</h5>
                    <button type="button" class="btn-close" id="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card b-radius--10">
                                    <div class="card-body p-3">
                                        <form id="bonusForm" method="post" class="row">
                                            @csrf

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="amount">@lang('Amount')</label>
                                                    <input id="amount" type="number" name="amount" value="{{ old('amount') }}" placeholder="Amount" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="rollover">@lang('Rollover')</label>
                                                    <input id="rollover" type="number" name="rollover" value="{{ old('rollover') }}" placeholder="rollover" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="minimum_betin_multibet">@lang('Minumum bet in Multibet')</label>
                                                    <input id="minimum_betin_multibet" type="number" name="minimum_betin_multibet" value="{{ old('minimum_betin_multibet') }}" placeholder="Minumum bet in Multibet" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="minimum_odd">@lang('Minimum Odd')</label>
                                                    <input id="minimum_odd" type="number" name="minimum_odd" value="{{ old('minimum_odd') }}" placeholder="Minimum Odd" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="valid_time">@lang('validation time')</label>
                                                    <select name="valid_time" class="form-control" required>
                                                        <option>---Select time duration---</option>
                                                        <option value="1">24 Hours</option>
                                                        <option value="2">48 Hours</option>
                                                        <option value="3">72 Hours</option>
                                                        <option value="7">7 Days</option>
                                                        <option value="14600">Lifetime</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeModalFooter">@lang('Close')</button>
                    <button type="submit" id="submitBonus" class="btn btn-sm btn-primary">@lang('Submit')</button>
                </div>
            </div>
        </div>
    </div>


    <div id="userModalCasino" class="modal" tabindex="-1" style="display:none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('User Details')</h5>
                    <button type="button" class="btn-close" id="closeModalcasino"></button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card b-radius--10">
                                    <div class="card-body p-3">
                                        <form id="casinobonusForm" method="post" class="row">
                                            @csrf

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="amount">@lang('Amount')</label>
                                                    <input id="amount" type="number" name="amount" value="{{ old('amount') }}" placeholder="Amount" class="form-control" />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="wager">@lang('Wager')</label>
                                                    <input id="wager" type="number" name="wager" value="{{ old('wager') }}" placeholder="wager" class="form-control" />
                                                </div>
                                            </div>

{{--                                            <div class="col-md-4">--}}
{{--                                                <div class="form-group">--}}
{{--                                                    <label for="minimum_betin_multibet">@lang('Minumum bet in Multibet')</label>--}}
{{--                                                    <input id="minimum_betin_multibet" type="number" name="minimum_betin_multibet" value="{{ old('minimum_betin_multibet') }}" placeholder="Minumum bet in Multibet" class="form-control" />--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                            <div class="col-md-4">--}}
{{--                                                <div class="form-group">--}}
{{--                                                    <label for="minimum_odd">@lang('Minimum Odd')</label>--}}
{{--                                                    <input id="minimum_odd" type="number" name="minimum_odd" value="{{ old('minimum_odd') }}" placeholder="Minimum Odd" class="form-control" />--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="valid_time">@lang('validation time')</label>
                                                    <select name="valid_time" class="form-control" required>
                                                        <option>---Select time duration---</option>
                                                        <option value="1">24 Hours</option>
                                                        <option value="2">48 Hours</option>
                                                        <option value="3">72 Hours</option>
                                                        <option value="7">7 Days</option>
                                                        <option value="14600">Lifetime</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="closeModalFootercasino">@lang('Close')</button>
                    <button type="submit" id="submitCasinoBonus" class="btn btn-sm btn-primary">@lang('Submit')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Handle View button click
            $('.view-user-btn').on('click', function() {
                // Get user data from button's data attributes
                var userId = $(this).data('id');

                var form = document.getElementById('bonusForm');
                form.action = '/admin/event/send/bonus/' + userId;
                console.log("userid",userId)
                console.log("form",form)




                // Show the modal
                $('#userModal').css('display', 'block');
            });

            // Close the modal when the close buttons are clicked
            $('#closeModal, #closeModalFooter').on('click', function() {
                $('#userModal').css('display', 'none');
            });

            document.getElementById('submitBonus').addEventListener('click', function () {
                // Submit the form
                document.getElementById('bonusForm').submit();
            });



            // Close the modal when clicking outside the modal content
            // $(window).on('click', function(e) {
            //     if ($(e.target).is('#userModal')) {
            //         $('#userModal').css('display', 'none');
            //     }
            // });
        });


    </script>

    <script>
        $(document).ready(function() {
            // Handle View button click
            $('.view-user-btn-casino').on('click', function() {
                // Get user data from button's data attributes
                var userId = $(this).data('id');

                var form = document.getElementById('casinobonusForm');
                form.action = '/admin/event/send/casino/bonus/' + userId;
                console.log("userid",userId)
                console.log("form",form)




                // Show the modal
                $('#userModalCasino').css('display', 'block');
            });

            // Close the modal when the close buttons are clicked
            $('#closeModalcasino, #closeModalFootercasino').on('click', function() {
                $('#userModalCasino').css('display', 'none');
            });

            document.getElementById('submitCasinoBonus').addEventListener('click', function () {
                // Submit the form
                document.getElementById('casinobonusForm').submit();
            });



            // Close the modal when clicking outside the modal content
            // $(window).on('click', function(e) {
            //     if ($(e.target).is('#userModal')) {
            //         $('#userModal').css('display', 'none');
            //     }
            // });
        });


    </script>
@endpush
