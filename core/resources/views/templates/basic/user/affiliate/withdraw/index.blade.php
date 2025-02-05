@extends($activeTemplate . 'layouts.master')
@section('master')
<div class="">
    <table class="table-responsive--md custom--table custom--table-separate table">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('Order Id')</th>
                <th>@lang('Agent Id')</th>
                <th>@lang('Date and time')</th>
                <th>@lang('Provider')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Status')</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>1</td>
                <td>123456</td>
                <td>43626</td>
                <td>12 Jan 2024</td>
                <td>GCash</td>
                <td>1000 USD</td>
                <td>
                    <span class="badge badge--success">@lang('Approved')</span>
                </td>
            </tr>
            <tr>
                <td>2</td>
                <td>123456</td>
                <td>43626</td>
                <td>12 Jan 2024</td>
                <td>GCash</td>
                <td>1000 USD</td>
                <td>
                    <span class="badge badge--danger">@lang('Reject')</span>
                </td>
            </tr>
            <tr>
                <td>3</td>
                <td>123456</td>
                <td>43626</td>
                <td>12 Jan 2024</td>
                <td>GCash</td>
                <td>1000 USD</td>
                <td>
                    <span class="badge badge--success">@lang('Approved')</span>
                </td>
            </tr>
        </tbody>
    </table>
    {{-- <div class="float-end mt-2">
        {{ $promotions->links() }}
    </div> --}}
</div>
@endsection
