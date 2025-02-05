@extends($activeTemplate . 'layouts.master')
@section('master')

<div class="card custom--card"">
    <h5 class=" card-header">
    <i class="las la-ticket-alt"></i>
    @lang('Events')
    </h5>
</div>

<div class="pt-4">
    <div class="row">
        @foreach($news as $key => $item)
        <div class="col-md-3 mb-3">
            <a href="{{ route('user.deposit.index', ['id' => encrypt($item->id)]) }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm" style="background: transparent">
                    <div class="card-body" style="background-image: url('{{ asset('assets/news/' . $item->image) }}'); background-size: cover;">
                        <div style="height: 120px"></div>
                    </div>
                    <div class="card-footer bg-dark border-0 m-0">
                        <p class="m-0 p-0
                        text-white">{{ __($item->title) }}</p>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
