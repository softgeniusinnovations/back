@extends($activeTemplate . 'layouts.master')
@section('master')

<div class="row">
    @if($tramcard)
        <div class="col-md-12">
            <p class="alert alert-success">Congratulations! You got a tramcard, Enjoy the game.</p>
            
            <div class="d-flex gap-2">
                <div>
                    <img src="{{ asset('/core/public/storage/event/tramcard/' . $tramcard->tramcard->image) }}" alt="Photo" style="width: 400px;
                                    min-height: 205px;
                                    padding: 5px;
                                    border: 1px solid #ddd;
                                    margin-bottom: 5px;
                                    object-fit: cover;" />
                </div>
                <div class="alert-danger px-3"  style="font-size:13px; margin-bottom:6px; width: 100%">
                    <h5>Rules</h5>
                    {!!$tramcard->tramcard->rules!!}
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <div style="width: 220px; height: 200px; background: #f2f2f2; border:1px solid #ddd; padding:10px; text-align:center">
                    <p style="font-weight: 800; font-size: 14px; color: #000; margin:0">Balance: {{$tramcard->amount}} {{$tramcard->currency}}</p>
                    <p style="font-weight: 800; font-size: 12px; color: #000; margin:0">Valid: {{$tramcard->duration_text}} </p>
                    @if($tramcard->duration_text != 'Life time')
                    <p style="font-weight: 800; font-size: 12px; color: #000; margin:0">Date:{{$tramcard->valid_time}} </p>
                    @endif
                    @if($tramcard->rule_1 && $tramcard->rule_2 && $tramcard->rule_3 && $tramcard->rule_4 && $tramcard->is_win)
                    <a href="{{route('user.tram.card.claim')}}" class="btn btn-sm btn-primary mt-2">Claim ({{$tramcard->amount}} {{$tramcard->currency}})</a>
                    @endif
                </div>
                <div style="width: 100%; height: auto; border:1px solid #ddd; padding:10px;">
                    <p class="m-0 mb-2">You claim the tramcard amount when you have completed 100% progressbar and passed the all rules</p>
                    <div class="progress">
                        @php
                            $rules = ['rule_1', 'rule_2', 'rule_3', 'rule_4'];
                            $progressBarValue = array_reduce($rules, function ($carry, $rule) use ($tramcard) {
                                return $carry + ($tramcard->$rule ? 1 : 0);
                            }, 0);
                        @endphp
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="{{$progressBarValue * 25}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$progressBarValue * 25}}%">{{$progressBarValue * 25}}%</div>
                    </div>

                    
                    <ul class="my-2" style="font-size:13px">
                        <li style="color:{{$tramcard->rule_1 ? 'green' : 'red'}}">Player can only use tram card in upcoming sport.</li>
                        <li style="color:{{$tramcard->rule_2 ? 'green' : 'red'}}">If player use a tram card, multibet stake value will be same as card value.</li>
                        <li style="color:{{$tramcard->rule_3 ? 'green' : 'red'}}">Each multibet bet must contain at least {{$tramcard->tramcard->minimum_bet}} selections.</li>
                        <li style="color:{{$tramcard->rule_4 ? 'green' : 'red'}}">Each selection of multibet must have odds of {{$tramcard->tramcard->odds}} or higher.</li>
                    </ul>

                </div>
            </div>
        </div>
    @else
        <div class="col-md-12">
            <p class="alert alert-danger">No tramcard found.</p>
        </div>
    @endif
    
    
</div>
@endsection
