@extends($activeTemplate . 'layouts.bet')
@section('bet')
    @php
        $banners = getContent('banner.element', false, null, true);
        $optionsId = collect(session()->get('bets'))
            ->pluck('option_id')
            ->toArray();
    @endphp

    <div class="col-12">
        <div class="banner-slider hero-slider mb-3">
            @foreach ($banners as $banner)
                <div class="banner_slide">
                    <img class="banner_image" src="{{ getImage('assets/images/frontend/banner/' . @$banner->data_values->image, '1610x450') }}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-12">
        <div class="betting-body">
            <strong>Live games</strong>
            <div class="row g-3 game-sliders" >
                @php
                    $user = auth()->user();
                @endphp
                @foreach ($liveGames as $game)
                    <div class="col-sm-4">
                        <a href="{{ $user ? route('game.markets', $game->slug) : route('user.login') }}" style="width: 100%;height: 100%">
                            <div class="sports-card position-relative">
                            <span class="sports-card__head">
                                <span class="sports-card__team">
                                    <span class="sports-card__team-flag">
                                        <img class="sports-card__team-flag-img" src="{{ @$game->teamOne->teamImage() }}" alt="@lang('image')">
                                    </span>
                                    <span class="sports-card__team-name">
                                        {{ __(@$game->teamOne->name) }}
                                    </span>
                                </span>

                                @if ($game->isRunning)
                                    <span class="sports-card__info text-center">
                                        <span class="sports-card__stream">
                                            <i class="fa-regular fa-circle-play text--danger"></i>
                                        </span>
                                        <span class="sports-card__info-text">@lang('Live Now')</span>
                                        <span class="sports-card__info-time">{{ carbonParse($game->bet_start_time, 'd M, h:i') }}</span>
                                    </span>
                                @else
                                    <span class="sports-card__info text-center">
                                        <span class="sports-card__stream">
                                            <i class="fa-regular fa-circle-play"></i>
                                        </span>

                                        <span class="sports-card__info-text">@lang('Starts On')</span>
                                        <span class="sports-card__info-time">{{ carbonParse($game->bet_start_time, 'd M, h:i') }}</span>
                                    </span>
                                @endif

                                <span class="sports-card__team">
                                    <span class="sports-card__team-flag">
                                        <img class="sports-card__team-flag-img" src="{{ @$game->teamTwo->teamImage() }}" alt="@lang('image')">
                                    </span>
                                    <span class="sports-card__team-name">
                                        {{ __(@$game->teamTwo->name) }}
                                    </span>
                                </span>
                            </span>
                        </a>
                            @if ($game->questions->count())
                                @php
                                    $firstMarket = $game->questions->first();
                                    $showCount = 4;
                                    $more = $game->questions->count() - $showCount;
                                @endphp

                                <div class="custom-dropdown">
                                    <div class="d-flex justify-content-between">
                                        <span class="custom-dropdown-selected">{{ $firstMarket->title }}</span>
                                        <!--<a href="{{ route('game.markets', $game->slug) }}" class="text--small">@lang('Markets')</a>-->
                                    </div>

                                    <div class="custom-dropdown-list">
                                        @foreach ($game->questions->take($showCount) as $question)
                                            <div class="custom-dropdown-list-item @if ($firstMarket->id == $question->id) disabled @endif @if ($question->locked) locked @endif" data-reference="{{ $question->id }}">{{ $question->title }}</div>
                                        @endforeach

                                        @if ($more > 0)
                                            <div class="text-center mt-1">
                                                <!-- <a href="{{ route('game.markets', $game->slug) }}?more={{ $more }}" class="text--small"> +{{ $more }} @lang('More')</a> -->
                                                <a href="{{ $user ? route('game.markets', $game->slug) : route('user.login') }}?more={{ $more }}" class="text--small"> +{{ $more }} @lang('More')</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="option-odd-list">
                                    @foreach ($firstMarket->options as $option)
                                        <div class="option-odd-list__item just-open-betslip">
                                            <div>
                                            <button class="btn btn-sm btn-light text--small border oddBtn @if (in_array($option->id, $optionsId)) active @endif @if ($option->locked) locked @endif" data-option_id="{{ $option->id }}" @disabled($game->bet_start_time >= now())>{{ rateData($option->odds) }} </button>
                                                {{-- @auth
                                                <button class="btn btn-sm btn-light text--small border oddBtn @if (in_array($option->id, $optionsId)) active @endif @if ($option->locked) locked @endif" data-option_id="{{ $option->id }}" @disabled($game->bet_start_time >= now())>{{ rateData($option->odds) }} </button>
                                                @endauth
                                                @guest
                                                    <a href="{{ route('user.login') }}">
                                                        <button class="btn btn-sm btn-light text--small border oddBtn disable">{{ rateData($option->odds) }} </button>
                                                    </a>
                                                @endguest --}}
                                            
                                                <span class="text--extra-small d-block text-center">{{ $option->name }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if (blank($games))
                <div class="empty-message mt-3">
                    <img class="img-fluid" src="{{ asset($activeTemplateTrue . '/images/empty_message.png') }}" alt="@lang('image')">
                    <p>@lang('No game available in this category')</p>

                </div>
            @endif
        </div>
    </div>

        <!-- Show modal if one_time_pass is not null -->
        <input class="d-none one_time_pass" id="one_time_pass" data-id="{{ optional(auth()->user())->id }}"
        value="{{ optional(auth()->user())->one_time_pass }}">
    <div class="modal oneTimePass fade" id="exampleModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">User Information</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>UserId: <input type="text" class="border-0" id="userName" readonly
                            value="{{ optional(auth()->user())->username }}"></h6>
                    <h6>Password:
                        <input type="text" class="border-0" readonly id="passwords"
                            value="{{ optional(auth()->user())->one_time_pass }}">
                    </h6>

                    <small class="text-danger">Please Save UserId and Password</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary copyBtn">Copy</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css"> 
        
@endpush

@push('script')

<script>
    function redirectTo(url) {
        if (url !== '#') {
            window.location.href = url;
        }
    }
    </script>
    <script>
        (function($) {
            "use strict";

            $(".banner-slider").stepCycle({
                autoAdvance: true,
                transitionTime: 1,
                displayTime: 5,
                transition: "zoomIn",
                easing: "linear",
                childSelector: false,
                ie8CheckSelector: ".ltie9",
                showNav: false,
                transitionBegin: function() {},
                transitionComplete: function() {},
            });

            function controlSliderHeight() {
                let width = $(".banner-slider")[0].clientWidth;
                let height = (width / 35) * 15;
                // $(".banner-slider").css({
                //     height: height,
                // });

                // $(".banner_image").css({
                //     height: height,
                // });
            }

            controlSliderHeight();


            $('.custom-dropdown-selected').click(function() {
                $(this).parents('.custom-dropdown').toggleClass('show');
            });

            $(window).scroll(function() {
                $('.custom-dropdown.show').toggleClass('show');
            });




            $('.custom-dropdown').mouseleave(function() {
                $(this).removeClass('show');
            });

            $('.custom-dropdown-list-item').on('click', function() {
                let parent = $(this).parents('.custom-dropdown');
                let selected = parent.find('.custom-dropdown-selected');
                parent.find('.custom-dropdown-list-item.disabled').removeClass('disabled');
                $(this).addClass('disabled');
                $(selected).text($(this).text());
                parent.removeClass('show');

                getOdds($(this).data('reference'), function(data) {
                    parent.siblings('.option-odd-list').slick('unslick');
                    parent.siblings('.option-odd-list').html(data);
                    initOddsSlider(parent.siblings('.option-odd-list'));
                });

            });

            function getOdds(id, callback) {
                $.get(`{{ route('market.odds', '') }}/${id}`,
                    function(data) {
                        callback(data);
                    }
                );
            }

        })(jQuery);
    </script>

<script>
    $(document).ready(function() {
        var one_time_pass = $('#one_time_pass').val();
        if (one_time_pass != null && one_time_pass != '') {
            $('.oneTimePass').modal('show');
            var id = $('.one_time_pass').data('id');
            $.ajax({
                url: "{{ route('user.one.time.pass') }}",
                method: "POST",
                data: {
                    id: id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    console.log(response);
                }
            });
        } else {
            $('.oneTimePass').modal('hide');
        }
    });
</script>
<script>
    $(document).ready(function() {
        $('.copyBtn').on('click', function() {
            var username = $("#userName").val();
            var password = $("#passwords").val();
            var copyText = "UserId : " + username + "\n" + "Password : " + password;
            navigator.clipboard.writeText(copyText);
            iziToast.success({
                message: "Copied: " + copyText,
                position: "topRight"
            });
        });
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<script>
    $(document).ready(function(){
        $('.owl-carousel').owlCarousel({
            loop:true,
            autoplay: true,
    		autoplayTimeout: 5000,
    		autoplayHoverPause: true,
    		dots: false,
    		nav:false,
    		margin: 10,
    		responsiveBaseElement: 'body',
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:2
                },
                1000:{
                    items:4
                }
            }
        })
    })
</script>
@endpush
