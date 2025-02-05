@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30 justify-content-center">
        <div class="col-xl-6 col-md-6 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">@lang('Details') {{ $agent->username }} </h5>
                    <div>
                        
                        @if($agent->file)
                            <img src="{{ asset('/core/public/storage/agent/photo/' . $agent->file) }}" alt="Photo" style="width: 150px;
                                height: 149px;
                                padding: 5px;
                                border: 1px solid #ddd;
                                margin-bottom: 5px;
                                object-fit: cover;" />
                        @else
                        <img src="https://via.placeholder.com/150x149" alt="Photo" style="width: 150px;
                                height: 149px;
                                padding: 5px;
                                border: 1px solid #ddd;
                                margin-bottom: 5px;
                                object-fit: cover;" />
                        @endif
                        
                    </div>
                    
                    <ul class="list-group">
                         <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Agent No')
                            <span class="fw-bold">{{ $agent->identity }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Name')
                            <span class="fw-bold">{{ $agent->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span class="fw-bold">{{ $agent->username }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Email')
                            <span class="fw-bold">{{ $agent->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Country')
                            <span class="fw-bold">{{ $agent->country_code }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Phone')
                            <span class="fw-bold">{{ $agent->phone }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Deposit commission')
                            <span class="fw-bold">{{ $agent->deposit_commission }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Withdraw commission')
                            <span class="fw-bold">{{ $agent->withdraw_commission }}%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Balance')
                            <span class="fw-bold">{{$agent->currency .' '. number_format($agent->balance,2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Bot token')
                            <span class="fw-bold">{{$agent->bot_token }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Bot username')
                            <span class="fw-bold">{{$agent->bot_username }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Chnnel ID')
                            <span class="fw-bold">{{$agent->channel_id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Telegram link')
                            <span class="fw-bold">{{$agent->telegram_link }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Logged In')
                            <span class="fw-bold">{{$agent->is_login == 1 ? 'Yes' : 'No' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            <span class="fw-bold">{{$agent->status == 1 ? 'Active' : 'Inactive' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
       
        <div class="col-xl-6 col-md-6 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title mb-10 border-bottom pb-2">@lang('Agent Information')</h5>
                        <div class="row">
                            <div class="col-md-12">
                                @if($agent->type == 1)
                                 <ul class="list-group mt-0">
                                     <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary">Transaction Providers</li>
                                    @foreach(@$agent->transectionProviders as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{@$item->pivot->wallet_name}}
                                            <span class="fw-bold">{{@$item->pivot->mobile }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                @endif
                                
                                @if($agent->type == 2 || $agent->type =3)
                                 <ul class="list-group mt-0">
                                     <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary">Address</li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Address')
                                            <span class="fw-bold">{{@$agent->address }}</span>
                                        </li>
                                </ul>
                                @endif
                                 <ul class="list-group mt-2">
                                     <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary">Bettor Deposit</li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Pending Deposit')
                                            <span class="fw-bold">{{number_format(@$agent->deposit(1)->sum('final_amo'), 2)}}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Approved Deposit')
                                            <span class="fw-bold">{{number_format(@$agent->deposit(2)->sum('final_amo'), 2)}}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Rejected Deposit')
                                            <span class="fw-bold">{{number_format(@$agent->deposit(3)->sum('final_amo'), 2)}}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Total Deposit')
                                            <span class="fw-bold">{{number_format(@$agent->deposit->sum('final_amo'), 2)}}</span>
                                        </li>
                                </ul>
                                
                                <ul class="list-group mt-2">
                                     <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary">Bettor Withdraw</li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Pending Withdraw')
                                            <span class="fw-bold">{{number_format(@$agent->withdraw(1)->sum('final_amount'), 2)}}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Approved Withdraw')
                                            <span class="fw-bold">{{number_format(@$agent->withdraw(2)->sum('final_amount'), 2)}}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Rejected Withdraw')
                                            <span class="fw-bold">{{number_format(@$agent->withdraw(3)->sum('final_amount'), 2)}}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Total Withdraw')
                                            <span class="fw-bold">{{number_format(@$agent->withdraw->sum('final_amount'), 2)}}</span>
                                        </li>
                                </ul>
                                
                                <ul class="list-group mt-2">
                                     <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary">Self Deposit</li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            @lang('Total Deposit')
                                            <span class="fw-bold">{{number_format(@$agent->agentDeposit()->sum('amount'), 2) . ' '.$agent->currency}}</span>
                                        </li>
                                </ul>
                            </div>
                        </div>
                </div>
            </div>
        </div>
                
    </div>
@endsection
