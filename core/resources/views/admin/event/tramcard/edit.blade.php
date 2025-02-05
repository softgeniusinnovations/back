@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-3">
               <form action="{{route('admin.event.tramcard.update', $tramcard->id)}}" method="POST" class="row" enctype="multipart/form-data">
                   @csrf
                   @method('PUT')
                  <div class="col-md-4">
                       <div class="form-group">
                           <label for="title">@lang('Title')</label>
                           <input id="title" name="title" value="{{$tramcard->title}}" placeholder="Title" class="form-control" required />
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="value">@lang('Value')</label>
                           <input type="number" id="value" name="value" value="{{$tramcard->value}}" placeholder="Value" class="form-control" min="0" required />
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="currency">@lang('Currency')</label>
                           <select name="currency" class="form-control selecte2">
                               <option value="">---Select currency---</option>
                               @foreach($currency as $c)
                                <option value="{{$c->currency_code}}" @selected($c->currency_code == $tramcard->currency)>{{$c->currency_code}}</option>
                               @endforeach
                           </select>
                       </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                            <label class="form-label">@lang('Image')</label>
                            <input type="file" name="file" class="form-control">
                            <img src="{{ asset('/core/public/storage/event/tramcard/' . $tramcard->image) }}" alt="Photo" style="width: 100%;
                                    height: 60px;
                                    padding: 5px;
                                    border: 1px solid #ddd;
                                    margin-bottom: 5px;
                                    object-fit: cover;" />
                        </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                            <label class="form-label">@lang('Minimum bet in multibet')</label>
                            <input type="number" name="minimum_bet" class="form-control" required min="1" value="{{$tramcard->minimum_bet}}">
                        </div>
                   </div>
                   <div class="col-md-4">
                       <div class="form-group">
                            <label class="form-label">@lang('Minimum Odds')</label>
                            <input  name="odds" class="form-control" required min="1" value="{{$tramcard->odds}}">
                        </div>
                   </div>
                   
                   <div class="col-md-12">
                       <div class="form-group">
                            <label class="form-label">@lang('Rules')</label>
                            <textarea class="form-control nicEdit" rows="3" cols="3" name="rules">{{$tramcard->rules}}<br /></textarea>
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
