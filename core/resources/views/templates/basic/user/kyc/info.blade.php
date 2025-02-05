@extends($activeTemplate . 'layouts.master')
@section('master')
    <div class="col-12">
        <div class="card custom--card">
            <h5 class="card-header">
                <span class="card-header__icon">
                    <i class="las la-user-check"></i>
                </span>
                @lang('KYC Data')
            </h5>

            <div class="card-body">
                <div class="preview-details">
                    <ul class="list-group list-group-flush">
                        @if ($user->kyc_data)
                            @foreach ($user->kyc_data as $val)
                                @continue(!$val->value)
                                <li class="list-group-item d-flex justify-content-between flex-wrap bg-transparent px-0">
                                    <span>{{ __($val->name) }}</span>
                                    <span class="fw-bold">
                                        @if ($val->type == 'checkbox')
                                            {{ implode(',', $val->value) }}
                                        @elseif($val->type == 'file')
                                            <a class="text--base" href="{{ route('user.attachment.download', encrypt(getFilePath('verify') . '/' . $val->value)) }}"><i class="fa fa-file"></i> @lang('Attachment') </a>
                                        @else
                                            {{ __($val->value) }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
