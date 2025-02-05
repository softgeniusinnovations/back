@extends($activeTemplate . 'layouts.bet')
@section('bet')
<section>
    <div class="row">
        <div class="col-md-3">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                @foreach ($categories as $category)
                <div class="accordion-item">
                    <p class="accordion-header" id="flush-heading{{$category->id}}">
                        <button class="accordion-button collapsed py-1" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse{{$category->id}}" aria-expanded="false" aria-controls="flush-collapse{{$category->id}}">
                            <div class="sp-s-l-head-bc">
                                <div>
                                    <span class="sports-category__icon">
                                        @php echo $category->icon @endphp
                                    </span> {{ strLimit(__($category->name), 20) }}</div>
                                <div class="text-right">
                                    <small class="badge rounded-pill bg-info">
                                        {{ App\Models\League::active()->where("category_id", $category->id)->count() }}
                                    </small>
                                </div>
                            </div>
                        </button>

                    </p>
                    <div id="flush-collapse{{$category->id}}" class="accordion-collapse collapse" aria-labelledby="flush-heading{{$category->id}}" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body game-color-1">
                            <div class="accordion " id="accordionExample">
                                @foreach(App\Models\League::active()->where("category_id", $category->id)->get() as $key => $item)
                                <div class="accordion-item{{$key}}">
                                    <p class="accordion-header" id="heading{{$key}}">
                                        <button class="accordion-button p-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$key}}" aria-expanded="true" aria-controls="collapse{{$key}}">

                                            <div class="sp-s-l-head-bc">
                                                <div>
                                                    {{ $item->short_name }}</div>
                                                <div class="text-right">
                                                    <small class="badge rounded-pill bg-info">
                                                        {{ App\Models\Game::active()->running()->where('league_id', $item->id)->count() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </button>
                                    </p>
                                    <div id="collapse{{$key}}" class="accordion-collapse collapse show" aria-labelledby="heading{{$key}}" data-bs-parent="#accordionExample{{$key}}">
                                        <div class="accordion-body p-0">
                                            @php
                                            $datas = App\Models\Game::active()->running()->where('league_id', $item->id)->get();
                                            @endphp
                                            @foreach ($datas as $data)
                                            <div class="card">
                                                <div class="card-body">

                                                </div>
                                            </div>
                                            @empty
                                                <div></div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
@push('style')
<style>
    .sp-s-l-head-bc {
        width: 100%;
        transition: background .24s;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

</style>
@endpush
