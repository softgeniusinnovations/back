@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-3">
               <form action="{{ route('admin.event.deposit.settings.update', $depositBonus->id) }}" method="POST" enctype="multipart/form-data" class="row">
                @csrf
                  <div class="col-md-2">
                       <div class="form-group">
                           <input type="hidden" name="type" value="deposit">
                           <label for="deposit_percentage">@lang('Deposit Percentage')</label>
                           <input type="number" id="deposit_percentage" name="deposit_percentage" value="{{$depositBonus->deposit_percentage}}" placeholder="10%" class="form-control" required />
                       </div>
                   </div>
                   <div class="col-md-2">
                       <div class="form-group">
                           <label for="bonus_type">@lang('Bonus type')</label>
                           <select id="bonus_type" name="bonus_type" class="form-control bonus_type" required>
                              <option value="providers" @selected(@$depositBonus->bonus_type == 'providers')>Providers</option>
                              <option value="days" @selected(@$depositBonus->bonus_type == 'days')>Days</option>
                           </select>
                       </div>
                   </div>
                   <div class="col-md-4 days" style="display:none">
                      <div class="form-group">
                          <label for="days">@lang('Activation Days')</label>
                          @php
                              // Decode days data, ensure it's always an array
                              $selectedDays = json_decode($depositBonus->days, true) ?? [];
                          @endphp
                          <select id="days" name="days[]" class="form-control select2" multiple="multiple">
                              <option value="Saturday" @if(in_array('Saturday', $selectedDays)) selected @endif>Saturday</option>
                              <option value="Sunday" @if(in_array('Sunday', $selectedDays)) selected @endif>Sunday</option>
                              <option value="Monday" @if(in_array('Monday', $selectedDays)) selected @endif>Monday</option>
                              <option value="Tuesday" @if(in_array('Tuesday', $selectedDays)) selected @endif>Tuesday</option>
                              <option value="Wednesday" @if(in_array('Wednesday', $selectedDays)) selected @endif>Wednesday</option>
                              <option value="Thursday" @if(in_array('Thursday', $selectedDays)) selected @endif>Thursday</option>
                              <option value="Friday" @if(in_array('Friday', $selectedDays)) selected @endif>Friday</option>
                          </select>
                      </div>
                  </div>

                  <div class="col-md-4 providers" style="display:none">
                      <div class="form-group">
                          <label for="providers">@lang('Providers')</label>
                          @php
                              // Decode providers data, ensure it's always an array
                              $selectedProviders = json_decode($depositBonus->providers, true) ?? [];
                          @endphp
                          <select id="providers" name="providers[]" class="form-control select2" multiple="multiple">
                              @foreach($providers as $provider)
                                  <option value="{{ $provider->id }}" @if(in_array($provider->id, $selectedProviders)) selected @endif>
                                      {{ $provider->name }}
                                  </option>
                              @endforeach
                              <option value="cash_agent" @if(in_array('cash_agent', $selectedProviders)) selected @endif>Cash Agent</option>
                          </select>
                      </div>
                  </div>
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="game_type">@lang('Game type')</label>
                           <select id="game_type" name="game_type" class="form-control" required>
                              <option value="sports" @selected(@$depositBonus->valid_type == 'sports') >Sports (Upcoming)</option>
                              <option value="casino" @selected(@$depositBonus->game_type == 'casino') >Casino</option>
                           </select>
                       </div>
                   </div>
                   <div class="col-md-3">
                       <div class="form-group">
                           <label for="wager">@lang('Wager')</label>
                           <input type="number" id="wager" name="wager" value={{$depositBonus->wager}} placeholder="3" class="form-control" required min="1" />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="rollover">@lang('Rollover')</label>
                           <input type="number" id="rollover" name="rollover" value={{$depositBonus->rollover}} placeholder="3" class="form-control" required min="1" />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="minimum_bet">@lang('Minimum bet in multibet')</label>
                           <input type="number" id="minimum_bet" name="minimum_bet" value={{$depositBonus->minimum_bet}} placeholder="3" class="form-control" required min="1" />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="odd_selection">@lang('Minimum odds')</label>
                           <input type="number" id="odd_selection" name="odd_selection" value={{$depositBonus->odd_selection}} class="form-control" required />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="min_bonus">@lang('Minimum bonus amount')</label>
                           <input type="number" id="min_bonus" name="min_bonus" value={{$depositBonus->min_bonus}} class="form-control" required />
                       </div>
                   </div>
                    <div class="col-md-3">
                       <div class="form-group">
                           <label for="max_bonus">@lang('Maximum bonusamount')</label>
                           <input type="number" id="max_bonus" name="max_bonus" value={{$depositBonus->max_bonus}} class="form-control" required />
                       </div>
                   </div>
                   <div class="col-md-2">
                       <div class="form-group">
                           <label for="valid_time">@lang('Playing time')</label>
                           <select id="valid_time" name="valid_time" class="form-control" required>
                              <option value="1"  @selected(@$depositBonus->valid_time == '1')>24 hrs</option>
                              <option value="2"   @selected(@$depositBonus->valid_time == '2')>48 hrs</option>
                              <option value="3"  @selected(@$depositBonus->valid_time == '3')>72 hrs</option>
                              <option value="168"  @selected(@$depositBonus->valid_time == '168')>7 days</option>
                           </select>
                       </div>
                   </div>
                   <div class="col-md-2">
                       <div class="form-group">
                           <label for="maximum_claim_in_day">@lang('Maximum Calim in day')</label>
                           <input type="number" id="maximum_claim_in_day" name="maximum_claim_in_day" value={{$depositBonus->maximum_claim_in_day}} min="1" class="form-control" required />
                       </div>
                   </div>  
                   <div class="col-md-2">
                       <div class="form-group">
                           <label for="status">@lang('Status')</label>
                           <select id="status" name="status" class="form-control" required>
                              <option value="1"  @selected(@$depositBonus->status == '1')>Active</option>
                              <option value="0"   @selected(@$depositBonus->stauts == '0')>InActive</option>
                           </select>
                       </div>
                   </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">@lang('Image')</label>
                            <input type="file" name="file" class="form-control">

                            @if(@$depositBonus->file) <img src="{{ asset('/core/public/storage/bonus/' . @$depositBonus->file) }}" alt="Logo" width="50"> @endif
                        </div>
                    </div>
                   <div class="col-lg-8"></div>
                   <div class="col-md-4 ">
                       <div class="form-group">
                           <button type="submit" class="btn btn-sm btn-primary">Update</button>
                       </div>
                   </div>
               </form>
            </div>
        </div>
    </div>
</div>
@endsection


@push('script')
<script>
    $(document).ready(function(){
        $('.select2').select2({
            placeholder: "---Select---"
        });
        
         function toggleBonusTypeFields() {
              var bonusType = $('#bonus_type').val();
              if (bonusType === 'days') {
                  $('.days').show();
                  $('.providers').attr("required", false);
                  $('.providers').hide();
                  $('.dayjs').attr("required", true);
              } else if (bonusType === 'providers') {
                  $('.providers').show();
                  $('.days').hide();
                  $('.dayjs').attr("required", false);
                  $('.providers').attr("required", true);
              }
          }

          toggleBonusTypeFields();

          $('#bonus_type').on('change', function() {
              toggleBonusTypeFields();
          });

        // $('.bonus_type').on('change', function(){
        //     if(this.value == 'days'){
        //         $('.providers').attr("required", false);
        //         $('.providers').css("display", "none")
        //         $('.days').css("display", "block");
        //          $('.dayjs').attr("required", true);
        //     }else{
        //         $('.providers').css("display", "block")
        //          $('.dayjs').attr("required", false);
        //          $('.providers').attr("required", true);
        //         $('.days').css("display", "none");
        //     }
        // })
    })
</script>
@endpush