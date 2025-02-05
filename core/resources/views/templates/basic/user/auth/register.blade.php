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
                        @include($activeTemplate . 'partials.register', ['registerContent' => $registerContent, 'policyElement' => $policyElements, 'countries' => $countries, 'currencies' => $currencies])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-labelledby="existModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <h5 class="text-center">@lang('You already have an account please Login')</h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>
@endsection
