@extends($activeTemplate . 'layouts.bet')
@section('bet')
    @php
        $optionsId = collect(session()->get('bets'))
            ->pluck('option_id')
            ->toArray();
    @endphp

    <div class="odd-list pt-0">
        <div class="row gx-0 pd-lg-15 gx-lg-3 gy-3">
            <div class="col-12">
                <div class="odd-list__head">
                    <div class="odd-list__team">
                        <div class="odd-list__team-name">{{ __($game->teamOne->name) }}</div>
                        <div class="odd-list__team-img">
                            <img class="odd-list__team-img-is" src="{{ $game->teamOne->teamImage() }}" alt="image" />
                        </div>
                    </div>

                    <div class="odd-list__team-divide">@lang('VS')</div>

                    <div class="odd-list__team justify-content-end">
                        <div class="odd-list__team-img">
                            <img class="odd-list__team-img-is" src="{{ $game->teamTwo->teamImage() }}" alt="image" />
                        </div>
                        <div class="odd-list__team-name">{{ __($game->teamTwo->name) }}</div>
                    </div>
                </div>

                <div class="odd-list__body">
                    <div class="odd-list__body-content">
                        <div class="scores-show accordion accordion--odd" style="background:url({{getImage('assets/images/league/'.@$game->league->image, '1610x450')}}) no-repeat center center/cover;color: #fff;font-size: 18px;padding: 15px; height: 250px">
                            <p style="margin:0">@lang('Scores') (<span class="game-process"></span>)</p>
                            <p style="margin:0">Match: <span class="game-league"></span></p>
                            <p style="margin:0"><span class="update-time"></span></p>
                            <p style="margin:0" class="accordion-header-first"></p>
                            <p style="margin:0" class="accordion-header-second"></p>
                        </div>
                        
                        <div class="odd-list__title">@lang('Markets')</div>
                        @forelse ($game->questions as $question)
                            <div class="accordion accordion--odd">
                                <div class="accordion-item  @if ($question->locked) locked @endif">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#question-{{ $question->id }}" aria-expanded="true">
                                            {{ __($question->title) }}
                                        </button>
                                    </h2>
                                    <div id="question-{{ $question->id }}" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            <ul class="list list--row odd-list__options">
                                                @forelse ($question->options as $option)
                                                    <li>
                                                        <button class="odd-list__option oddBtn just-open-betslip @if (in_array($option->id, $optionsId)) active @endif @if ($option->locked) locked @endif" data-option_id="{{ $option->id }}">
                                                            <span class="odd-list__option-text">{{ __($option->name) }}</span>
                                                            <span class="odd-list__option-ratio">{{ rateData($option->odds) }} </span>
                                                        </button>
                                                    </li>
                                                @empty
                                                    <small class="text-muted"> @lang('No odds available for now')</small>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-message mt-3">
                                <img class="img-fluid" src="{{ asset($activeTemplateTrue . '/images/empty_message.png') }}" alt="@lang('image')">
                                <p>@lang('No markets available for now')</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
<script>
    $(document).ready(function(){
        @if($game->status == 1)
            // var ajax_call = function(){
                $.ajax({
                    url: "{{route('market.scores')}}",
                    type: 'GET',
                    data:{
                        "game": "{{$game->game_id}}",
                        "_token": "{{csrf_token()}}"
                    },
                    success:function(data){
                        if(data?.length > 0 && data[0]?.scores){
                            $('.accordion-header-first').text(`${data[0]?.scores[0]?.name} : ${data[0]?.scores[0]?.score}`);
                            $('.accordion-header-second').text(`${data[0]?.scores[1]?.name} : ${data[0]?.scores[1]?.score}`);
                            $('.update-time').text(formatTimestamp(data[0]?.last_update));
                            $('.game-league').text(`${data[0]?.sport_title}`);
                            $('.game-process').text(`${data[0]?.completed ? 'Game End' : 'Live'}`);
                        }else{
                            $('.scores-show').hide();
                        }
                    },
                    error: function (request, status, error) {
                        $('.scores-show').hide();
                    }
                });
            // }
            // setInterval(ajax_call, 100000);
      @endif
    });
    
    function formatTimestamp(timestamp) {
        const date = new Date(timestamp);
    
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are zero-based, so add 1
        const year = date.getFullYear();
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const seconds = date.getSeconds().toString().padStart(2, '0');
    
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const monthName = monthNames[date.getMonth()];
    
        return `${day} ${monthName} ${year} , ${hours}:${minutes}:${seconds}`;
    }
    
</script>
@endpush
