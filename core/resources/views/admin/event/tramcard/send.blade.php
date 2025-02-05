@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-3">
               <form action="{{route('admin.event.tramcard.send.user', $tramcard->id)}}" method="POST" class="row">
                   @csrf
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="bettor">@lang('Select Bettor')</label>
                           <select name="bettor" class="form-control select2" required>
                               <option value="">---Select Bettor---</option>
                               @foreach($bettors as $b)
                                <option value="{{$b->id}}">{{$b->user_id}}</option>
                               @endforeach
                           </select>
                       </div>
                   </div>
                   
                  <div class="col-md-4">
                       <div class="form-group">
                           <label for="remark">@lang('Remark')</label>
                           <input id="remark" name="remark" value="{{old('remark')}}" placeholder="remark" class="form-control" />
                       </div>
                   </div>
                   
                   <div class="col-md-4">
                       <div class="form-group">
                           <label for="valid_time">@lang('Card validation time')</label>
                           <select name="valid_time" class="form-control" required>
                                <option>---Select time duration---</option>
                                <option value="1">24Hours</option>
                                <option value="2">48Hours</option>
                                <option value="3">72Hours</option>
                                <option value="7">7 Dyas</option>
                                <option value="14600">Lifetime</option>
                           </select>
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
    
    <div class="col-md-12">
        <div class="table-responsive--md table-responsive">
            <table class="table--light style--two table">
                <thead>
                    <th>@lang('#')</th>
                    <th>@lang('Bettor')</th>
                    <th>@lang('Value')</th>
                    <th>@lang('Currency')</th>
                    <th>@lang('Valid time')</th>
                    <th>@lang('Created Time')</th>
                    <th>@lang('Remarks')</th>
                    <!--<th>@lang('Action')</th>-->
                </thead>
                <tbody>
                    @forelse($activeBettors as $key=>$a)
                        <tr>
                            <td>{{++$key}}</td>
                            <td>{{$a->username}}</td>
                            <td>{{$a->amount}}</td>
                            <td>{{$a->currency}}</td>
                            <td>{{ \Carbon\Carbon::parse($a->valid_time)->format('d M Y') }} <br>
                                    {{ \Carbon\Carbon::parse($a->valid_time)->format('h:i:s A') }}</td>
                            <td>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y') }} <br>
                                    {{ \Carbon\Carbon::parse($a->created_at)->format('h:i:s A') }}</td>
                            <td>{{$a->remarks}}</td>
                            <!--<td>-->
                            <!--    <a href="" class="btn btn-sm btn-danger">Delete</a>-->
                            <!--</td>-->
                        </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
         @if ($activeBettors->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($activeBettors) }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function(){
        $('.select2').select2();
    })
</script>
@endpush
