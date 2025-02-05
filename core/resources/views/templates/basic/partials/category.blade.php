@php
        $gameType = session('game_type', 'live');

    @endphp

    @auth
    <nav class="sports-category" data-simplebar>
        <div class="sports-category__list">
            @foreach ($categories as $category)
                <a class="sports-category__link @if (@$activeCategory->id == $category->id) active @endif" href="{{ route('category.games', $category->slug) }}">
                    @if ($category->games_count)
                        <span class="sports-category__notification"> {{ $category->games_count }} </span>
                    @endif
                    <span class="sports-category__icon">
                        @php echo $category->icon @endphp
                    </span>
                    <span class="sports-category__text">
                        {{ strLimit(__($category->name), 20) }}
                    </span>
                </a>
            @endforeach

        </div>
    </nav>

    <nav class="sports-sub-category" data-simplebar>
        <div class="sports-category__list">
            @foreach ($leagues as $league)
                <a class="sub-category-drawer__link @if (@$activeLeague->id == $league->id) active @endif" href="{{ route('league.games', $league->slug) }}">

                    <span class="sub-category-drawer__flag">
                        <img class="sub-category-drawer__flag-img" src="{{ getImage(getFilePath('league') . '/' . $league->image, getFileSize('league')) }}" alt="@lang('image')">
                    </span>
                    <span class="sub-category-drawer__text" title="{{ __($league->name) }}">
                        {{ __($league->short_name) }}
                    </span>
                    @if ($league->game_count)
                        <span class="league-game-count">{{ $league->game_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>

    <div class="sub-category-drawer">
        <div class="container-fluid p-0">
            <div class="row g-0">
                <div class="col-12">
                    <div class="sub-category-drawer__head">
                        <span class="sub-category-drawer__head-content"></span>
                        <button class="sub-category-drawer__head-close" type="button">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="sub-category-drawer__body" data-simplebar>
                        <ul class="list sub-category-drawer__list">
                            @foreach ($leagues as $league)
                                <li>
                                    <a class="sub-category-drawer__link @if (@$activeLeague->id == $league->id) active @endif" href="{{ route('league.games', $league->slug) }}">
                                        <span class="sub-category-drawer__flag">
                                            <img class="sub-category-drawer__flag-img" src="{{ getImage(getFilePath('league') . '/' . $league->image, getFileSize('league')) }}" alt="@lang('image')">
                                        </span>
                                        <span class="sub-category-drawer__text" title="{{ __($league->name) }}">
                                            {{ __($league->short_name) }}
                                        </span>
                                    </a>

                                    @if ($league->game_count)
                                        <span class="league-game-count">{{ $league->game_count }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endauth
