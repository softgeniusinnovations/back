@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-3">
               <form action="{{route('admin.event.cashback.settings.update')}}" method="POST" class="row">
                   @csrf
                  <div class="col-md-4">
                       <div class="form-group">
                           <input type="hidden" name="type" value="cashback">
                           <label for="cashback_percentage">@lang('Cashback Percentage')</label>
                           <input type="number" id="cashback_percentage" name="cashback_percentage" value="{{$setting->cashback_percentage ?? 20}}" placeholder="10" class="form-control" required />
                       </div>
                   </div>
                   
                   {{-- <div class="col-md-4">
                       <div class="form-group">
                           <label for="loss_calculation_start">@lang('Loss calculation duration start')</label>
                           <select id="loss_calculation_start" name="loss_calculation_start" class="form-control" required>
                              <option value="Saturday" @selected(@$setting->loss_calculation_start == 'Saturday')>Saturday</option>
                              <option value="Sunday" @selected(@$setting->loss_calculation_start== 'Sunday')>Sunday</option>
                              <option value="Monday" @selected(@$setting->loss_calculation_start== 'Monday')>Monday</option>
                              <option value="Tuesday" @selected(@$setting->loss_calculation_start== 'Tuesday')>Tuesday</option>
                              <option value="Wednesday" @selected(@$setting->loss_calculation_start== 'Wednesday')>Wednesday</option>
                              <option value="Thursday" @selected(@$setting->loss_calculation_start== 'Thursday')>Thursday</option>
                              <option value="Friday" @selected(@$setting->loss_calculation_start== 'Friday')>Friday</option>
                           </select>
                       </div>
                   </div> --}}
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="loss_calculation_end">@lang('Loss calculation duration')</label>
                           <select id="loss_calculation_end" name="loss_calculation_end" class="form-control" required>
                              <option value="Saturday" @selected(@$setting->loss_calculation_end== 'Saturday')>Saturday</option>
                              <option value="Sunday"  @selected(@$setting->loss_calculation_end== 'Sunday')>Sunday</option>
                              <option value="Monday" @selected(@$setting->loss_calculation_end== 'Monday')>Monday</option>
                              <option value="Tuesday" @selected(@$setting->loss_calculation_end== 'Tuesday')>Tuesday</option>
                              <option value="Wednesday" @selected(@$setting->loss_calculation_end== 'Wednesday')>Wednesday</option>
                              <option value="Thursday" @selected(@$setting->loss_calculation_end== 'Thursday')>Thursday</option>
                              <option value="Friday" @selected(@$setting->loss_calculation_end== 'Friday')>Friday</option>
                           </select>
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="game_type">@lang('Game type')</label>
                           <select id="game_type" name="game_type" class="form-control" required>
                              <option value="upcoming"  @selected(@$setting->game_type== 'upcoming')>Upcoming</option>
{{--                              <option value="live" @selected(@$setting->game_type== 'live')>Live</option>--}}
                           </select>
                       </div>
                   </div>
                   <div class="col-md-3">
                       <div class="form-group">
                           <label for="wager">@lang('Wager')</label>
                           <input type="number" id="wager" name="wager" value="{{$setting->wager ?? 3}}" placeholder="3" class="form-control" required min="1" />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="rollover">@lang('Rollover')</label>
                           <input type="number" id="rollover" name="rollover" value="{{$setting->rollover ?? 3}}" placeholder="3" class="form-control" required min="1" />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="minimum_bet">@lang('Minimum bet in multibet')</label>
                           <input type="number" id="minimum_bet" name="minimum_bet" value="{{$setting->minimum_bet ?? 3}}" placeholder="3" class="form-control" required min="1" />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="odd_selection">@lang('Minimum odds')</label>
                           <input type="number" id="odd_selection" name="odd_selection" value="{{$setting->odd_selection ?? 1.4}}" class="form-control" required />
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="valid_time">@lang('Playing time')</label>
                           <select id="valid_time" name="valid_time" class="form-control" required>
                              <option value="1"  @selected(@$setting->valid_time== 1) >24 hrs</option>
                              <option value="2"  @selected(@$setting->valid_time== 2) >48 hrs</option>
                              <option value="3"  @selected(@$setting->valid_time== 3) >72 hrs</option>
                              <option value="168"  @selected(@$setting->valid_time== 168) >7 days</option>
                           </select>
                       </div>
                   </div>
                    <div class="col-md-4">
                       <div class="form-group">
                           <label for="activation_day">@lang('Activation day')</label>
                           <input type="number" id="activation_day" name="activation_day" value="{{$setting->activation_day ?? 7}}" class="form-control" required min="1" />
                       </div>
                   </div> 
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="maximum_claim_in_week">@lang('Maximum Calim in week')</label>
                           <input type="number" id="maximum_claim_in_week" name="maximum_claim_in_week" value="{{$setting->maximum_claim_in_week ?? 1}}" min="1" class="form-control" required />
                       </div>
                   </div>
                   
                   <div class="col-md-4">
                       <div class="form-group">
                           <button class="btn btn-sm btn-primary">Update</button>
                       </div>
                   </div>
               </form>
            </div>
        </div>
    </div>
</div>

<div class="d-flex mb-30 mt-4 flex-wrap gap-3 justify-content-between align-items-center">
    <h6 class="page-title">Cashback Setting for Casino</h6>
    <div class="d-flex flex-wrap justify-content-end gap-2 align-items-center breadcrumb-plugins">
        @stack('breadcrumb-plugins')
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-3">
                <form action="{{route('admin.event.cashback.settings.casino.update')}}" method="POST" class="row">
                    @csrf
                    <div class="col-md-4">
                        <div class="form-group">
                            <input type="hidden" name="type" value="cashback">
                            <label for="cashback_percentage">@lang('Cashback Percentage')</label>
                            <input type="number" id="cashback_percentage" name="cashback_percentage" value="{{$casinosetting->cashback_percentage ?? 20}}" placeholder="10" class="form-control" required />
                        </div>
                    </div>

                    {{-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="loss_calculation_start">@lang('Loss calculation duration start')</label>
                            <select id="loss_calculation_start" name="loss_calculation_start" class="form-control" required>
                               <option value="Saturday" @selected(@$setting->loss_calculation_start == 'Saturday')>Saturday</option>
                               <option value="Sunday" @selected(@$setting->loss_calculation_start== 'Sunday')>Sunday</option>
                               <option value="Monday" @selected(@$setting->loss_calculation_start== 'Monday')>Monday</option>
                               <option value="Tuesday" @selected(@$setting->loss_calculation_start== 'Tuesday')>Tuesday</option>
                               <option value="Wednesday" @selected(@$setting->loss_calculation_start== 'Wednesday')>Wednesday</option>
                               <option value="Thursday" @selected(@$setting->loss_calculation_start== 'Thursday')>Thursday</option>
                               <option value="Friday" @selected(@$setting->loss_calculation_start== 'Friday')>Friday</option>
                            </select>
                        </div>
                    </div> --}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="loss_calculation_end">@lang('Loss calculation duration')</label>
                            <select id="loss_calculation_end" name="loss_calculation_end" class="form-control" required>
                                <option value="Saturday" @selected(@$casinosetting->loss_calculation_end== 'Saturday')>Saturday</option>
                                <option value="Sunday"  @selected(@$casinosetting->loss_calculation_end== 'Sunday')>Sunday</option>
                                <option value="Monday" @selected(@$casinosetting->loss_calculation_end== 'Monday')>Monday</option>
                                <option value="Tuesday" @selected(@$casinosetting->loss_calculation_end== 'Tuesday')>Tuesday</option>
                                <option value="Wednesday" @selected(@$casinosetting->loss_calculation_end== 'Wednesday')>Wednesday</option>
                                <option value="Thursday" @selected(@$casinosetting->loss_calculation_end== 'Thursday')>Thursday</option>
                                <option value="Friday" @selected(@$casinosetting->loss_calculation_end== 'Friday')>Friday</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="game_type">@lang('Game type')</label>
                            <select id="game_type" name="game_type" class="form-control" required>
                                <option value="casino"  @selected(@$casinosetting->game_type== 'casino')>Casino</option>
                                {{--                              <option value="live" @selected(@$setting->game_type== 'live')>Live</option>--}}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="wager">@lang('Wager')</label>
                            <input type="number" id="wager" name="wager" value="{{$casinosetting->wager ?? 3}}" placeholder="3" class="form-control" required min="1" />
                        </div>
                    </div>
{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="rollover">@lang('Rollover')</label>--}}
{{--                            <input type="number" id="rollover" name="rollover" value="{{$setting->rollover ?? 3}}" placeholder="3" class="form-control" required min="1" />--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="minimum_bet">@lang('Minimum bet in multibet')</label>--}}
{{--                            <input type="number" id="minimum_bet" name="minimum_bet" value="{{$setting->minimum_bet ?? 3}}" placeholder="3" class="form-control" required min="1" />--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="odd_selection">@lang('Minimum odds')</label>--}}
{{--                            <input type="number" id="odd_selection" name="odd_selection" value="{{$setting->odd_selection ?? 1.4}}" class="form-control" required />--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="valid_time">@lang('Playing time')</label>
                            <select id="valid_time" name="valid_time" class="form-control" required>
                                <option value="1"  @selected(@$casinosetting->valid_time== 1) >24 hrs</option>
                                <option value="2"  @selected(@$casinosetting->valid_time== 2) >48 hrs</option>
                                <option value="3"  @selected(@$casinosetting->valid_time== 3) >72 hrs</option>
                                <option value="168"  @selected(@$casinosetting->valid_time== 168) >7 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="activation_day">@lang('Activation day')</label>
                            <input type="number" id="activation_day" name="activation_day" value="{{$casinosetting->activation_day ?? 7}}" class="form-control" required min="1" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="maximum_claim_in_week">@lang('Maximum Calim in week')</label>
                            <input type="number" id="maximum_claim_in_week" name="maximum_claim_in_week" value="{{$casinosetting->maximum_claim_in_week ?? 1}}" min="1" class="form-control" required />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <button class="btn btn-sm btn-primary">Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
