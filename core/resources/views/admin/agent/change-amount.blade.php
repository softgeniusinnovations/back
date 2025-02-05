@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-6 mx-auto">
            <div class="card b-radius--10">
                <div class="card-body p-4">
                    <form action="{{route('admin.agent.changed.amount', $agent->id)}}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <p class="alert alert-danger p-2">
                                   Be careful! Now you will change the amount for {{ $agent->username }} ({{ $agent->identity }}). 
                                   Now the ({{ $agent->identity }}) agent have {{showAmount($agent->balance)}} {{$agent->currency}}
                                </p>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Amount')</label>
                                    <input class="form-control form--control" name="amount" type="number" required min="0">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Type')</label>
                                    <select class="form-control" name="type" required>
                                        <option value="">---select the option---</option>
                                        <option value="+">Addition</option>
                                        <option value="-">Substruction</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Remarks')</label>
                                    <input name="remark" class="form-control" placeholder="Remarks" />
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <button class="btn btn--primary" type="submit">@lang('Change Amount')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

