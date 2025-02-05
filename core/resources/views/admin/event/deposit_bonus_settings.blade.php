@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-3">
                    <form action="{{route('admin.event.deposit.settings.create')}}" enctype="multipart/form-data"
                          method="POST" class="row">
                        @csrf
                        <div class="col-md-2">
                            <div class="form-group">
                                <input type="hidden" name="type" value="deposit">
                                <label for="deposit_percentage">@lang('Deposit Percentage')</label>
                                <input type="number" id="deposit_percentage" name="deposit_percentage" value="20"
                                       placeholder="10%" class="form-control" required/>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="bonus_type">@lang('Bonus type')</label>
                                <select id="bonus_type" name="bonus_type" class="form-control bonus_type" required>
                                    <option value="providers">Providers</option>
                                    <option value="days">Days</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 days" style="display:none">
                            <div class="form-group">
                                <label for="days">@lang('Activation Days')</label>
                                <select id="days" name="days[]" class="form-control select2" multiple="multiple">
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 providers">
                            <div class="form-group">
                                <label for="providers">@lang('Providers')</label>
                                <select id="providers" name="providers[]" class="form-control select2"
                                        multiple="multiple">
                                    @foreach($providers as $provider)
                                        <option value="{{$provider->id}}">{{$provider->name}}</option>
                                    @endforeach

                                    <option value="cash_agent">Cash Agent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="game_type">@lang('Game type')</label>
                                <select id="game_type" name="game_type" class="form-control" required>
                                    <option value="sports">Sports (Upcoming)</option>
                                    <option value="casino">Casino</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="wager">@lang('Wager')</label>
                                <input type="number" id="wager" name="wager" value="3" placeholder="3"
                                       class="form-control" required min="1"/>
                            </div>
                        </div>
                        <div class="col-md-3 hide-fields">
                            <div class="form-group">
                                <label for="rollover">@lang('Rollover')</label>
                                <input type="number" id="rollover" name="rollover" value="3" placeholder="3"
                                       class="form-control" required min="1"/>
                            </div>
                        </div>
                        <div class="col-md-3 hide-fields">
                            <div class="form-group">
                                <label for="minimum_bet">@lang('Minimum bet in multibet')</label>
                                <input type="number" id="minimum_bet" name="minimum_bet" value="3" placeholder="3"
                                       class="form-control" required min="1"/>
                            </div>
                        </div>
                        <div class="col-md-3 hide-fields">
                            <div class="form-group">
                                <label for="odd_selection">@lang('Minimum odds')</label>
                                <input type="text" id="odd_selection" name="odd_selection" value="1.4"
                                       class="form-control" required/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="min_bonus">@lang('Minimum bonus amount')</label>
                                <input type="number" id="min_bonus" name="min_bonus" value="0" class="form-control"
                                       required/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="max_bonus">@lang('Maximum bonusamount')</label>
                                <input type="number" id="max_bonus" name="max_bonus" value="0" class="form-control"
                                       required/>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="valid_time">@lang('Playing time')</label>
                                <select id="valid_time" name="valid_time" class="form-control" required>
                                    <option value="1">24 hrs</option>
                                    <option value="2">48 hrs</option>
                                    <option value="3">72 hrs</option>
                                    <option value="168">7 days</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="maximum_claim_in_day">@lang('Maximum Calim in day')</label>
                                <input type="number" id="maximum_claim_in_day" name="maximum_claim_in_day" value="1"
                                       min="1" class="form-control" required/>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-label">@lang('Image')</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-primary">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card b-radisu--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                            <tr>
                                <th>@lang('Bonus type')</th>
                                <th>@lang('Image')</th>
                                <th>@lang('Game type')</th>
                                <th>@lang('Days')</th>
                                <th>@lang('Providers')</th>
                                <th>@lang('Percentage')</th>
                                <th>@lang('Wager')</th>
                                <th>@lang('Rollover')</th>
                                <th>@lang('Min Bet')</th>
                                <th>@lang('Minimum Odds')</th>
                                <th>@lang('Valid time')</th>
                                <th>@lang('Minimum bonus')</th>
                                <th>@lang('Maximum bonus')</th>
                                <th>@lang('Claim in Day')</th>
                                <th>@lang('Stauts')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $valid = [
                                    1 => '1 days',
                                    2 => '2 days',
                                    3 => '3 days',
                                    7 => '7 days'
                                ];
                            @endphp
                            @forelse ($depositBonus as $item)
                                <tr>
                                    <td>{{$item->bonus_type}}</td>
                                    <td>
                                        @if ($item->file)
                                            <img src="{{ asset('/core/public/storage/bonus/' . $item->file) }}"
                                                 alt="Logo" width="50">
                                        @else
                                            <img src="https://via.placeholder.com/50x50" alt="Logo"
                                                 width="50">
                                        @endif
                                    </td>
                                    <td>{{$item->game_type}}</td>
                                    <td>
                                        @php
                                            $days = is_string($item->days) ? json_decode($item->days, true) : $item->days;
                                        @endphp
                                        {{ is_array($days) ? implode(', ', $days) : $item->days }}
                                    </td>
                                    <td>
                                        @php
                                            $providerIds = json_decode($item->providers, true);
                                            $providerNames = [];
                            
                                            if (is_array($providerIds)) {
                                                foreach ($providerIds as $providerId) {
                                                    if ($providerId === 'cash_agent') {
                                                        $providerNames[] = 'cash agent';
                                                    } else {
                                                        $provider = $providers->firstWhere('id', $providerId);
                                                        if ($provider) {
                                                            $providerNames[] = $provider->name;
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        {{ implode(', ', $providerNames) }}
                                    </td>
                                    <td>{{$item->deposit_percentage}}</td>
                                    <td>{{$item->wager}}</td>
                                    <td>{{$item->rollover}}</td>
                                    <td>{{$item->minimum_bet}}</td>
                                    <td>{{$item->odd_selection}}</td>
                                    <td>{{$valid[$item->valid_time]}}</td>
                                    <td>{{$item->min_bonus}}</td>
                                    <td>{{$item->max_bonus}}</td>
                                    <td>{{$item->maximum_claim_in_day}}</td>
                                    <td>{{$item->status ? 'Active' : 'Inactive'}}</td>
                                    <td>
                                        <a href="{{route('admin.event.deposit.settings.edit', $item->id)}}"
                                           class="btn-sm btn-primary">Edit</a>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-danger deleteBtn"
                                           data-id="{{ $item->id }}">
                                            @lang('Delete')
                                        </a>
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
                    @if ($depositBonus->hasPages())
                        <div class="card-footer py-4">
                            {{ paginateLinks($depositBonus) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                placeholder: "---Select---"
            });

            $('.bonus_type').on('change', function () {
                if (this.value == 'days') {
                    $('.providers').attr("required", false);
                    $('.providers').css("display", "none")
                    $('.days').css("display", "block");
                    $('.dayjs').attr("required", true);
                } else {
                    $('.providers').css("display", "block")
                    $('.dayjs').attr("required", false);
                    $('.providers').attr("required", true);
                    $('.days').css("display", "none");
                }
            })
        })
    </script>

    <script>
        $(document).ready(function () {
            $('.deleteBtn').on('click', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                var url = "{{ route('admin.event.deposit.settings.delete', ':id') }}";
                url = url.replace(':id', id);
                var csrf_token = $('meta[name="csrf-token"]').attr('content');

                Swal.fire({
                    title: "@lang('Are you sure to delete?')"
                    , text: "@lang('You won\'t be able to revert this!')"
                    , icon: "warning"
                    , showCancelButton: true
                    , confirmButtonColor: "#335eea"
                    , cancelButtonColor: "#d33"
                    , confirmButtonText: "@lang('Yes, delete it!')"
                    , cancelButtonText: "@lang('Cancel')"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: "DELETE",
                            data: {
                                "_token": csrf_token
                                , "id": id
                            }
                            , success: function (response) {
                                Swal.fire({
                                    icon: "success"
                                    , title: "@lang('Deleted')!"
                                    , text: 'News Deleted Successfully!'
                                    , showConfirmButton: false
                                    , timer: 1500
                                });
                                window.location.reload();
                            }
                        });
                    }
                });
            });
        })

    </script>
    {{--script for hide Rollove,Minimum bet in multibet,Minimum odds depend on game type--}}
    <script>
        $(document).ready(function () {
            $('#game_type').on('change', function () {
                let gameType = $(this).val();
                if (gameType === 'casino') {
                    $('.hide-fields').hide();
                } else {
                    $('.hide-fields').show();
                }
            });
            $('#game_type').trigger('change');
        });
    </script>
@endpush