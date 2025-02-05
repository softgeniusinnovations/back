@extends($activeTemplate . 'layouts.master')
@section('master')

<div class="row">
    @if($activeBonus)
        <div class="col-md-12">
            <p class="alert alert-success">Congratulations! You have an active {{$activeBonus->type}} bonus </p>
            
            <div class="d-flex gap-2">
                <div class="alert-danger px-3"  style="font-size:13px; margin-bottom:6px; width: 100%">
                    <h5>Rules</h5>
                    <ul>
                        <li>Player can only use this amount in upcoming sport.</li>
                        <li>Player use this bonus only for multibet.</li>
                        <li>Each multibet bet must contain at least 3 selections.</li>
                        <li>Each selection of multibet must have odds of 1.8 or higher.</li>
                        <li>Minimum rollover 3 times</li>
                    </ul>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <div style="width: 260px; height: 200px; background: #f2f2f2; border:1px solid #ddd; padding:10px; text-align:center">
                    <p style="font-weight: 800; font-size: 14px; color: #000; margin:0">Initial Balance: {{showAmount($activeBonus->initial_amount)}} {{$activeBonus->currency}}</p>
                    <p style="font-weight: 800; font-size: 12px; color: #000; margin:0">Valid: {{$activeBonus->duration_text}} </p>
                    @if($activeBonus->duration_text != 'Life time')
                    <p style="font-weight: 800; font-size: 12px; color: #000; margin:0">Date:{{$activeBonus->valid_time}} </p>
                    @endif
                    @if($activeBonus->rule_1 && $activeBonus->rule_2 && $activeBonus->rule_3 && $activeBonus->rule_4 && $activeBonus->rule_5 && auth()->user()->bonus_account > 0)
                    <a href="{{route('user.bonus.claim')}}" class="btn btn-sm btn-primary mt-2">Claim ({{showAmount($activeBonus->initial_amount)}} {{$activeBonus->currency}})</a>
                    @endif
                </div>
                <div style="width: 100%; height: auto; border:1px solid #ddd; padding:10px;">
                    <p class="m-0 mb-2">You claim the tramcard amount when you have completed 100% progressbar and passed the all rules</p>
                    <div class="progress">
                        @php
                            $rules = ['rule_1', 'rule_2', 'rule_3', 'rule_4','rule_5'];
                            $progressBarValue = array_reduce($rules, function ($carry, $rule) use ($activeBonus) {
                                return $carry + ($activeBonus->$rule ? 1 : 0);
                            }, 0);
                        @endphp
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="{{$progressBarValue * 20}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$progressBarValue * 20}}%">{{$progressBarValue * 20}}%</div>
                    </div>

                    
                    <ul class="my-2" style="font-size:13px">
                        <li style="color:{{$activeBonus->rule_1 ? 'green' : 'red'}}">Player can only use this amount in upcoming sport.</li>
                        <li style="color:{{$activeBonus->rule_2 ? 'green' : 'red'}}">Player use this bonus only for multibet.</li>
                        <li style="color:{{$activeBonus->rule_3 ? 'green' : 'red'}}">Each multibet bet must contain at least 3 selections.</li>
                        <li style="color:{{$activeBonus->rule_4 ? 'green' : 'red'}}">Each selection of multibet must have odds of 1.8 or higher.</li>
                        <li style="color:{{$activeBonus->rule_5 ? 'green' : 'red'}}">Minimum rollover 3.</li>
                    </ul>

                </div>
            </div>
        </div>
    @else
        <div class="col-md-12">
            <p class="alert alert-danger">No active bonus found.</p>
        </div>
    @endif
    
    <div class="col-md-12">
        <h5>Waiting for active referral tramcard bonus</h5>
        <hr/>
        <ul class=" p-0 mb-2">
            @forelse($referralUsers as $key=>$user)
            <li style="width: 100%;
  display: flex;
  align-items: center;
  gap: 43px;
  font-size: 14px;
  background: #f2f2f2;
  padding: 8px;
  border-bottom: 1px solid #ddd;"><span>{{++$key}}). </span> Congratulations on receiving your referral bonus, Enjoy your referral bonus of  {{$user->username}} bettor. Happy betting ahead! <a href="{{route('user.bonus.referral.claim', $user->id)}}" class="btn-sm btn-primary">Claim Now</a></li>
            @empty
            <li>No referrar found right now</li>
            @endforelse
        </ul>
        {{ $referralUsers->links() }}
    </div>
    
    
</div>
@endsection
