@extends('admin.layouts.app')

@section('panel')
    @php
        $isGameDataExists = $game->id ?? false;
    @endphp

    <form action="{{ route('admin.game.store', $isGameDataExists ?? 0) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row justify-content-center">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>@lang('League')</label>
                                    <select class="form-control select2-basic slug" name="league_id" required>
                                        <option value="" selected disabled>@lang('Select One')</option>
                                        @foreach ($leagues as $league)
                                            <option data-name="{{ $league->name }}" data-category="{{ $league->category_id }}" value="{{ $league->id }}" @selected(@$game->league_id == $league->id)>{{ __($league->name) }} -
                                                ({{ __($league->category->name) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Team One')</label>
                                    <select class="form-control select2-basic teams slug" name="team_one_id" required>
                                        <option value="" selected disabled>@lang('Select One')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Team Two')</label>
                                    <select class="form-control select2-basic teams slug" name="team_two_id" required>
                                        <option value="" selected disabled>@lang('Select One')</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Game Starts From')</label>
                                    <input class="dateTimePicker form-control bg--white" name="start_time" data-language="en" data-position="top left" type="text" value="{{ $isGameDataExists ? showDateTime($game->start_time) : old('start_time') }}" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Bet Starts From')</label>
                                    <input class="dateTimePicker form-control bg--white" name="bet_start_time" data-language="en" data-position="top left" type="text" value="{{ $isGameDataExists ? showDateTime($game->bet_start_time) : old('bet_start_time') }}" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Bet Ends At')</label>
                                    <input class="dateTimePicker form-control bg--white" name="bet_end_time" data-language="en" data-position="top left" type="text" value="{{ $isGameDataExists ? showDateTime($game->bet_end_time) : old('bet_end_time') }}" autocomplete="off" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label>@lang('Slug')</label>
                                    <input class="form-control" name="slug" type="text" value="{{ old('slug') }}" required>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.game.index') }}"></x-back>
@endpush

@push('style')
    <style>
        .select2-container {
            z-index: 9 !important;
        }
    </style>
@endpush

@push('style-lib')
    <link href="{{ asset('assets/admin/css/vendor/datepicker.min.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";


            @if ($isGameDataExists)
                $('[name=slug]').val(`{{ $game->slug }}`);
            @endif

            let isExistiTeamOne = "{{ $isGameDataExists ? $game->team_one_id : old('team_one_id') }}";
            let isExistiTeamTwo = "{{ $isGameDataExists ? $game->team_two_id : old('team_two_id') }}";
            let counter = false;

            $('.dateTimePicker').datepicker({
                onSelect: makeGameSlug,
                timepicker: true,
                dateFormat: 'yyyy-mm-dd',
            });

            @if (old('league_id'))
                $('[name=league_id]').val({{ old('league_id') }})
            @endif

            $('.select2-basic').select2({
                dropdownParent: $('.card-body')
            });

            $('[name=league_id]').on('change', function() {
                if (!this.value) {
                    return;
                }

                let categoryId = $(this).find(":selected").data('category');

                $.ajax({
                    type: "get",
                    url: `{{ route('admin.game.teams', '') }}/${categoryId}`,
                    dataType: "json",
                    success: function(response) {
                        if (response.teams) {
                            $('.teams').removeAttr('disabled');
                            $('[name=team_one_id]').html(
                                `<option value="" disabled {{ $isGameDataExists ? '' : 'selected' }}>@lang('Select One')</option>`
                            );
                            $('[name=team_two_id]').html(
                                `<option value="" disabled {{ $isGameDataExists ? '' : 'selected' }} >@lang('Select One')</option>`
                            );

                            $.each(response.teams, function(i, team) {
                                $('[name=team_one_id]').append(
                                    `<option data-team_one="${team.name}" value="${team.id}" ${(isExistiTeamOne == team.id) ? 'selected' : ''}> ${team.name}</option>`
                                );
                                $('[name=team_two_id]').append(
                                    `<option data-team_two="${team.name}" value="${team.id}" ${(isExistiTeamTwo == team.id) ? 'selected' : ''}> ${team.name}</option>`
                                );
                            });
                        } else {
                            $('[name=team_one_id]').html(
                                `<option value="" selected disabled>@lang('Select One')</option>`);
                            $('[name=team_two_id]').html(
                                `<option value="" selected disabled>@lang('Select One')</option>`);
                            $('#league_id').closest('.form-group').find(
                                '.select2-selection__rendered').text("@lang('Select One')");
                            $('[name=league_id]').val('');

                            notify('error', response.error);
                        }
                    }
                });
            }).change();

            $('[name=team_one_id]').on('change', function() {

                let teamOneValue = this.value;
                let teamTwoValue = $('[name=team_two_id]').val();

                if (teamOneValue == teamTwoValue) {
                    $('#team_one_id').closest('.form-group').find('.select2-selection__rendered').text(
                        "@lang('Select One')");
                    $('[name=team_one_id]').val('');
                    notify('error', "Same team can't be opponent");
                }
            });

            $('[name=team_two_id]').on('change', function() {
                let teamOneValue = $('[name=team_one_id]').val();
                let teamTwoValue = this.value;

                if (teamOneValue == teamTwoValue) {
                    $('#team_two_id').closest('.form-group').find('.select2-selection__rendered').text(
                        "@lang('Select One')");
                    $('[name=team_two_id]').val('');
                    notify('error', "Same team can't be opponent");
                }
            });

            $('.slug').on('change', function() {
                makeGameSlug();
            });

            function makeGameSlug() {

                let slug = ``;
                if ($('[name=league_id]').val()) {
                    slug = `${$('[name=league_id]').find(':selected').data('name')} `;
                }

                if ($(document).find('[name=team_one_id]').val()) {
                    slug += `${$(document).find('[name=team_one_id]').find(':selected').html()} `;
                }
                if ($('[name=team_two_id]').val()) {
                    slug += `${' vs ' + $('[name=team_two_id]').find(':selected').html()} `;
                }
                if ($('[name=start_time]').val()) {
                    let startTime = $('[name=start_time]').val();
                    slug += `${startTime.replace(/:/g, "-")} `;
                }
                if ($('[name=end_time]').val()) {
                    let endTime = $('[name=end_time]').val();
                    slug += `${endTime.replace(/:/g, "-")} `;
                }

                slug = slug.trim();
                slug = slug.replace(/\s+/g, '-').toLowerCase();
                $('[name=slug]').val(slug);
            }



        })(jQuery)
    </script>
@endpush
