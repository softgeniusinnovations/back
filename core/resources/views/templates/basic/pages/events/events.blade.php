@extends($activeTemplate . 'layouts.bet')
@section('bet')
<section class="pt-4">
    <div class="col-12">
        <div class="container">
            <div class="bonus-carousel-wrapper container">
                <h4 class="text-dark">Permanent bonuses</h4>
                <div class="bonus-carousel">
                    @foreach($news as $key => $item)

                    {{-- <a href="{{ route('event.details', ['id' => encrypt($item->id)]) }}" class="bonus-card-link" style="box-shadow: rgb(98, 97, 245) 0px 26px 80px -20px;"> --}}
                    <a href="javascript:void(0)" data-id="{{ $item->id }}" class="bonus-card-link myLink" id="myLink" style="box-shadow: rgb(98, 97, 245) 0px 26px 80px -20px;">
                        <div style="background-image: url('{{ asset('assets/news/' . $item->image) }}');" class="BonusCard_root">
                            <div class="BonusCard_container BonusCard_Qmtck">
                                <div class="BonusCard_title">
                                    <div class="title-s"> {{$item->title}}</div>
                                </div>
                                <div class="BonusCard_title">
                                    <div class="title-m">+ {{$item->bonus_percentage}}%
                                    </div>
                                </div>
                                <div class="BonusCard_subtitle">
                                    <div class="subtitle">{{$item->sub_title}}
                                    </div>
                                </div>
                            </div>
                            <div class="BonusCard_arrow">
                                <i class="fas fa-arrow-right BonusCard_logo"></i>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<div id="myModal" class="modal_1">
    <!-- Modal content -->
    <div class="modal-content_1">
        <span class="close">&times;</span>
        <div class="row">
            <div class="col-sm-2"></div>
            <div class="col-sm-8">
                <div>
                    <h4 class="heading text-center"></h4>
                    <h6 class="sub_title text-center"></h6>
                    <p class="details"></p>
                </div>
            </div>
            <div class="col-sm-2"></div>
        </div>
    </div>
</div>
@endsection
@push('script')
<script>
    (function($) {
        "use strict";

        $(document).ready(function() {
            var modal = $(".modal_1");
            var link = $(".myLink");
            var span = $(".close");
            link.click(function(event) {
                event.preventDefault();
                var id = $(this).data('id');
                var url = `{{ URL::route('event.details', ':id') }}`;
                url = url.replace(':id', id);

                $.ajax({
                    url: url
                    , type: 'GET'
                    , dataType: 'json'
                    , success: function(data) {
                        $('.heading').html(data.title);
                        $('.heading').css({
                            'background-image': 'url(' + '{{ asset('/assets/news/') }}' + '/'+ data.image + ')',
                            'background-repeat': 'no-repeat',
                            'background-size': 'cover',
                            'height': '200px',
                            'width': '100%',
                            'border-radius': '10px',
                            'text-align': 'center',
                            'display': 'flex',
                            'justify-content': 'center',
                            'align-items': 'center',
                            'color': 'white',
                            'background-color': '#201658',
                        });
                        $('.sub_title').html(data.sub_title);
                        $('.details').html(data.description);
                    }
                });
                modal.show();
            });
            span.click(function() {
                modal.hide();
                $('.heading').html('');
                $('.sub_title').html('');
                $('.details').html('');

            });

            $(window).click(function(event) {
                if (event.target == modal[0]) {
                    modal.hide();
                    $('.heading').html('');
                    $('.sub_title').html('');
                    $('.details').html('');
                }
            });
        });
    })(jQuery);

</script>
@endpush
