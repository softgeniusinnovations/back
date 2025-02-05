@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12 text-right">
        <div class=text-right">
            <a href="{{route('admin.event.tramcard.list')}}" class="btn btn-sm btn-primary my-2">Back</a>
        </div>
    </div>
    <div class="card p-3">
        <div class="row">
            <div class="col-md-5">
                <img src="{{ asset('/core/public/storage/event/tramcard/' . $tramcard->image) }}" alt="Photo" style="width: 100%;
                                    height: 100%;
                                    padding: 5px;
                                    border: 1px solid #ddd;
                                    margin-bottom: 5px;
                                    object-fit: cover;" />
            </div>
            
            <div class="col-md-7">
                <h5>Title: {{$tramcard->title}}</h5>
                <h5>Value: {{$tramcard->value}}</h5>
                <h5>TRX: {{$tramcard->trx}}</h5>
                <h5>Currency: {{$tramcard->currency}}</h5>
                <h5>Minimum Multibet: {{$tramcard->minimum_bet}}</h5>
                <h5>Minimum Odds: {{$tramcard->odds}}</h5>
            </div>
        </div>
    </div>
    
</div>
@endsection
@push('script')
<script>
    $(document).ready(function() {
    })

</script>
@endpush
