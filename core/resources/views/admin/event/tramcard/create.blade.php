@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-3">
               <form action="{{route('admin.event.tramcard.store')}}" method="POST" class="row" enctype="multipart/form-data">
                   @csrf
                  <div class="col-md-4">
                       <div class="form-group">
                           <label for="title">@lang('Title')</label>
                           <input id="title" name="title" value="{{old('title')}}" placeholder="Title" class="form-control" required />
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="value">@lang('Value')</label>
                           <input type="number" id="value" name="value" value="{{old('value')}}" placeholder="Value" class="form-control" min="0" required />
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="currency">@lang('Currency')</label>
                           <select name="currency" class="form-control selecte2">
                               <option value="">---Select currency---</option>
                               @foreach($currency as $c)
                                <option value="{{$c->currency_code}}" @selected($c->currency_code == old('currency'))>{{$c->currency_code}}</option>
                               @endforeach
                           </select>
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                            <label class="form-label">@lang('Image')</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                            <label class="form-label">@lang('Minimum bet in multibet')</label>
                            <input type="number" name="minimum_bet" class="form-control" required min="1" value="3">
                        </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                            <label class="form-label">@lang('Minimum Odds')</label>
                            <input  name="odds" class="form-control" required min="1" value="1.8">
                        </div>
                   </div>
                   <div class="col-md-12">
                       <div class="form-group">
                            <label class="form-label">@lang('Rules')</label>
                            <textarea class="form-control nicEdit" rows="3" cols="3" name="rules">Player can only use tram card in upcoming sport <br/>If player use a tram card, multibet stake value will be same as card
value <br/>If player win that multibet then initial card value will credited to
winning funds<br />Each multibet bet must contain at least three selections. Each
selection of multibet must have odds of 1.80 or higher.<br /></textarea>
                        </div>
                   </div>
                   
                   <div class="col-md-4">
                       <div class="form-group">
                           <button class="btn btn-sm btn-primary">Submit</button>
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
    $(document).ready(function() {
       $('.selecte2').select2();
       bkLib.onDomLoaded(function() {
            $(".nicEdit").each(function(index) {
                $(this).attr("id", "nicEditor" + index);
                new nicEditor({
                    fullPanel: true
                }).panelInstance('nicEditor' + index, {
                    hasPanel: true
                });
            });
        })
    })

</script>
@endpush
