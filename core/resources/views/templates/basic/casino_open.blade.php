@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="col-lg-12">
    <iframe id="inlineFrameExample" title="Casino Game" width="100%" height="500" src="{{$src}}"></iframe>
</div>
@endsection

@push('script')
<script>
    
    window.onmessage=function(event) {
        if (event.data=='closeGame' || event.data=='close' ||
        event.data=='notifyCloseContainer' || (event.data.indexOf &&
        event.data.indexOf("GAME_MODE:LOBBY")>=0)) {
            console.log("hello");
            closeGame ();
        }
    }

    
</script>
@endpush