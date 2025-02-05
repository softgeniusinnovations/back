@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-4 mx-auto">
            <div class="card b-radius--10">
                <div class="card-body p-4">
                    <form action="{{route('admin.agent.threshold', $threshold->id)}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <label for="name">Threshold name</label>
                                <input name="name" id="name" value="{{$threshold->name}}" class="form-control" placeholder="Threshold name" required />
                            </div>
                            <div class="col-md-12">
                                <label for="amount">Threshold amount</label>
                                <input type="number" name="amount" id="amount" value="{{$threshold->amount}}" class="form-control" placeholder="Threshold amount" min="0" />
                            </div>
                             <div class="col-md-12">
                                <label for="currency">Threshold currency</label>
                                <select name="currency" id="currency"  class="form-control">
                                    <option>---Select currency---</option>
                                    @foreach($currency as $c)
                                    <option value="{{$c->currency_code}}" @selected($threshold->currency == $c->currency_code)>{{$c->currency_code}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-12 mt-2">
                                <button class="btn btn-sm btn-primary">Update</button>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

