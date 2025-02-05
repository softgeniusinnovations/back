@extends($activeTemplate . 'layouts.bet')
@section('bet')
<section>
    <div class="card">
        <div class="card-body">
            <h5 class="p-0 m-0">{{$event->title}}</h5>
            <div class="row">
                <div class="col-md-4">
                    <img src="{{ asset('assets/news/' . $event->image) }}" alt="{{$event->title}}" class="img-fluid">
                </div>

                <div class="col-md-8">
                    <p class="p-0 m-0 text-dark"><b>{{$event->title}}</b></p>
                    <p class="p-0 m-0 text-dark">{!! $event->description !!}</p>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="p-0 m-0 text-dark"><b>{{__('Start Date')}}:</b> {{date('d M, Y', strtotime($event->start_date))}}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="p-0 m-0 text-dark"><b>{{__('End Date')}}:</b> {{date('d M, Y', strtotime($event->end_date))}}</p>
                        </div>
                    </div>
                    <div class="mt-2 float-right">
                    <a href="{{ auth()->user() ? route('user.deposit.index', ['id' => encrypt($event->id)]) : route('login') }}" class="btn btn-primary">{{__('Deposit Now')}}</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
