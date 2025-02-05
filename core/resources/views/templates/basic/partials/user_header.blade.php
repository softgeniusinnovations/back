<header class="header-primary dark--600 user-header-primary">
    <div class="container">
        <div class="row g-0 align-items-center">
            <div class="header-fluid-custom-parent">
                <a class="logo" href="{{ route('home') }}"><img class="img-fluid logo__is" src="{{ getImage(getFilePath('logoIcon') . '/logo.png') }}" alt="@lang('logo')"></a>
                <nav class="primary-menu-container">
                    <ul class="list list--row primary-menu-lg justify-content-end justify-content-lg-start">
                        <li class="text-white d-lg-none d-block"><a class="text-light" href="{{ route('user.profile.setting') }}"><i class="far fa-user-circle fa-xl"></i> {{ optional(auth()->user())->user_id }}</a></li>
                    </ul>
                    <ul class="list list--row primary-menu justify-content-end align-items-center right-side-nav gap-4">

                        @if ($general->multi_language)
                        @php
                        $language = App\Models\Language::all();
                        @endphp
                        <li class="d-none d-lg-block">
                            <div class="select-lang--container">
                                <div class="select-lang">
                                    <span class="select-lang__icon text-white">
                                        <i class="fas fa-globe"></i>
                                    </span>
                                    <select class="form-select langSel">
                                        @foreach ($language as $item)
                                        <option value="{{ $item->code }}" @if (session('lang')==$item->code) selected @endif>
                                            {{ __($item->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </li>
                        @endif
                        <li><a class="btn btn--signup" href="{{ route('home') }}"> @lang('Bet Now') </a></li>
                        <li>
                            <img src="{{ asset('assets/profile/user/'. auth()->user()->profile_photo) }}" alt="@lang('user')"
                            style="width: 40px;
                                height: 40px;
                                border-radius: 50%;">
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>
