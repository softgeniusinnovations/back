@extends($activeTemplate . 'layouts.master')
@section('master')

<div>
    <table class="table-responsive--md custom--table custom--table-separate table">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('Event')</th>
                <th>@lang('Deposit Amount')</th>
                <th>@lang('Bonus Amount')</th>
                <th>@lang('Request')</th>
                <th>@lang('Last Update')</th>
                <th>@lang('Status')</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 0;
            @endphp
            @forelse ($bonus as $item)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ optional($item->event)->title }}</td>
                    <td>{{ showAmount(optional($item->deposit)->amount) }}</td>
                    <td>{{ showAmount($item->bonus_amount) }}</td>
                    <td>
                        {{ $item->created_at->format('d-M-Y') }} <br>
                        {{ $item->created_at->format('g:i A') }}
                    </td>
                    <td>
                        {{ $item->updated_at->format('d-M-Y') }} <br>
                        {{ $item->updated_at->format('g:i A') }}
                    </td>
                    <td>
                        @if ($item->status == 1)
                            <span class="badge badge--success">@lang('Success')</span>
                        @elseif ($item->status == 2)
                            <span class="badge badge--info">@lang('Pending')</span>
                        @elseif($item->status == 3)
                            <span class="badge badge--danger">@lang('Reject')</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
