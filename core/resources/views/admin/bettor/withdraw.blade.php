@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-6">
            <div class="card b-radius--10">
                <div class="card-header">
                    Bettor Withdraw Form
                </div>
                <div class="card-body p-3">
                    <form action={{route('admin.agent.make.bettor.withdraw')}} method="POST">
                        @method('POST')
                        @csrf
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('User ID')</label>
                                    <input type="text" name="bettor" id="bettor" class="form-control" required value="{{old('bettor')}}" />
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Amount') ({{auth()->user()->currency}})</label>
                                    <input type="number" name="amount" class="form-control" required min="300" max="20000" value="{{old('amount')}}" /> 
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-sm">@lang('Make Withdraw')</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                    
            </div>
        </div>
        <div class="col-lg-6 ">
            <div class="card">
                <div class="card-header">Bettor Information</div>
                <div class="card-body">
                    <div class="loading-indicator"></div>
                    <ul class="bettor-item-list list-group">
                        
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {
            $('#bettor').on('keyup', function(){
                var bettor_id = $(this).val();
                if(bettor_id?.length > 5){
                    $('.loading-indicator').text('Loading...');
                    $.ajax({
                        url: "{{route('admin.search.bettor')}}",
                        type: 'GET',
                        data: {
                            bettor_id : bettor_id,
                            "_token" : "{{csrf_token()}}",
                        },
                        success: function(response){
                            $('.bettor-item-list').empty();
                            
                           if(response.length > 0) {
                                var bettor = response[0];
                                var listItem = `
                                    <li class="list-group-item"> User ID : ${bettor.user_id}</li>
                                    <li class="list-group-item"> Name : ${bettor.firstname} ${bettor.lastname}</li>
                                    <li class="list-group-item"> Username : ${bettor.username}</li>
                                    <li class="list-group-item"> Country : ${bettor.country_code}</li>`;
                                $('.bettor-item-list').append(listItem);
                            } else {
                                // Handle the case where no item is found
                                var listItem = '<li class="list-group-item">No bettor found</li>';
                                $('.bettor-item-list').append(listItem);
                            }
                            $('.loading-indicator').text('');
                        }
                    })
                }else{
                    $('.bettor-item-list').empty();
                    $('.loading-indicator').text('No Data found! Type at least 6 char.');
                }
            })
        })(jQuery);
    </script>
@endpush