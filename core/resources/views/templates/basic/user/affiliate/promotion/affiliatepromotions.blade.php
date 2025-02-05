@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="table-responsive">
    <table class="table-responsive table-sm custom--table table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('User Details')</th>
                <th>@lang('Promo Code')</th>
                <th>@lang('Percentage')</th>
                <th>@lang('Register Date')</th>
                {{-- <th>@lang('Action')</th> --}}
            </tr>
        </thead>

        <tbody>
            @php
            $i=0;
            @endphp
            @forelse ($affiliates as $item)
            <tr>
                <td>{{ ++$i }}</td>
                <td class="text-left" style="text-align: left">
                    {{ optional($item->betterUser)->user_id }}<br>
                </td>
                <td>{{ $item->promo->promo_code }}</td>
                <td>{{ $item->promo->promo_percentage }}%</td>
                <td>{{ $item->betterUser->created_at->format('d-m-Y') }}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="float-end mt-2">
        {{ $affiliates->links() }}
    </div>
</div>
@endsection
