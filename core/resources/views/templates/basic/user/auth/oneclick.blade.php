@extends($activeTemplate . 'layouts.frontend')
@section('frontend')
    @php
        $policyElements = getContent('policy_pages.element', false, null, true);
        $registerContent = getContent('register.content', true);
    @endphp
    <div class="login-page section register-page" style="background-image: url({{ getImage('assets/images/frontend/register/' . @$registerContent->data_values->background_image, '1920x1070') }});">
        <div class="container">
            <div class="row g-3 align-items-center justify-content-lg-between">
                <div class="col-lg-6 d-lg-block d-none">
                    <img class="login-page__img img-fluid" src="{{ getImage('assets/images/frontend/register/' . @$registerContent->data_values->image, '1380x1150') }}" alt="@lang('image')">
                </div>
                <div class="col-lg-6 col-xl-5">
                    <div class="login-form mt-0">
                        <div class="col-12">
                            <h4 class="login-form__title">{{ __(@$registerContent->data_values->heading) }}</h4>
                        </div>
                        @include($activeTemplate . 'partials.oneclick', ['registerContent' => $registerContent, 'policyElement' => $policyElements, 'countries' => $countries, 'currencies' => $currencies])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
