@extends($activeTemplate . 'layouts.frontend')

@section('frontend')
<x-breadcrumb pageTitle="{{ $pageTitle }}" />

<div class="casino-area">
    <!--Prelaoder area start-->
    <div class="d-flex align-items-center justify-content-center" style="width:100%">
        <div class="spinner-border text-warning" role="status">
          <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!--Preloader area end-->
    
    
    <!--Casino menu area start-->
    <div class="casino-menu-area">
        <ul class="casino-menu">
            
        </ul>
    </div>
    <!--Casino menu area end-->
    
    <!--Casino area start-->
    <div class="casino-all-data">
    </div>
    <!--Casino area end-->
</div>
@endsection

@push('script')

<script>
    $(document).ready(function(){
        
        var currentPage = 1;
        var itemsPerPage = 36;
        var allGames = [];
        var type = 'all';
        
        function loadMoreItems(games=allGames, type=type) {
            var startIndex = (currentPage - 1) * itemsPerPage;
            var endIndex = currentPage * itemsPerPage;
            var gameList = [];
            var allGames = games;
            if(type == 'all'){
                gameList = allGames?.filter((item)=> item?.device == '2' && item?.flash=='0') ?? [];
            }else{
                gameList = games?.filter(item => item?.title == type && item?.device == '2' && item?.flash=='0')??[];
            }
        
            for (var i = startIndex; i < endIndex && i < gameList.length; i++) {
                 var casinoItem = $(`<div class="casino-item-data" style="background: url(${gameList[i]?.img}) no-repeat center center/cover"><span>${gameList[i]?.name}</span><div><button  class="play-btn">Play</button>${gameList[i]?.demo == '1' ? '<button class="demo-btn">Demo</button>':'' }</div>
                 </div>`);
                    casinoItem.attr('data-casino', JSON.stringify(gameList[i]));
                    casinoItem.attr('data-casino-title', gameList[i]?.title);
                    $('.casino-all-data').append(casinoItem);
            }
            currentPage++;
        }
                
        $.ajax({
            url:"{{route('casino.data')}}",
            method: 'GET',
            dataType: "json",
            beforeSend: function() {
              $(".spinner-border").show();
           },
            success:function(responseData){
                if(responseData?.status == 'success'){
                    let {status,content:{gameLabels, gameTitles,gameList}} = responseData;
                    allGames = gameList;
                    // Casino Menu Item
                    $('.casino-menu').append('<li class="casino-item active" data-title="all"><img src="{{asset("assets/providers/providers_icons/rubyplay.png")}}" /><span>All</span></li>');
                    $.each(gameTitles, function(index, item){
                        var listItem = $('<li class="casino-item" data-title="'+item+'"><img src="https://trambet.smshagor.com/assets/providers/providers_icons/' + item + '.png"><span>' + item.replace(/_/g, ' ') + '</span></li>');
                            $('.casino-menu').append(listItem);
                    });
                    
                    loadMoreItems(allGames,'all');
                
                    $(".spinner-border").hide();
                }else{
                    $('.casino-area').html('Something went wrong');
                    $(".spinner-border").hide();
                }
            },
            error: function (request, status, error) {
                alert(request.responseText);
            }
        });
        
        
        
        $(document).on('click', '.casino-item', function(){
             $('.casino-item').removeClass('active');
             $(this).addClass('active');
             let title = $(this).data('title');
             type=title;
             $('.casino-all-data').html('');
             currentPage = 1;
             loadMoreItems(allGames, type);
        });
        
        $(window).on('scroll', function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
                loadMoreItems(allGames, type);
            }
        });
        
        // Demo game run
        $(document).on('click', '.play-btn, .demo-btn', function(){
           var casinoItem = $(this).closest('.casino-item-data');
           var buttonName = $(this).attr("class");
           var dataCasino = casinoItem.data('casino');
           var auth = "{{auth()->check() ? 'Yes' : 'No'}}"
           
           if(auth == 'No'){
               $('#loginModal').addClass('show');
               $('#loginModal').show();
           }
           if(auth == 'Yes'){
              if(dataCasino?.id){
                  $.ajax({
                      url: "{{route('casino.open.game')}}",
                      method: 'GET',
                      data: {
                          id: dataCasino?.id,
                          name: dataCasino?.name,
                          demo: buttonName == 'play-btn' ? 0 : dataCasino?.demo,
                          _token: "{{csrf_token()}}"
                      },
                      dataType: "json",
                      beforeSend: function() {
                         $(this).text('...')
                      },
                      success:function(responseData){
                          if(responseData?.status == 'success'){
                            //   var iframeSrc = responseData?.content?.game?.url;
                            //   window.location.href = "{{ route('casino.game.open') }}?url=" + encodeURIComponent(iframeSrc);
                            var iframeSrc = responseData?.content?.game?.url;
                            window.open("{{ route('casino.game.open') }}?url=" + encodeURIComponent(iframeSrc), "_blank");

                            //   window.open(responseData?.content?.game?.url, '_blank');
                          }
                      },
                      error: function (request, status, error) {
                            alert('Something went wrong please reload the page');
                        }
                  });
              }
          }
           
        });
        // Login Modal Close
        $(document).on('click','.close', function(){
             $('#loginModal').removeClass('show');
             $('#loginModal').hide();
        })
    });
</script>
@endpush

@push('style')
<style>
    .casino-area{
        background:#20212F;
        width: 100%;
        height: auto;
    }
    .casino-menu {
      width: 100%;
      display: flex;
      list-style: none;
      align-content: center;
      justify-content: flex-start;
      margin: 0;
      padding: 0;
      background: #393A48;
      color: #A3A3AA;
      font-size: 11px;
      overflow: hidden;
      overflow-x: auto;
      text-transform: capitalize;
      font-weight: 700;
    }
    .casino-menu li {
      cursor: pointer;
      transition: all 0.3s ease-in-out;
      min-width: 108px;
      text-align: center;
      padding: 10px 0;
    }
    .casino-menu li span{
        display:block;
    }
    .casino-menu li img {
      width: 24px;
      margin-bottom: 5px;
    }
    .casino-menu li:hover, .casino-menu li.active {
      background: #4f539d;
    }
    .casino-all-data {
      display: flex;
      flex-flow: row wrap;
    }
    .casino-item-data {
        border-radius: 10px;
        overflow: hidden;
        flex: 0 1 calc(25% - 12px);
        margin: 6px;
        height: 236px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-flow: row wrap;
        z-index: 1;
        text-align: center;
        line-height: 1.3;
        color: #fff;
        font-size: 11px;
        padding: 5px;
    }
    .casino-item-data:hover:after {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        content: '';
        background: black;
        opacity: 0.7;
        z-index: -1;
    }
    
    .casino-item-data span {
        display: block;
        width: 100%;
        visibility:hidden;
    }
    
    .casino-item-data button {
        background: transparent;
        border: 1px solid #ddd;
        color: #fff;
        text-transform: uppercase;
        padding: 5px 10px 3px;
        border-radius: 4px;
        visibility:hidden;
    }
    .casino-item-data button:first-child{
        background: #8325EE;
        border-color: #8325EE;
    }
    .casino-item-data:hover button,.casino-item-data:hover span{
        visibility: visible;
    }
    .casino-item-data div {
        display: flex;
        gap: 5px;
    }
    .casino-item-data img {
      object-fit: cover;
      width: 100%;
      height: 100%;
      cursor: pointer;
    }
    @media only screen and (max-width: 767px) and (min-width: 320px)  {
        .casino-menu-area {
          position: sticky;
          top: 70px;
          z-index: 999999;
        }
        .casino-all-data {
          margin-top: 90px;
        }
        .casino-item-data {
          flex: 0 1 calc(100% - 12px);
        }
    }
</style>
@endpush