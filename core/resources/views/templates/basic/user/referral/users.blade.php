@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="card custom--card">
        <h5 class="card-header">
            {{ __($pageTitle) }}
        </h5>
        <div class="row">
            <div class="col-xl-12">
                @if ($user->refBy)
                    <div class="d-flex justify-content-center flex-wrap">
                        <h5>
                            <span class="mb-2">@lang('You are referred by')</span>
                            <span class="text--base">{{ $user->refBy->username }}</span>
                        </h5>
                    </div>
                @endif

                <div class="treeview-container @if (!$user->refBy) mt-3 @endif">
                    <ul class="treeview">
                        @if ($user->allReferrals->count() > 0 && $maxLevel > 0)
                            <li class="items-expanded"> {{ $user->username }}
                                @include($activeTemplate . 'partials.under_tree', ['user' => $user, 'layer' => 0, 'isFirst' => true])
                            </li>
                        @else
                            <div class="text-center">
                                <i class="text-muted fal fa-user-alt-slash fa-3x"></i><br>
                                <p class="text-muted">@lang('No referred user found')</p>
                            </div>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-lib')
    <link href="{{ asset($activeTemplateTrue . 'css/treeView.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/treeView.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.treeview').treeView();
        })(jQuery);
    </script>
@endpush
